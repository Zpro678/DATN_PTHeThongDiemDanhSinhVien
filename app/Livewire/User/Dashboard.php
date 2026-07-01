<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use App\Services\DashboardStatisticService;
use App\Services\LectureManageStudentService;
use App\Services\StudentsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public string $workspace = 'admin';

    public bool $showCreateModal = false;



    public array $overview = [];

    public array $classes = [];

    public array $managedClassCards = [];

    public array $studentDashboard = [];

    public $classId = null;

    public function mount(): void
    {
        $this->loadStatistics();
        $this->workspace = $this->defaultWorkspace();
    }

    public function updatedClassId(): void
    {
        $this->loadStatistics();
    }

    public function loadStatistics(): void
    {
        $userId = auth()->id();
        $classId = $this->classId ?: null;

        $this->classes = CourseClass::query()
            ->where('owner_user_id', $userId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->overview = app(DashboardStatisticService::class)
            ->getOwnerOverview($userId, $classId);

        $this->managedClassCards = $this->loadManagedClassCards($userId);

        $this->studentDashboard = app(StudentsService::class)
            ->getDashboardForStudent($userId);
    }

    private function loadManagedClassCards(int $userId): array
    {
        $studentService = app(LectureManageStudentService::class);
        $cardStyles = [
            ['icon' => 'code', 'color' => 'text-primary', 'bar' => 'bg-primary'],
            ['icon' => 'database', 'color' => 'text-tertiary', 'bar' => 'bg-tertiary'],
            ['icon' => 'monitor', 'color' => 'text-secondary', 'bar' => 'bg-secondary'],
        ];

        return CourseClass::query()
            ->where('owner_user_id', $userId)
            ->where('status', 'active') // Chỉ hiển thị các lớp đang hoạt động
            ->withCount(['members as students_count' => fn ($query) => $query->where('status', \App\Models\ClassMember::STATUS_ACTIVE)])
            ->withCount(['meetings as studied_sessions' => fn ($query) => $query->whereHas('sessions', fn ($s) => $s->where('status', 'closed'))])
            ->orderByDesc('updated_at') // Lớp nào vừa có tương tác mới nhất (tạo phiên, sửa thông tin, thêm học viên...) sẽ lên đầu
            ->take(3)
            ->get(['id', 'join_key', 'name', 'status', 'total_sessions'])
            ->values()
            ->map(function (CourseClass $courseClass, int $index) use ($studentService, $cardStyles, $userId) {
                $style = $cardStyles[$index % count($cardStyles)];
                $attendance = $studentService->getTotalAttendanceStats($userId, $courseClass->id);
                $studiedSessions = (int) ($courseClass->studied_sessions ?? 0);
                $hasAttendanceData = (int) $courseClass->students_count > 0 && $studiedSessions > 0;
                $attendancePercent = (float) $attendance['attendance_percent'];

                return [
                    'id' => $courseClass->id, // ID lớp để điều hướng sang chi tiết/điểm danh.
                    'title' => $courseClass->name, // Tên lớp hiển thị trên thẻ.
                    'code' => $courseClass->join_key, // Mã lớp.
                    'students' => (int) $courseClass->students_count, // Tổng sinh viên active trong lớp.
                    'studied_sessions' => $studiedSessions, // Số buổi đã chốt.
                    'sessions' => $courseClass->total_sessions > 0 ? $studiedSessions.'/'.$courseClass->total_sessions : $studiedSessions.' buổi', // Tiến độ số buổi đã học/tổng số buổi.
                    'attendance' => $attendancePercent, // Chuyên cần trung bình của cả lớp.
                    'attendance_label' => $hasAttendanceData ? $attendancePercent.'%' : 'Chưa có dữ liệu',
                    'attendance_bar_width' => $hasAttendanceData ? $attendancePercent : 0,
                    'status_label' => in_array($courseClass->status, ['ended', 'archived'], true) ? 'Đã kết thúc' : 'Đang học',
                    ...$style,
                ];
            })
            ->toArray();
    }

    private function defaultWorkspace(): string
    {
        $ownedClasses = count($this->classes);
        $joinedClasses = (int) data_get($this->studentDashboard, 'stats.joined_classes', 0);

        return $ownedClasses === 0 && $joinedClasses > 0 ? 'student' : 'admin';
    }

    public function setWorkspace(string $workspace): void
    {
        if (in_array($workspace, ['admin', 'student'], true)) {
            $this->workspace = $workspace;
        }
    }

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    #[\Livewire\Attributes\On('class-joined')]
    public function refreshDashboardStats(): void
    {
        $this->loadStatistics();
    }

    public function render(): View
    {
        return view('livewire.user.dashboard')
            ->layout('layouts.user', ['title' => 'Tổng quan']);
    }
}
