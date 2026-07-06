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

    // Tổng số buổi dự kiến của môn học (dùng để tính quỹ vắng 20% và tiến độ).
    public int $totalSessions = 15;

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

    // Email người muốn thêm làm đồng chủ lớp.
    public string $coOwnerEmail = '';

    public function mount(CourseClass $courseClass): void
    {
        // Cài đặt lớp (đổi cấu hình, xóa lớp, quản lý đồng chủ) chỉ dành cho CHỦ CHÍNH.
        // Đồng chủ vẫn quản lý được điểm danh/học viên/đơn nghỉ ở các trang khác, nhưng không đụng cấu hình lớp.
        abort_unless(
            $courseClass->isPrimaryOwner(auth()->id()),
            403,
            'Chỉ chủ chính của lớp mới được chỉnh sửa cài đặt.'
        );

        $this->courseClass = $courseClass;

        $this->name = $courseClass->name;
        $this->classCode = $courseClass->class_code ?? $courseClass->join_key;
        $this->join_key = $courseClass->join_key;
        $this->description = $courseClass->description ?? '';
        $this->lateThreshold = $courseClass->late_threshold ?? 15;
        $this->totalSessions = $courseClass->total_sessions ?? 15;
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
            'totalSessions' => ['required', 'integer', 'min:1', 'max:200'],
            'deductExcusedAbsence' => ['boolean'],
            'requireApproval' => ['boolean'],
            'status' => ['required', 'string', Rule::in(['active', 'archived', 'ended'])],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'join_key.required' => 'Mã lớp không được để trống.',
            'join_key.unique' => 'Mã lớp đã tồn tại.',
            'totalSessions.required' => 'Vui lòng nhập tổng số buổi dự kiến.',
            'totalSessions.min' => 'Tổng số buổi dự kiến phải từ 1 trở lên.',
            'totalSessions.max' => 'Tổng số buổi dự kiến tối đa là 200.',
        ]);

        $this->courseClass->update([
            'name' => $validated['name'],
            'class_code' => $validated['classCode'] ?: strtoupper($validated['join_key']),
            'join_key' => strtoupper($validated['join_key']),
            'description' => $validated['description'] ?: null,
            'late_threshold' => $validated['lateThreshold'],
            'total_sessions' => $validated['totalSessions'],
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

    // ─── Đồng chủ lớp ─────────────────────────────────────────────────────────────

    public function addCoOwner(): void
    {
        // Chỉ chủ chính được thêm đồng chủ.
        abort_unless($this->courseClass->isPrimaryOwner(auth()->id()), 403);

        $this->validate([
            'coOwnerEmail' => ['required', 'email'],
        ], [
            'coOwnerEmail.required' => 'Vui lòng nhập email người muốn thêm.',
            'coOwnerEmail.email' => 'Email không hợp lệ.',
        ]);

        $user = \App\Models\User::where('email', $this->coOwnerEmail)->first();

        if (! $user) {
            $this->addError('coOwnerEmail', 'Không tìm thấy người dùng với email này.');
            return;
        }

        if ($this->courseClass->isPrimaryOwner($user->id)) {
            $this->addError('coOwnerEmail', 'Người này đã là chủ chính của lớp.');
            return;
        }

        if ($this->courseClass->coOwners()->where('users.id', $user->id)->exists()) {
            $this->addError('coOwnerEmail', 'Người này đã là đồng chủ của lớp.');
            return;
        }

        $this->courseClass->coOwners()->attach($user->id, [
            'role' => 'co_owner',
            'invited_by' => auth()->id(),
            'accepted_at' => now(),
        ]);

        // Báo cho người vừa được thêm.
        app(\App\Services\NotificationService::class)->push(
            (int) $user->id,
            'App\\Notifications\\ClassCoOwnerAdded',
            'Bạn được thêm làm đồng chủ lớp',
            'Bạn vừa được thêm làm đồng chủ lớp "' . $this->courseClass->name . '". Bạn có thể quản lý điểm danh, học viên và đơn nghỉ của lớp này.',
            route('lecturer.classes.show', ['ma_user' => $user->id, 'courseClass' => $this->courseClass->id]),
            'info',
            ['class_id' => $this->courseClass->id],
        );

        $this->coOwnerEmail = '';
        session()->flash('coowner_status', 'Đã thêm đồng chủ lớp thành công.');
    }

    public function removeCoOwner(int $userId): void
    {
        abort_unless($this->courseClass->isPrimaryOwner(auth()->id()), 403);

        $this->courseClass->coOwners()->detach($userId);

        session()->flash('coowner_status', 'Đã gỡ đồng chủ khỏi lớp.');
    }

    public function render(): View
    {
        $coOwners = $this->courseClass->coOwners()->get();

        return view('livewire.lecturer.class-settings', compact('coOwners'))
            ->layout('layouts.user', ['title' => 'Cài đặt · ' . $this->courseClass->name]);
    }
}
