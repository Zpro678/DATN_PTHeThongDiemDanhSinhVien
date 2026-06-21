<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use App\Services\DashboardStatisticService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public string $workspace = 'admin';

    public bool $showCreateModal = false;

    public bool $showJoinModal = false;

    public string $joinStep = 'input';

    public array $overview = [];

    public array $classes = [];

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

        //
        //     'total_students' => 40,
        //     'total_required_lessons' => 45,
        //     'total_studied_lessons' => 20,
        //     'remaining_lessons' => 25,
        //     'lesson_progress_percent' => 44.44,
        //     'total_present' => 120,
        //     'total_absent' => 15,
        //     'classes_progress' => [...]
        // ]
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

    public function openJoinModal(): void
    {
        $this->joinStep = 'input';
        $this->showJoinModal = true;
    }

    public function closeJoinModal(): void
    {
        $this->showJoinModal = false;
    }

    public function previewJoinClass(): void
    {
        $this->joinStep = 'preview';
    }

    public function backToJoinInput(): void
    {
        $this->joinStep = 'input';
    }

    public function render(): View
    {
        return view('livewire.user.dashboard')
            ->layout('layouts.user', ['title' => 'Tổng quan']);
    }
}
