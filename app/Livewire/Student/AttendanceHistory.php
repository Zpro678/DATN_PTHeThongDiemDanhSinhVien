<?php

namespace App\Livewire\Student;

use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class AttendanceHistory extends Component
{
    public string $statusFilter = 'all';

    #[Url]
    public string $classFilter = 'all';

    public string $search = '';

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
            ->get();

        $records = $this->attendanceRecords($members);

        return view('livewire.student.attendance-history', [
            'records' => $records,
            'classes' => $members->pluck('courseClass')->filter()->unique('id')->values(),
            'summary' => $this->summary($records),
            'isDemo' => $records->first()['demo'] ?? false,
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
            return $this->demoRecords();
        }

        $records = AttendanceRecord::query()
            ->with(['classMember.courseClass.owner', 'classSession'])
            ->whereIn('class_member_id', $memberIds)
            ->get()
            ->sortByDesc(fn (AttendanceRecord $record) => $record->classSession?->date?->timestamp ?? 0)
            ->map(fn (AttendanceRecord $record): array => [
                'id' => $record->id,
                'date' => $record->classSession?->date,
                'session' => $record->classSession?->name ?? 'Buổi điểm danh',
                'class_id' => $record->classMember?->courseClass?->id,
                'class_code' => $record->classMember?->courseClass?->code ?? 'N/A',
                'class_name' => $record->classMember?->courseClass?->name ?? 'Lớp học',
                'subject_code' => $record->classMember?->courseClass?->subject_code,
                'semester' => $record->classMember?->courseClass?->semester,
                'teacher' => $record->classMember?->courseClass?->owner?->name ?? 'Chưa cập nhật',
                'status' => $record->status,
                'method' => filled($record->classSession?->qr_token) ? 'QR + GPS' : 'Thủ công',
                'check_in_time' => $record->check_in_time,
                'verified' => $record->is_verified,
                'distance' => $record->distance_meters,
                'note' => $record->note,
                'demo' => false,
            ])
            ->values();

        if ($this->classFilter !== 'all') {
            $records = $records->where('class_id', (int) $this->classFilter)->values();
        }

        if ($this->statusFilter !== 'all') {
            $records = $records->where('status', $this->statusFilter)->values();
        }

        if (trim($this->search) !== '') {
            $search = str($this->search)->lower()->toString();
            $records = $records->filter(function (array $record) use ($search): bool {
                return str($record['class_name'])->lower()->contains($search)
                    || str($record['class_code'])->lower()->contains($search)
                    || str((string) $record['subject_code'])->lower()->contains($search);
            })->values();
        }

        return $records->isNotEmpty() ? $records : $this->demoRecords();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $records
     * @return array<string, int>
     */
    private function summary(\Illuminate\Support\Collection $records): array
    {
        return [
            'total' => $records->count(),
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
            'excused' => $records->where('status', 'excused')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'pending' => $records->where('status', 'pending')->count(),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function demoRecords(): \Illuminate\Support\Collection
    {
        return collect([
            ['id' => 1, 'date' => now()->subDays(1), 'session' => 'Buổi 05', 'class_id' => 1, 'class_code' => 'DB101', 'class_name' => 'Thiết kế & Quản trị SQL', 'subject_code' => 'DB101', 'semester' => 'HK1 2026-2027', 'teacher' => 'Thầy Lê Hoàng Đạt', 'status' => 'present', 'method' => 'QR + GPS', 'check_in_time' => now()->subDays(1)->setTime(7, 5), 'verified' => true, 'distance' => 12, 'note' => null, 'demo' => true],
            ['id' => 2, 'date' => now()->subDays(2), 'session' => 'Buổi 04', 'class_id' => 2, 'class_code' => 'PY201', 'class_name' => 'Phát triển Web Python', 'subject_code' => 'PY201', 'semester' => 'HK1 2026-2027', 'teacher' => 'Cô Trần Thị Thu Thủy', 'status' => 'late', 'method' => 'QR + GPS', 'check_in_time' => now()->subDays(2)->setTime(7, 18), 'verified' => true, 'distance' => 34, 'note' => 'Vào lớp muộn 8 phút', 'demo' => true],
            ['id' => 3, 'date' => now()->subDays(3), 'session' => 'Buổi 03', 'class_id' => 3, 'class_code' => 'PH102', 'class_name' => 'Vật lý đại cương 2', 'subject_code' => 'PH102', 'semester' => 'HK1 2026-2027', 'teacher' => 'Thầy Lâm Văn Tiến', 'status' => 'absent', 'method' => 'Thủ công', 'check_in_time' => null, 'verified' => false, 'distance' => null, 'note' => 'Chưa có minh chứng nghỉ', 'demo' => true],
            ['id' => 4, 'date' => now()->subDays(5), 'session' => 'Buổi 02', 'class_id' => 4, 'class_code' => 'NET301', 'class_name' => 'Lý thuyết Mạng Máy Tính', 'subject_code' => 'NET301', 'semester' => 'HK1 2026-2027', 'teacher' => 'TS. Lê Quang Linh', 'status' => 'excused', 'method' => 'Thủ công', 'check_in_time' => null, 'verified' => true, 'distance' => null, 'note' => 'Đơn xin nghỉ đã duyệt', 'demo' => true],
            ['id' => 5, 'date' => now()->subDays(6), 'session' => 'Buổi 01', 'class_id' => 1, 'class_code' => 'DB101', 'class_name' => 'Thiết kế & Quản trị SQL', 'subject_code' => 'DB101', 'semester' => 'HK1 2026-2027', 'teacher' => 'Thầy Lê Hoàng Đạt', 'status' => 'present', 'method' => 'QR + GPS', 'check_in_time' => now()->subDays(6)->setTime(7, 1), 'verified' => true, 'distance' => 9, 'note' => null, 'demo' => true],
        ]);
    }
}
