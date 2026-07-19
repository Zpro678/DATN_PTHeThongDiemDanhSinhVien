<?php

namespace App\Livewire\Lecturer;

use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ClassSettings extends Component
{
    use AuthorizesRequests;

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

    // Tổng số buổi dự kiến của môn học (dùng để tính quỹ vắng và tiến độ).
    public int $totalSessions = 15;

    // Quỹ vắng cho phép, tính theo % tổng số buổi. Ngưỡng cấm thi = 100 - giá trị này.
    public float $absenceLimitPercent = 20;

    // Cảnh báo sớm hơn ngưỡng cấm thi bao nhiêu % chuyên cần.
    public float $warningMarginPercent = 5;

    // Còn bao nhiêu buổi trong quỹ vắng thì gửi thông báo "sắp vượt ngưỡng".
    public int $nearAbsenceSessions = 2;

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
        // Chủ chính và đồng chủ đều mở được trang này. Quyền trên từng thao tác do
        // CourseClassPolicy quyết định — mỗi action bên dưới tự authorize, KHÔNG dựa
        // vào lá chắn ở mount(), vì mỗi method Livewire là một endpoint gọi được trực tiếp.
        $this->authorize('view', $courseClass);

        $this->courseClass = $courseClass;

        $this->name = $courseClass->name;
        $this->classCode = $courseClass->class_code ?? $courseClass->join_key;
        $this->join_key = $courseClass->join_key;
        $this->description = $courseClass->description ?? '';
        $this->totalSessions = $courseClass->total_sessions ?? 15;
        $this->deductExcusedAbsence = (bool) $courseClass->deduct_excused_absence;
        $this->attendanceRules = $courseClass->getAttendanceRules();

        $thresholds = $courseClass->getAttendanceThresholds();
        $this->absenceLimitPercent = $thresholds['absence_limit_percent'];
        $this->warningMarginPercent = $thresholds['warning_percent'] - $thresholds['min_attendance_percent'];
        $this->nearAbsenceSessions = $thresholds['near_absence_sessions'];

        $this->requireApproval = $courseClass->require_approval;
        $this->status = $courseClass->status;
    }

    public function save()
    {
        $this->authorize('update', $this->courseClass);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'classCode' => ['nullable', 'string', 'max:50'],
            'join_key' => ['required', 'string', 'max:20', Rule::unique('classes', 'join_key')->ignore($this->courseClass->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'totalSessions' => ['required', 'integer', 'min:1', 'max:200'],
            'absenceLimitPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'warningMarginPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'nearAbsenceSessions' => ['required', 'integer', 'min:0', 'max:50'],
            'attendanceRules' => ['required', 'array'],
            'attendanceRules.late' => ['required', 'numeric', 'min:0', 'max:10'],
            'attendanceRules.absent' => ['required', 'numeric', 'min:0', 'max:10'],
            'attendanceRules.excused' => ['required', 'numeric', 'min:0', 'max:10'],
            'requireApproval' => ['boolean'],
            'status' => ['required', 'string', Rule::in(['active', 'archived', 'ended'])],
        ], [
            'name.required' => 'Vui lòng nhập tên lớp.',
            'join_key.required' => 'Mã lớp không được để trống.',
            'join_key.unique' => 'Mã lớp đã tồn tại.',
            'totalSessions.required' => 'Vui lòng nhập tổng số buổi dự kiến.',
            'totalSessions.min' => 'Tổng số buổi dự kiến phải từ 1 trở lên.',
            'totalSessions.max' => 'Tổng số buổi dự kiến tối đa là 200.',
            'absenceLimitPercent.required' => 'Vui lòng nhập ngưỡng vắng cho phép.',
            'absenceLimitPercent.min' => 'Ngưỡng vắng cho phép không được nhỏ hơn 0%.',
            'absenceLimitPercent.max' => 'Ngưỡng vắng cho phép tối đa là 100%.',
            'warningMarginPercent.required' => 'Vui lòng nhập biên cảnh báo.',
            'warningMarginPercent.max' => 'Biên cảnh báo tối đa là 100%.',
            'nearAbsenceSessions.required' => 'Vui lòng nhập số buổi còn lại để cảnh báo.',
            'nearAbsenceSessions.max' => 'Số buổi cảnh báo tối đa là 50.',
            'attendanceRules.late.required' => 'Vui lòng nhập điểm trừ khi đi muộn.',
            'attendanceRules.absent.required' => 'Vui lòng nhập điểm trừ khi vắng.',
            'attendanceRules.excused.required' => 'Vui lòng nhập điểm trừ khi vắng có phép.',
        ]);

        // Điểm trừ có phép > 0 nghĩa là "trừ chuyên cần khi vắng có phép" (giữ đồng bộ cờ cũ
        // để các truy vấn/thống kê đọc deduct_excused_absence vẫn đúng).
        $deductExcused = (float) $validated['attendanceRules']['excused'];

        $this->courseClass->update([
            'name' => $validated['name'],
            'class_code' => $validated['classCode'] ?: strtoupper($validated['join_key']),
            'join_key' => strtoupper($validated['join_key']),
            'description' => $validated['description'] ?: null,
            'total_sessions' => $validated['totalSessions'],
            'absence_limit_percent' => $validated['absenceLimitPercent'],
            'warning_margin_percent' => $validated['warningMarginPercent'],
            'near_absence_sessions' => $validated['nearAbsenceSessions'],
            'deduct_late' => (float) $validated['attendanceRules']['late'],
            'deduct_absent' => (float) $validated['attendanceRules']['absent'],
            'deduct_excused' => $deductExcused,
            'deduct_excused_absence' => $deductExcused > 0,
            'require_approval' => $validated['requireApproval'],
            'status' => $validated['status'],
        ]);

        session()->flash('status', 'Cài đặt lớp học đã được cập nhật.');
        $this->redirectRoute('lecturer.classes.show', $this->courseClass, navigate: true);
    }

    public function regenerateCode(): void
    {
        $this->authorize('update', $this->courseClass);

        $this->join_key = CourseClass::generateUniqueCode('', $this->courseClass->id);
    }

    // ─── Xoá lớp ────────────────────────────────────────────────────────────────

    public function confirmDelete(): void
    {
        $this->authorize('delete', $this->courseClass);

        $this->isConfirmingDelete = true;
    }

    public function closeDeleteConfirm(): void
    {
        $this->isConfirmingDelete = false;
    }

    public function deleteClass(): void
    {
        $this->authorize('delete', $this->courseClass);

        if (! $this->isConfirmingDelete) {
            return;
        }

        $this->courseClass->update(['status' => 'archived']);
        session()->flash('status', 'Đã chuyển lớp học vào Lưu trữ thành công.');
        $this->redirectRoute('managed-classes');
    }

    // ─── Đồng chủ lớp ─────────────────────────────────────────────────────────────

    public function addCoOwner(): void
    {
        // Chỉ chủ chính được thêm đồng chủ.
        $this->authorize('manageCoOwners', $this->courseClass);

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

        // Tài khoản quản trị (ADMIN / SUPER_ADMIN) chỉ hoạt động ở khu vực admin,
        // không được mời làm đồng chủ lớp (đồng chủ là vai trò của khu vực user).
        if ($user->isAdmin()) {
            $this->addError('coOwnerEmail', 'Không thể thêm tài khoản quản trị làm đồng chủ lớp.');
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

        $subscriptionService = app(\App\Services\SubscriptionService::class);
        $maxClasses = $subscriptionService->maxClasses($user);

        if ($maxClasses !== null) {
            $managedCount = CourseClass::managedBy($user->id)->where('status', '!=', 'archived')->count();

            if ($managedCount >= $maxClasses) {
                $this->addError('coOwnerEmail', 'Người này đã quản lý tối đa ' . $maxClasses . ' lớp theo gói hiện tại, không thể thêm làm đồng chủ.');
                return;
            }
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
        $this->dispatch('toast', message: 'Đã thêm đồng chủ lớp thành công.', type: 'success');
    }

    public function removeCoOwner(int $userId): void
    {
        $this->authorize('manageCoOwners', $this->courseClass);

        $this->courseClass->coOwners()->detach($userId);

        $this->dispatch('toast', message: 'Đã gỡ đồng chủ khỏi lớp.', type: 'success');
    }

    public function render(): View
    {
        $coOwners = $this->courseClass->coOwners()->get();

        // Đồng chủ cần biết ai là chủ chính để liên hệ khi cần thao tác ngoài quyền của mình.
        $primaryOwner = $this->courseClass->owner;

        return view('livewire.lecturer.class-settings', compact('coOwners', 'primaryOwner'))
            ->layout('layouts.user', ['title' => 'Cài đặt · ' . $this->courseClass->name]);
    }
}
