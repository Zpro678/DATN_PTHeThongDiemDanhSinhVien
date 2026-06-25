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
    }

    public function updatedClassId(): void
    {
        $this->loadStatistics();
    }

    public function loadStatistics(): void
    {
        $userId = auth()->id();
        $classId = $this->classId ? (int) $this->classId : null;

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
            ->withCount(['members as students_count' => fn ($query) => $query->where('status', 'active')])
            ->withSum(['sessions as studied_lessons' => fn ($query) => $query->where('status', 'closed')], 'lesson_count')
            ->orderByDesc('updated_at') // Lớp nào vừa có tương tác mới nhất (tạo phiên, sửa thông tin, thêm học viên...) sẽ lên đầu
            ->take(3)
            ->get(['id', 'code', 'name', 'subject_code', 'semester', 'status', 'total_lessons'])
            ->values()
            ->map(function (CourseClass $courseClass, int $index) use ($studentService, $cardStyles, $userId) {
                $style = $cardStyles[$index % count($cardStyles)];
                $attendance = $studentService->getTotalAttendanceStats($userId, $courseClass->id);
                $studiedLessons = (int) ($courseClass->studied_lessons ?? 0);

                return [
                    'id' => $courseClass->id, // ID lớp để điều hướng sang chi tiết/điểm danh.
                    'title' => $courseClass->name, // Tên lớp hiển thị trên thẻ.
                    'code' => $courseClass->code, // Mã lớp.
                    'subject_code' => $courseClass->subject_code ?: 'N/A', // Mã học phần nếu có.
                    'semester' => $courseClass->semester ?: 'Chưa xác định', // Học kỳ của lớp.
                    'students' => (int) $courseClass->students_count, // Tổng sinh viên active trong lớp.
                    'lessons' => $studiedLessons.'/'.$courseClass->total_lessons, // Tiến độ số tiết đã học/tổng số tiết.
                    'attendance' => $attendance['attendance_percent'], // Chuyên cần trung bình của cả lớp.
                    'status_label' => in_array($courseClass->status, ['ended', 'archived'], true) ? 'Đã kết thúc' : 'Đang học',
                    ...$style,
                ];
            })
            ->toArray();
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
