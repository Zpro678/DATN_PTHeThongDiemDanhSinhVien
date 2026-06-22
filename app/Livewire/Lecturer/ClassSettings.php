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

    // Trạng thái hiển thị modal xác nhận tạo lại mã lớp học mới
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
        $this->totalLessons = $courseClass->total_lessons;
        $this->requireApproval = $courseClass->require_approval;
        $this->status = $courseClass->status;

        $this->gpsEnabled = $courseClass->gps_latitude !== null;
        $this->gpsLatitude = $courseClass->gps_latitude;
        $this->gpsLongitude = $courseClass->gps_longitude;
        $this->gpsRadius = $courseClass->gps_radius ?? 50;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'subjectCode' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'totalLessons' => ['required', 'integer', 'min:1', 'max:300'],
            'requireApproval' => ['boolean'],
            'status' => ['required', 'string', Rule::in(['active', 'archived', 'ended'])],
            'gpsEnabled' => ['boolean'],
            'gpsLatitude' => ['required_if:gpsEnabled,true', 'nullable', 'numeric'],
            'gpsLongitude' => ['required_if:gpsEnabled,true', 'nullable', 'numeric'],
            'gpsRadius' => ['required_if:gpsEnabled,true', 'integer', 'min:5', 'max:2500'],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'totalLessons.min' => 'Tổng số tiết phải lớn hơn 0.',
            'gpsLatitude.required_if' => 'Vui lòng lấy tọa độ GPS khi kích hoạt định vị.',
            'gpsLongitude.required_if' => 'Vui lòng lấy tọa độ GPS khi kích hoạt định vị.',
            'gpsRadius.required_if' => 'Vui lòng nhập bán kính GPS.',
            'gpsRadius.min' => 'Bán kính tối thiểu là 5m.',
            'gpsRadius.max' => 'Bán kính tối đa là 2500m.',
        ]);

        $this->courseClass->update([
            'name' => $validated['name'],
            'subject_code' => filled($validated['subjectCode']) ? strtoupper($validated['subjectCode']) : null,
            'semester' => $validated['semester'] ?: null,
            'description' => $validated['description'] ?: null,
            'total_lessons' => $validated['totalLessons'],
            'require_approval' => $validated['requireApproval'],
            'status' => $validated['status'],
            'gps_latitude' => $validated['gpsEnabled'] ? $validated['gpsLatitude'] : null,
            'gps_longitude' => $validated['gpsEnabled'] ? $validated['gpsLongitude'] : null,
            'gps_radius' => $validated['gpsEnabled'] ? $validated['gpsRadius'] : null,
        ]);

        session()->flash('status', 'Cài đặt lớp học đã được cập nhật.');

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
            ->layout('layouts.user', ['title' => 'Cài đặt lớp học - '.$this->courseClass->code]);
    }
}
