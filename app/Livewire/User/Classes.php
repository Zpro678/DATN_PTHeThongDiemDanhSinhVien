<?php

namespace App\Livewire\User;

use App\Models\ClassMember;
use App\Models\ClassJoinRequest;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Classes extends Component
{
    public function render(): View
    {
        $userId = auth()->id();

        $totalClasses = CourseClass::where('owner_user_id', $userId)->count();

        $totalStudents = ClassMember::whereHas('courseClass', function ($q) use ($userId) {
            $q->where('owner_user_id', $userId);
        })->where('status', ClassMember::STATUS_ACTIVE)->count();

        $sessionsToday = ClassSession::whereHas('courseClass', function ($q) use ($userId) {
            $q->where('owner_user_id', $userId);
        })->whereDate('created_at', today())->count();

        $pendingLeaves = LeaveRequest::whereHas('classMember.courseClass', function ($q) use ($userId) {
            $q->where('owner_user_id', $userId);
        })->whereIn('status', [ClassJoinRequest::STATUS_PENDING, 'pending'])->count();

        $stats = [
            ['label' => 'Tổng lớp', 'value' => $totalClasses, 'icon' => 'book-open', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
            ['label' => 'Tổng sinh viên', 'value' => $totalStudents, 'icon' => 'users', 'color' => 'text-tertiary', 'bg' => 'bg-tertiary/10'],
            ['label' => 'Buổi điểm danh nay', 'value' => $sessionsToday, 'icon' => 'user-check', 'color' => 'text-secondary', 'bg' => 'bg-secondary/10'],
            ['label' => 'Đơn nghỉ chờ duyệt', 'value' => $pendingLeaves, 'icon' => 'clock', 'color' => 'text-error', 'bg' => 'bg-error/10'],
        ];

        $classesQuery = CourseClass::where('owner_user_id', $userId)
            ->where('status', 'active')
            ->withCount([
                'members as students_count' => function ($q) {
                    $q->where('status', ClassMember::STATUS_ACTIVE);
                },
                'sessions as sessions_completed' => function ($q) {
                    $q->whereIn('status', ['closed', 'active']);
                },
            ])
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        $classes = $classesQuery->map(function ($class, $index) {
            $colors = [
                ['bg' => 'bg-primary', 'hover' => 'group-hover:text-primary'],
                ['bg' => 'bg-tertiary', 'hover' => 'group-hover:text-tertiary'],
                ['bg' => 'bg-secondary', 'hover' => 'group-hover:text-secondary'],
            ];
            $color = $colors[$index % 3];

            $sessionsCompleted = $class->sessions_completed ?? 0;
            $attendancePct = $class->total_sessions > 0
                ? min(100, round(($sessionsCompleted / $class->total_sessions) * 100))
                : 0;

            return [
                'id' => $class->id,
                'title' => $class->name,
                'code' => $class->join_key,
                'status' => $class->status === 'ended' ? 'Đã kết thúc' : 'Đang hoạt động',
                'students' => $class->students_count,
                'attendance' => $attendancePct,
                'icon' => 'book',
                'bg' => $color['bg'],
                'bar' => $color['bg'],
                'hover' => $color['hover'],
            ];
        })->toArray();

        return view('livewire.user.classes', compact('stats', 'classes'))
            ->layout('layouts.user', ['title' => 'Tổng quan Giảng viên']);
    }
}
