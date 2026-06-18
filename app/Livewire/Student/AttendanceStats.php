<?php

namespace App\Livewire\Student;

use App\Models\ClassMember;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendanceStats extends Component
{
    public bool $showList = false;

    public function toggleView(): void
    {
        $this->showList = ! $this->showList;
    }

    public function render(): View
    {
        $subjects = $this->subjects();
        $totals = [
            'records' => collect($subjects)->sum('total'),
            'present' => collect($subjects)->sum('present'),
            'late' => collect($subjects)->sum('late'),
            'excused' => collect($subjects)->sum('excused'),
            'absent' => collect($subjects)->sum('absent'),
        ];

        $counted = max(1, $totals['records']);
        $totals['percent'] = (int) round((($totals['present'] + $totals['late'] + $totals['excused']) / $counted) * 100);
        $totals['warning_count'] = collect($subjects)->where('warning', true)->count();

        return view('livewire.student.attendance-stats', [
            'subjects' => $subjects,
            'totals' => $totals,
            'isDemo' => collect($subjects)->first()['demo'] ?? false,
        ])->layout('layouts.user', ['title' => 'Thống kê chuyên cần']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function subjects(): array
    {
        $members = ClassMember::query()
            ->with(['courseClass.owner', 'attendanceRecords.classSession'])
            ->where('user_id', auth()->id())
            ->get();

        if ($members->isEmpty()) {
            return $this->demoSubjects();
        }

        return $members->map(function (ClassMember $member): array {
            $records = $member->attendanceRecords;
            $total = max(1, $records->count());
            $present = $records->where('status', 'present')->count();
            $late = $records->where('status', 'late')->count();
            $excused = $records->where('status', 'excused')->count();
            $absent = $records->where('status', 'absent')->count();
            $percent = (int) round((($present + $late + $excused) / $total) * 100);

            return [
                'id' => $member->id,
                'code' => $member->courseClass?->subject_code ?: $member->courseClass?->code,
                'class_code' => $member->courseClass?->code,
                'name' => $member->courseClass?->name ?? 'Lớp học',
                'teacher' => $member->courseClass?->owner?->name ?? 'Chưa cập nhật',
                'semester' => $member->courseClass?->semester ?? 'Chưa cập nhật',
                'total' => $records->count(),
                'present' => $present,
                'late' => $late,
                'excused' => $excused,
                'absent' => $absent,
                'percent' => $records->isEmpty() ? 100 : $percent,
                'warning' => $records->isNotEmpty() && $percent < 80,
                'demo' => false,
            ];
        })->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function demoSubjects(): array
    {
        return [
            ['id' => 1, 'code' => 'DB101', 'class_code' => 'DB101_L02', 'name' => 'Thiết kế & Quản trị SQL', 'teacher' => 'Thầy Lê Hoàng Đạt', 'semester' => 'HK1 2026-2027', 'total' => 16, 'present' => 15, 'late' => 0, 'excused' => 0, 'absent' => 1, 'percent' => 94, 'warning' => false, 'demo' => true],
            ['id' => 2, 'code' => 'PY201', 'class_code' => 'PY201_L01', 'name' => 'Phát triển Web Python', 'teacher' => 'Cô Trần Thị Thu Thủy', 'semester' => 'HK1 2026-2027', 'total' => 15, 'present' => 13, 'late' => 1, 'excused' => 0, 'absent' => 1, 'percent' => 93, 'warning' => false, 'demo' => true],
            ['id' => 3, 'code' => 'PH102', 'class_code' => 'PH102_L04', 'name' => 'Vật lý đại cương 2', 'teacher' => 'Thầy Lâm Văn Tiến', 'semester' => 'HK1 2026-2027', 'total' => 14, 'present' => 9, 'late' => 1, 'excused' => 0, 'absent' => 4, 'percent' => 71, 'warning' => true, 'demo' => true],
            ['id' => 4, 'code' => 'NET301', 'class_code' => 'NET301_L01', 'name' => 'Lý thuyết Mạng Máy Tính', 'teacher' => 'TS. Lê Quang Linh', 'semester' => 'HK1 2026-2027', 'total' => 12, 'present' => 11, 'late' => 0, 'excused' => 1, 'absent' => 0, 'percent' => 100, 'warning' => false, 'demo' => true],
        ];
    }
}
