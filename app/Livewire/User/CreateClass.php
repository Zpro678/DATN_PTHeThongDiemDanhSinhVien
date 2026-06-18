<?php

namespace App\Livewire\User;

use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateClass extends Component
{
    public string $name = '';

    public string $code = '';

    public string $subjectCode = '';

    public string $semester = '';

    public string $description = '';

    public int $totalSessions = 15;

    public int $lessonsPerSession = 3;

    public bool $requireApproval = false;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('classes', 'code')],
            'subjectCode' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'totalSessions' => ['required', 'integer', 'min:1', 'max:100'],
            'lessonsPerSession' => ['required', 'integer', 'min:1', 'max:20'],
            'requireApproval' => ['boolean'],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'code.required' => 'Vui lòng nhập mã lớp.',
            'code.unique' => 'Mã lớp đã tồn tại trên hệ thống.',
            'totalSessions.min' => 'Tổng số buổi phải lớn hơn 0.',
            'lessonsPerSession.min' => 'Số tiết mỗi buổi phải lớn hơn 0.',
        ]);

        CourseClass::query()->create([
            'owner_user_id' => auth()->id(),
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'subject_code' => filled($validated['subjectCode']) ? strtoupper($validated['subjectCode']) : null,
            'semester' => $validated['semester'] ?: null,
            'description' => $validated['description'] ?: null,
            'total_sessions' => $validated['totalSessions'],
            'lessons_per_session' => $validated['lessonsPerSession'],
            'require_approval' => $validated['requireApproval'],
            'status' => 'active',
        ]);

        session()->flash('status', 'Lớp học đã được tạo thành công.');

        $this->redirectRoute('managed-classes', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.user.create-class')
            ->layout('layouts.user', ['title' => 'Tạo lớp mới']);
    }
}
