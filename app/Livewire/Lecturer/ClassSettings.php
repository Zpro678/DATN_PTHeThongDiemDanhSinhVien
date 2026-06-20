<?php

namespace App\Livewire\Lecturer;

use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ClassSettings extends Component
{
    public CourseClass $courseClass;

    public string $name = '';
    public string $code = '';
    public string $subjectCode = '';
    public string $semester = '';
    public string $description = '';
    public int $totalSessions = 15;
    public int $lessonsPerSession = 3;
    public bool $requireApproval = false;
    public string $status = 'active';

    public bool $isConfirmingDelete = false;
    public bool $isConfirmingRegenCode = false;

    public function mount(CourseClass $courseClass): void
    {
        if ($courseClass->owner_user_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền quản lý lớp này.');
        }

        $this->courseClass = $courseClass;
        
        $this->name = $courseClass->name;
        $this->code = $courseClass->code;
        $this->subjectCode = $courseClass->subject_code ?? '';
        $this->semester = $courseClass->semester ?? '';
        $this->description = $courseClass->description ?? '';
        $this->totalSessions = $courseClass->total_sessions;
        $this->lessonsPerSession = $courseClass->lessons_per_session;
        $this->requireApproval = $courseClass->require_approval;
        $this->status = $courseClass->status;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'subjectCode' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'totalSessions' => ['required', 'integer', 'min:1', 'max:100'],
            'lessonsPerSession' => ['required', 'integer', 'min:1', 'max:20'],
            'requireApproval' => ['boolean'],
            'status' => ['required', 'string', Rule::in(['active', 'archived', 'ended'])],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'totalSessions.min' => 'Tổng số buổi phải lớn hơn 0.',
            'lessonsPerSession.min' => 'Số tiết mỗi buổi phải lớn hơn 0.',
        ]);

        $this->courseClass->update([
            'name' => $validated['name'],
            'subject_code' => filled($validated['subjectCode']) ? strtoupper($validated['subjectCode']) : null,
            'semester' => $validated['semester'] ?: null,
            'description' => $validated['description'] ?: null,
            'total_sessions' => $validated['totalSessions'],
            'lessons_per_session' => $validated['lessonsPerSession'],
            'require_approval' => $validated['requireApproval'],
            'status' => $validated['status'],
        ]);

        session()->flash('status', 'Cài đặt lớp học đã được cập nhật.');
        
        // Refresh the model properties just in case
        $this->courseClass->refresh();
    }

    // ─── Đổi mã lớp ────────────────────────────────────────────────────────────

    public function confirmRegenCode(): void
    {
        $this->isConfirmingRegenCode = true;
    }

    public function closeRegenCodeConfirm(): void
    {
        $this->isConfirmingRegenCode = false;
    }

    public function regenerateCode(): void
    {
        if (! $this->isConfirmingRegenCode) {
            return;
        }

        $newCode = CourseClass::generateUniqueCode(
            $this->courseClass->subject_code ?? '',
            $this->courseClass->semester ?? '',
            $this->courseClass->id,
        );

        $this->courseClass->update(['code' => $newCode]);
        $this->courseClass->refresh();

        $this->code = $this->courseClass->code;
        $this->isConfirmingRegenCode = false;

        session()->flash('status', 'Mã lớp đã được đổi thành công. Vui lòng thông báo mã mới cho sinh viên.');
    }

    // ─── Xoá lớp ────────────────────────────────────────────────────────────────

    public function confirmDelete(): void
    {
        $this->isConfirmingDelete = true;
    }

    public function closeDeleteConfirm(): void
    {
        $this->isConfirmingDelete = false;
    }

    public function deleteClass(): void
    {
        if (!$this->isConfirmingDelete) {
            return;
        }

        $this->courseClass->delete();
        session()->flash('status', 'Đã xóa lớp học thành công.');
        $this->redirectRoute('managed-classes', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.lecturer.class-settings')
            ->layout('layouts.user', ['title' => 'Cài đặt lớp học - ' . $this->courseClass->code]);
    }
}
