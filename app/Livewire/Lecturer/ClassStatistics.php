<?php

namespace App\Livewire\Lecturer;

use App\Models\AttendanceRecord;
use App\Models\CourseClass;
use App\Services\LectureManageStudentService;
use Livewire\Component;

class ClassStatistics extends Component
{
    public $class_id;

    public function mount($class_id): void
    {
        $this->class_id = (int) $class_id;

        CourseClass::where('id', $class_id)
            ->where('owner_user_id', auth()->id())
            ->firstOrFail();
    }

    public function render(LectureManageStudentService $service): \Illuminate\Contracts\View\View
    {
        $class = CourseClass::with(['sessions' => fn ($q) => $q->orderBy('date')->orderBy('id')])
            ->where('id', $this->class_id)
            ->firstOrFail();

        $members = $class->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->with('user')->get();
        $memberIds = $members->pluck('id')->all();
        $statsMap = $memberIds ? $service->getStudentsAttendanceStats($memberIds) : [];

        // Tổng hợp summary
        $statsCollection = collect($statsMap);
        $totalStudents   = $members->count();
        $closedSessions  = $class->sessions->where('status', 'closed');
        $totalSessions   = $class->sessions->count();
        // Số buổi đã học = số buổi (meeting) có phiên đã chốt.
        $studiedSessions  = $closedSessions->pluck('meeting_id')->filter()->unique()->count();
        $plannedSessions  = (int) $class->total_sessions;
        $avgAttendance   = $statsCollection->isNotEmpty()
            ? (int) round($statsCollection->avg('attendance_percent'))
            : 100;
        $bannedCount   = $statsCollection->where('is_banned', true)->count();
        $warningCount  = $statsCollection->where('is_warning', true)->where('is_banned', false)->count();
        // Quỹ vắng cấp lớp dựa trên số buổi cơ sở = max(dự kiến, số buổi đã học).
        $allowedAbsent = \App\Services\AttendanceCalculator::allowedAbsentSessions(
            \App\Services\AttendanceCalculator::baseSessions($plannedSessions, $studiedSessions)
        );

        // Danh sách học viên cần chú ý (banned trước, warning sau)
        $alertStudents = $members
            ->map(fn ($m) => ['member' => $m, 'stats' => $statsMap[$m->id] ?? null])
            ->filter(fn ($row) => $row['stats'] && ($row['stats']['is_banned'] || $row['stats']['is_warning']))
            ->sortByDesc(fn ($row) => $row['stats']['is_banned'] ? 1 : 0)
            ->values();

        // Tất cả học viên (sắp xếp theo tên A-Z)
        $allStudents = $members
            ->map(fn ($m) => ['member' => $m, 'stats' => $statsMap[$m->id] ?? null])
            ->sortBy(function ($row) {
                $parts = explode(' ', trim($row['member']->full_name));
                return end($parts);
            })
            ->values();

        // Dữ liệu điểm danh theo từng buổi đã chốt (cho biểu đồ)
        $sessionChart = $closedSessions->map(function ($session) use ($totalStudents) {
            $presentCount = AttendanceRecord::where('class_session_id', $session->id)
                ->whereIn('status', ['present', 'late'])
                ->count();

            $attendanceRate = $totalStudents > 0
                ? (int) round(($presentCount / $totalStudents) * 100)
                : 0;

            return [
                'name'            => $session->name,
                'date'            => $session->date?->format('d/m'),
                'present'         => $presentCount,
                'total'           => $totalStudents,
                'attendance_rate' => $attendanceRate,
            ];
        })->values()->all();

        return view('livewire.lecturer.class-statistics', [
            'class'           => $class,
            'totalStudents'   => $totalStudents,
            'totalSessions'   => $totalSessions,
            'closedCount'     => $closedSessions->count(),
            'studiedSessions'  => $studiedSessions,
            'plannedSessions'  => $plannedSessions,
            'avgAttendance'   => $avgAttendance,
            'bannedCount'     => $bannedCount,
            'warningCount'    => $warningCount,
            'allowedAbsent'   => $allowedAbsent,
            'alertStudents'   => $alertStudents,
            'allStudents'     => $allStudents,
            'sessionChart'    => $sessionChart,
        ])->layout('layouts.fullscreen', [
            'title' => 'Thống kê',
            'subtitle' => $class->code . ' - ' . $class->name,
            'backUrl' => route('lecturer.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $class->id])
        ]);
    }
}
