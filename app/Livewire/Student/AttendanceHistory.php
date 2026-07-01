<?php

namespace App\Livewire\Student;

use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Services\LectureManageStudentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Exports\StudentAttendanceHistoryExport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceHistory extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    #[Url]
    public string $classFilter = 'all';

    public string $search = '';

    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function exportExcel()
    {
        $fileName = 'lich_su_diem_danh_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new StudentAttendanceHistoryExport(auth()->id(), $this->classFilter, $this->statusFilter, $this->search), $fileName);
    }

    public function clearFilters(): void
    {
        $this->statusFilter = 'all';
        $this->classFilter = 'all';
        $this->search = '';
    }

    public function render(): View
    {
        $members = ClassMember::query()
            ->with('courseClass.owner')
            ->where('user_id', auth()->id())
            ->where('status', ClassMember::STATUS_ACTIVE)
            ->get();

        // Summary: dùng LectureManageStudentService — tính theo TIẾT, chỉ buổi đã chốt.
        // Đồng nhất với trang "Lớp tôi tham gia" và trang giảng viên.
        $memberIds = $members->pluck('id')->all();
        $statsMap  = $memberIds
            ? app(LectureManageStudentService::class)->getStudentsAttendanceStats($memberIds)
            : [];

        $allRecords = $this->attendanceRecords($members);

        $page = $this->getPage();
        $perPage = $this->perPage;

        $paginatedRecords = new LengthAwarePaginator(
            $allRecords->forPage($page, $perPage),
            $allRecords->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        return view('livewire.student.attendance-history', [
            'records' => $paginatedRecords,
            'classes' => $members->pluck('courseClass')->filter()->unique('id')->values(),
            'summary' => $this->summary($allRecords, $statsMap),
            'isDemo' => false,
        ])->layout('layouts.user', ['title' => 'Lịch sử điểm danh']);
    }

    /**
     * @param  Collection<int, ClassMember>  $members
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function attendanceRecords(Collection $members): \Illuminate\Support\Collection
    {
        $memberIds = $members->pluck('id');

        if ($memberIds->isEmpty()) {
            return collect();
        }

        $records = AttendanceRecord::query()
            ->with(['classMember.courseClass.owner', 'classSession'])
            ->whereIn('class_member_id', $memberIds)
            ->whereHas('classSession', fn ($q) => $q->where('status', 'closed'))
            ->get()
            ->sortByDesc(fn (AttendanceRecord $record) => $record->classSession?->date?->timestamp ?? 0)
            ->map(fn (AttendanceRecord $record): array => [
                'id' => $record->id,
                'date' => $record->classSession?->date,
                'session' => $record->classSession?->name ?? 'Buổi điểm danh',
                'class_id' => $record->classMember?->courseClass?->id,
                'class_code' => $record->classMember?->courseClass?->join_key ?? 'N/A',
                'class_name' => $record->classMember?->courseClass?->name ?? 'Lớp học',
                'subject_code' => $record->classMember?->courseClass?->subject_code,
                'semester' => $record->classMember?->courseClass?->semester,
                'teacher' => $record->classMember?->courseClass?->owner?->name ?? 'Chưa cập nhật',
                'status' => $record->status,
                'method' => filled($record->classSession?->qr_token) ? 'QR + GPS' : 'Thủ công',
                'check_in_time' => $record->check_in_time,
                'verified' => $record->is_account,
                'distance' => $record->distance_meters,
                'note' => $record->note,
                'demo' => false,
            ])
            ->values();

        if ($this->classFilter !== 'all') {
            $records = $records->where('class_id', $this->classFilter)->values();
        }

        if ($this->statusFilter !== 'all') {
            $records = $records->where('status', $this->statusFilter)->values();
        }

        if (trim($this->search) !== '') {
            $search = str($this->search)->lower()->toString();
            $records = $records->filter(function (array $record) use ($search): bool {
                $dateStr = $record['date'] ? $record['date']->format('d/m/Y') : '';
                return str($record['class_name'])->lower()->contains($search)
                    || str($record['class_code'])->lower()->contains($search)
                    || str((string) $record['subject_code'])->lower()->contains($search)
                    || str($dateStr)->contains($search);
            })->values();
        }

        return $records;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $records
     * @param  array<int, array<string, mixed>>  $statsMap  từ LectureManageStudentService
     * @return array<string, int>
     */
    private function summary(\Illuminate\Support\Collection $records, array $statsMap): array
    {
        // Tổng hợp TIẾT từ service — chỉ buổi đã chốt, tính theo lesson_count.
        // Đồng nhất với trang giảng viên và trang "Lớp tôi tham gia".
        $presentSessions = (int) array_sum(array_column($statsMap, 'present_sessions'));
        $lateSessions    = (int) array_sum(array_column($statsMap, 'late_sessions'));
        $absentSessions  = (int) array_sum(array_column($statsMap, 'absent_sessions'));
        $excusedSessions = (int) array_sum(array_column($statsMap, 'excused_sessions'));
        $studiedSessions = (int) array_sum(array_column($statsMap, 'studied_sessions'));

        return [
            'total'   => $studiedSessions,   // Tổng tiết đã chốt (mẫu số).
            'present' => $presentSessions,   // Tiết có mặt đúng giờ.
            'late'    => $lateSessions,       // Tiết đi muộn.
            'excused' => $excusedSessions,   // Tiết vắng có phép.
            'absent'  => $absentSessions,    // Tiết vắng không phép.
            'pending' => $records->where('status', 'pending')->count(), // Buổi chưa điểm danh (vẫn đếm bản ghi).
        ];
    }
}
