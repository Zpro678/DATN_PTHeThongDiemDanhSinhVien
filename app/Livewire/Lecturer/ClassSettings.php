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

    // Mã lớp học phần do giảng viên tự đặt (VD: CS101, WEB-2026-01)
    public string $classCode = '';

    // Mã tham gia lớp (duy nhất — tự sinh ngẫu nhiên)
    public string $join_key = '';

    // Mô tả chi tiết về lớp học
    public string $description = '';

    // Ngưỡng thời gian đi muộn (phút)
    public int $lateThreshold = 15;

    // Có trừ điểm chuyên cần khi vắng có phép hay không (giữ nguyên cờ cũ hoặc đồng bộ với attendanceRules)
    public bool $deductExcusedAbsence = false;

    // Cấu hình bảng điểm trừ chuyên cần
    public array $attendanceRules = [];

    // Yêu cầu duyệt khi sinh viên tham gia
    public bool $requireApproval = false;

    // Trạng thái của lớp học (active, archived, ended)
    public string $status = 'active';

    // Cấu hình GPS mặc định
    public bool $gpsEnabled = false;
    public ?float $gpsLatitude = null;
    public ?float $gpsLongitude = null;
    public ?int $gpsRadius = null;



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
        $this->classCode = $courseClass->class_code ?? $courseClass->join_key;
        $this->join_key = $courseClass->join_key;
        $this->description = $courseClass->description ?? '';
        $this->lateThreshold = $courseClass->late_threshold ?? 15;
        $this->deductExcusedAbsence = (bool) $courseClass->deduct_excused_absence;
        $this->attendanceRules = $courseClass->getAttendanceRules();
        $this->requireApproval = $courseClass->require_approval;
        $this->status = $courseClass->status;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'classCode' => ['nullable', 'string', 'max:50'],
            'join_key' => ['required', 'string', 'max:20', Rule::unique('classes', 'join_key')->ignore($this->courseClass->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'lateThreshold' => ['required', 'integer', 'min:0', 'max:300'],
            'deductExcusedAbsence' => ['boolean'],
            'requireApproval' => ['boolean'],
            'status' => ['required', 'string', Rule::in(['active', 'archived', 'ended'])],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'join_key.required' => 'Mã tham gia lớp không được để trống.',
            'join_key.unique' => 'Mã tham gia lớp đã tồn tại.',
        ]);

        $this->courseClass->update([
            'name' => $validated['name'],
            'class_code' => $validated['classCode'] ?: strtoupper($validated['join_key']),
            'join_key' => strtoupper($validated['join_key']),
            'description' => $validated['description'] ?: null,
            'late_threshold' => $validated['lateThreshold'],
            'deduct_excused_absence' => $validated['deductExcusedAbsence'] ?? false,
            'require_approval' => $validated['requireApproval'],
            'status' => $validated['status'],
        ]);

        session()->flash('status', 'Cài đặt lớp học đã được cập nhật.');
        $this->redirectRoute('lecturer.classes.show', $this->courseClass, navigate: true);
    }

    public function regenerateCode(): void
    {
        $this->join_key = CourseClass::generateUniqueCode('', $this->courseClass->id);
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
        $this->redirectRoute('managed-classes');
    }

    public function render(): View
    {
        return view('livewire.lecturer.class-settings')
            ->layout('layouts.user', ['title' => 'Cài đặt · ' . $this->courseClass->name]);
    }
}
