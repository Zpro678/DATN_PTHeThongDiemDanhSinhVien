<?php

namespace App\Livewire;

use App\Models\CourseClass;
use Livewire\Component;

class ClassSettings extends Component
{
    public CourseClass $class;

    public bool $requireApproval;
    public bool $isActive;

    public function mount(CourseClass $class): void
    {
        $this->class = $class;
        $this->requireApproval = (bool) $class->require_approval;
        $this->isActive = $class->status === 'active';
    }

    public function toggleRequireApproval(): void
    {
        $this->requireApproval = ! $this->requireApproval;

        $this->class->update([
            'require_approval' => $this->requireApproval,
        ]);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => $this->requireApproval
                ? 'Đã bật yêu cầu duyệt sinh viên.'
                : 'Đã tắt yêu cầu duyệt sinh viên.',
        ]);
    }

    public function toggleStatus(): void
    {
        $this->isActive = ! $this->isActive;

        $this->class->update([
            'status' => $this->isActive ? 'active' : 'archived',
        ]);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => $this->isActive
                ? 'Lớp học đã được kích hoạt.'
                : 'Lớp học đã được lưu trữ.',
        ]);
    }

    public function render()
    {
        return view('livewire.class-settings');
    }
}
