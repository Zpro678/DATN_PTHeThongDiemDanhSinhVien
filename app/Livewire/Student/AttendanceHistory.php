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
use App\Services\SubscriptionService;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceHistory extends Component
{
    use WithPagination;

    #[Url(keep: true)]
    public string $statusFilter = 'all';

    #[Url(keep: true)]
    public string $classFilter = '';

    #[Url(keep: true)]
    public string $search = '';

    public int $perPage = 10;

    public function mount()
    {
        if (empty($this->classFilter)) {
            $this->classFilter = 'all';
        }
    }

    public function updatedClassFilter($value): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter($value): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function exportExcel()
    {
        // Chỉ cho xuất khi gói hiện tại bật tính năng xuất Excel.
        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            session()->flash('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');

            return $this->redirectRoute('upgrade', navigate: true);
        }

        $fileName = 'lich_su_diem_danh_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new StudentAttendanceHistoryExport(auth()->id(), $this->classFilter, $this->statusFilter, $this->search), $fileName);
    }

    public function clearFilters(): void
    {
        $this->statusFilter = 'all';
        $this->search = '';
        $this->classFilter = 'all';
    }

    public function render(): View
    {
        $members = ClassMember::query()
            ->with('courseClass.owner')
            ->where('user_id', auth()->id())
            ->where('status', ClassMember::STATUS_ACTIVE)
            ->get();

        // When a specific class is selected, filter members for summary stats
        $summaryMembers = $members;
        if (!empty($this->classFilter) && $this->classFilter !== 'all') {
            $filterId = (string) $this->classFilter;
            $summaryMembers = $members->filter(fn ($m) => (string) $m->courseClass?->id === $filterId);
        }

        // Summary: dùng LectureManageStudentService — tính theo TIẾT, chỉ buổi đã chốt.
        // Đồng nhất với trang "Lớp tôi tham gia" và trang giảng viên.
        $summaryMemberIds = $summaryMembers->pluck('id')->all();
        $statsMap  = $summaryMemberIds
            ? app(LectureManageStudentService::class)->getStudentsAttendanceStats($summaryMemberIds)
            : [];

        $allRecords = $this->attendanceRecords($members);

        // Gom các phiên (record) theo BUỔI để hiển thị dạng danh sách buổi, bấm vào xem chi tiết từng phiên.
        $meetingGroups = $this->groupByMeeting($allRecords);

        $page = $this->getPage();
        $perPage = $this->perPage;

        $paginatedMeetings = new LengthAwarePaginator(
            $meetingGroups->forPage($page, $perPage),
            $meetingGroups->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        return view('livewire.student.attendance-history', [
            'meetings' => $paginatedMeetings,
            'flatRecords' => $allRecords,
            'classes' => $members->pluck('courseClass')->filter()->unique('id')->values(),
            'summary' => $this->summary($allRecords, $statsMap),
            'isDemo' => false,
            'canExportExcel' => app(SubscriptionService::class)->canExportExcel(auth()->user()),
        ])->layout('layouts.user', ['title' => 'Lịch sử điểm danh']);
    }

    /**
     * Gom các bản ghi phiên theo buổi (meeting). Mỗi nhóm = 1 buổi, kèm danh sách phiên bên trong.
     * Trạng thái buổi lấy theo mức "tốt nhất" của các phiên (có mặt > muộn > có phép > vắng > chưa ĐD).
     *
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $records
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function groupByMeeting(\Illuminate\Support\Collection $records): \Illuminate\Support\Collection
    {
        $priority = ['present' => 5, 'late' => 4, 'excused' => 3, 'absent' => 2, 'pending' => 1];

        return $records
            ->groupBy(fn (array $r) => $r['meeting_id'] ? 'm-'.$r['meeting_id'] : 's-'.$r['session_id'])
            ->map(function ($group) use ($priority): array {
                $first = $group->first();
                $status = $group->sortByDesc(fn (array $r) => $priority[$r['status']] ?? 0)->first()['status'];
                $methods = $group->pluck('method')->unique();

                return [
                    'key' => $first['meeting_id'] ? 'm-'.$first['meeting_id'] : 's-'.$first['session_id'],
                    'date' => $first['date'],
                    'meeting' => $first['meeting'] ?: $first['session'],
                    'class_id' => $first['class_id'],
                    'class_code' => $first['class_code'],
                    'class_name' => $first['class_name'],
                    'teacher' => $first['teacher'],
                    'status' => $status,
                    'method' => $methods->count() === 1 ? $methods->first() : 'Nhiều hình thức',
                    'session_count' => $group->count(),
                    'sessions' => $group->values()->all(),
                ];
            })
            ->values();
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
            ->with(['classMember.courseClass.owner', 'classSession.meeting'])
            ->whereIn('class_member_id', $memberIds)
            ->whereHas('classSession', fn ($q) => $q->where('status', 'closed'))
            ->get()
            // Ngày mới nhất trước; cùng ngày thì gom theo buổi rồi theo thứ tự phiên (id) để các phiên cùng buổi liền nhau.
            ->sort(function (AttendanceRecord $a, AttendanceRecord $b) {
                $dateA = $a->classSession?->date?->timestamp ?? 0;
                $dateB = $b->classSession?->date?->timestamp ?? 0;
                return $dateB <=> $dateA
                    ?: ($b->classSession?->meeting_id ?? 0) <=> ($a->classSession?->meeting_id ?? 0)
                    ?: $a->class_session_id <=> $b->class_session_id;
            })
            ->map(fn (AttendanceRecord $record): array => [
                'id' => $record->id,
                'session_id' => $record->class_session_id,
                'meeting_id' => $record->classSession?->meeting_id,
                'date' => $record->classSession?->date,
                'meeting' => $record->classSession?->meeting?->name,
                'session' => $record->classSession?->name ?? 'Buổi điểm danh',
                'class_id' => $record->classMember?->courseClass?->id,
                'class_code' => $record->classMember?->courseClass?->join_key ?? 'N/A',
                'class_name' => $record->classMember?->courseClass?->name ?? 'Lớp học',
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

        if (!empty($this->classFilter) && $this->classFilter !== 'all') {
            $filterId = (string) $this->classFilter;
            $records = $records->filter(function ($record) use ($filterId) {
                return (string) $record['class_id'] === $filterId;
            })->values();
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
