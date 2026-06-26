<?php

namespace App\Livewire\Lecturer;

use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ClassSettings extends Component
{
    // Model lưu trữ thông tin của lớp học hiện tại đang được chỉnh sửa
    public CourseClass $courseClass;

    // Tên của lớp học
    public string $name = '';

    // Mã lớp học (duy nhất để tham gia lớp)
    public string $code = '';

    // Mã môn học
    public string $subjectCode = '';

    // Học kỳ của lớp học
    public string $semester = '';

    // Mô tả chi tiết về lớp học
    public string $description = '';

    // Tổng số tiết/buổi học dự kiến
    public int $totalLessons = 45;

    // Ngưỡng thời gian đi muộn (phút)
    public int $lateThreshold = 15;

    // Số lần muộn quy đổi thành 1 lần vắng (0 = không quy đổi)
    public int $latesPerAbsent = 0;

    // Có trừ điểm chuyên cần khi vắng có phép hay không
    public bool $deductExcusedAbsence = false;

    // Yêu cầu duyệt khi sinh viên tham gia
    public bool $requireApproval = false;

    // Trạng thái của lớp học (active, archived, ended)
    public string $status = 'active';



    // Trạng thái hiển thị modal xác nhận xóa lớp học
    public bool $isConfirmingDelete = false;

    public function mount(CourseClass $courseClass): void
    {
        // Kiểm tra quyền — chỉ chủ lớp mới được xem
        abort_unless(
            $courseClass->owner_user_id === auth()->id(),
            403,
            'Bạn không có quyền chỉnh sửa lớp học này.'
        );

        $this->courseClass = $courseClass;

        $this->name = $courseClass->name;
        $this->code = $courseClass->code;
        $this->subjectCode = $courseClass->subject_code ?? '';
        $this->semester = $courseClass->semester ?? '';
        $this->description = $courseClass->description ?? '';
        $this->totalLessons = $courseClass->total_lessons;
        $this->lateThreshold = $courseClass->late_threshold ?? 15;
        $this->latesPerAbsent = $courseClass->lates_per_absent ?? 0;
        $this->deductExcusedAbsence = (bool) $courseClass->deduct_excused_absence;
        $this->requireApproval = $courseClass->require_approval;
        $this->status = $courseClass->status;


    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('classes', 'code')->ignore($this->courseClass->id)],
            'subjectCode' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'totalLessons' => ['required', 'integer', 'min:1', 'max:300'],
            'lateThreshold' => ['required', 'integer', 'in:5,10,15,20,30'],
            'latesPerAbsent' => ['required', 'integer', 'in:0,1,2,3,4,5'],
            'deductExcusedAbsence' => ['boolean'],
            'requireApproval' => ['boolean'],
            'status' => ['required', 'string', Rule::in(['active', 'archived', 'ended'])],

        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'code.required' => 'Mã lớp không được để trống.',
            'code.unique' => 'Mã lớp đã tồn tại.',
            'totalLessons.min' => 'Tổng số tiết phải lớn hơn 0.',

        ]);

        $this->courseClass->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'subject_code' => filled($validated['subjectCode']) ? strtoupper($validated['subjectCode']) : null,
            'semester' => $validated['semester'] ?: null,
            'description' => $validated['description'] ?: null,
            'late_threshold' => $validated['lateThreshold'],
            'lates_per_absent' => $validated['latesPerAbsent'],
            'deduct_excused_absence' => $validated['deductExcusedAbsence'],
            'total_lessons' => $validated['totalLessons'],
            'require_approval' => $validated['requireApproval'],
            'status' => $validated['status'],

        ]);

        session()->flash('status', 'Cài đặt lớp học đã được cập nhật.');
        $this->redirectRoute('lecturer.classes.show', $this->courseClass, navigate: true);
    }

    public function regenerateCode(): void
    {
        $this->code = CourseClass::generateUniqueCode($this->subjectCode, $this->semester, $this->courseClass->id);
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
        if (! $this->isConfirmingDelete) {
            return;
        }

        $this->courseClass->delete();
        session()->flash('status', 'Đã xóa lớp học thành công.');
        $this->redirectRoute('managed-classes', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.lecturer.class-settings')
            ->layout('layouts.user', ['title' => 'Cài đặt · ' . $this->courseClass->name]);
    }
}
