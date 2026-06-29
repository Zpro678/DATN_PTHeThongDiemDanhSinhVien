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
        $this->code = $courseClass->code;
        $this->subjectCode = $courseClass->subject_code ?? '';
        $this->semester = $courseClass->semester ?? '';
        $this->description = $courseClass->description ?? '';
        $this->lateThreshold = $courseClass->late_threshold ?? 15;
        $rules = $courseClass->getAttendanceRules();
        $this->deductExcusedAbsence = ($rules['excused'] ?? 0) > 0;
        $this->attendanceRules = $rules;
        $this->requireApproval = $courseClass->require_approval;
        $this->status = $courseClass->status;

        $this->gpsEnabled = $courseClass->gps_latitude !== null;
        $this->gpsLatitude = $courseClass->gps_latitude ? (float) $courseClass->gps_latitude : null;
        $this->gpsLongitude = $courseClass->gps_longitude ? (float) $courseClass->gps_longitude : null;
        $this->gpsRadius = $courseClass->gps_radius ? (int) $courseClass->gps_radius : null;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('classes', 'code')->ignore($this->courseClass->id)],
            'subjectCode' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'lateThreshold' => ['required', 'integer', 'min:0', 'max:300'],
            'attendanceRules' => ['required', 'array'],
            'attendanceRules.present' => ['required', 'numeric', 'max:0'],
            'attendanceRules.late' => ['required', 'numeric'],
            'attendanceRules.partial' => ['required', 'numeric'],
            'attendanceRules.early_leave' => ['required', 'numeric'],
            'attendanceRules.absent' => ['required', 'numeric'],
            'attendanceRules.excused' => ['required', 'numeric'],
            'deductExcusedAbsence' => ['boolean'],
            'requireApproval' => ['boolean'],
            'status' => ['required', 'string', Rule::in(['active', 'archived', 'ended'])],
            'gpsEnabled' => ['boolean'],
            'gpsLatitude' => ['nullable', 'numeric'],
            'gpsLongitude' => ['nullable', 'numeric'],
            'gpsRadius' => ['nullable', 'integer', 'min:10', 'max:5000'],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'code.required' => 'Mã lớp không được để trống.',
            'code.unique' => 'Mã lớp đã tồn tại.',

        ]);

        $rules = $this->attendanceRules;
        $rules['excused'] = $validated['deductExcusedAbsence'] ? 1.0 : 0.0;

        $this->courseClass->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'subject_code' => filled($validated['subjectCode']) ? strtoupper($validated['subjectCode']) : null,
            'semester' => $validated['semester'] ?: null,
            'description' => $validated['description'] ?: null,
            'late_threshold' => $validated['lateThreshold'],
            'attendance_rules' => $rules,
            'total_sessions' => 0,
            'require_approval' => $validated['requireApproval'],
            'status' => $validated['status'],
            'gps_latitude' => $validated['gpsEnabled'] ? ($validated['gpsLatitude'] ?? null) : null,
            'gps_longitude' => $validated['gpsEnabled'] ? ($validated['gpsLongitude'] ?? null) : null,
            'gps_radius' => $validated['gpsEnabled'] ? ($validated['gpsRadius'] ?? null) : null,
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
        $this->redirectRoute('managed-classes');
    }

    public function render(): View
    {
        return view('livewire.lecturer.class-settings')
            ->layout('layouts.user', ['title' => 'Cài đặt · ' . $this->courseClass->name]);
    }
}
