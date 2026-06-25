<?php

namespace App\Livewire\Lecturer;

use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ClassSettings extends Component
{
    // Cờ hiển thị modal
    public bool $showModal = false;

    // Model lưu trữ thông tin của lớp học hiện tại đang được chỉnh sửa
    public ?CourseClass $courseClass = null;

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

    // Trạng thái kích hoạt tính năng kiểm tra vị trí GPS khi điểm danh
    public bool $gpsEnabled = false;

    // Vĩ độ (Latitude) lưu trữ tọa độ GPS trung tâm của lớp học
    public ?float $gpsLatitude = null;

    // Kinh độ (Longitude) lưu trữ tọa độ GPS trung tâm của lớp học
    public ?float $gpsLongitude = null;

    // Bán kính cho phép điểm danh (tính bằng mét), sinh viên phải đứng trong vùng này
    public int $gpsRadius = 50;

    // Trạng thái hiển thị modal xác nhận xóa lớp học
    public bool $isConfirmingDelete = false;

    public function mount(): void
    {
        // 
    }

    #[On('open-class-settings')]
    public function openModal(int $classId): void
    {
        $courseClass = CourseClass::find($classId);
        if (! $courseClass || $courseClass->owner_user_id !== auth()->id()) {
            return;
        }

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

        $this->gpsEnabled = $courseClass->gps_latitude !== null;
        $this->gpsLatitude = $courseClass->gps_latitude;
        $this->gpsLongitude = $courseClass->gps_longitude;
        $this->gpsRadius = $courseClass->gps_radius ?? 50;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function save()
    {
        if (!$this->courseClass) return;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('classes', 'code')->ignore($this->courseClass->id)],
            'subjectCode' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'totalLessons' => ['required', 'integer', 'min:1', 'max:300'],
            'lateThreshold' => ['required', 'integer', 'in:5,10,15,20,30'],
            'latesPerAbsent' => ['required', 'integer', 'in:0,2,3,4,5'],
            'deductExcusedAbsence' => ['boolean'],
            'requireApproval' => ['boolean'],
            'status' => ['required', 'string', Rule::in(['active', 'archived', 'ended'])],
            'gpsEnabled' => ['boolean'],
            'gpsLatitude' => ['required_if:gpsEnabled,true', 'nullable', 'numeric'],
            'gpsLongitude' => ['required_if:gpsEnabled,true', 'nullable', 'numeric'],
            'gpsRadius' => ['required_if:gpsEnabled,true', 'integer', 'min:5', 'max:2500'],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'code.required' => 'Mã lớp không được để trống.',
            'code.unique' => 'Mã lớp đã tồn tại.',
            'totalLessons.min' => 'Tổng số tiết phải lớn hơn 0.',
            'gpsLatitude.required_if' => 'Vui lòng lấy tọa độ GPS khi kích hoạt định vị.',
            'gpsLongitude.required_if' => 'Vui lòng lấy tọa độ GPS khi kích hoạt định vị.',
            'gpsRadius.required_if' => 'Vui lòng nhập bán kính GPS.',
            'gpsRadius.min' => 'Bán kính tối thiểu là 5m.',
            'gpsRadius.max' => 'Bán kính tối đa là 2500m.',
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
            'gps_latitude' => $validated['gpsEnabled'] ? $validated['gpsLatitude'] : null,
            'gps_longitude' => $validated['gpsEnabled'] ? $validated['gpsLongitude'] : null,
            'gps_radius' => $validated['gpsEnabled'] ? $validated['gpsRadius'] : null,
        ]);

        $this->closeModal();
        session()->flash('status', 'Cài đặt lớp học đã được cập nhật.');
        $this->dispatch('class-settings-updated');
        $this->redirect(request()->header('Referer'), navigate: true);
    }

    public function regenerateCode(): void
    {
        if (!$this->courseClass) return;
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
        if (! $this->isConfirmingDelete || !$this->courseClass) {
            return;
        }

        $this->courseClass->delete();
        $this->closeModal();
        session()->flash('status', 'Đã xóa lớp học thành công.');
        $this->redirectRoute('managed-classes', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.lecturer.class-settings');
    }
}
