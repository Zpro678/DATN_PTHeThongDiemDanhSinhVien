<?php

namespace App\Livewire\Student;

use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JoinClass extends Component
{
    // Mã lớp học mà sinh viên muốn tham gia
    public $class_code = '';


    // Họ tên đầy đủ của sinh viên
    public $full_name = '';

    public $showModal = false;

    public function mount()
    {
        $this->full_name = Auth::user()->name;
    }

    public $confirmingClass = null;

    #[\Livewire\Attributes\On('open-join-class-modal')]
    public function openModal($code = null)
    {
        $this->full_name = Auth::user()->name;
        // Điền sẵn mã lớp khi mở từ bộ quét QR (QR dạng 2 = join_key).
        if (! empty($code)) {
            $this->class_code = (string) $code;
        }
        if ($code) {
            $this->class_code = $code;
            // Automatically check code if it was passed in
            $this->checkCode();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['class_code', 'confirmingClass']);
        $this->resetValidation();
    }

    public function checkCode()
    {
        $this->validate([
            'class_code' => 'required|string',
        ], [
            'class_code.required' => 'Vui lòng nhập mã lớp.',
        ]);

        $courseClass = CourseClass::where('join_key', $this->class_code)->with('owner')->first();

        if (! $courseClass) {
            $this->addError('class_code', 'Mã lớp không tồn tại hoặc đã hết hạn. Vui lòng kiểm tra lại.');
            return;
        }

        $this->confirmingClass = $courseClass;
    }

    public function cancelConfirm()
    {
        $this->confirmingClass = null;
    }

    public function confirmJoin()
    {
        if (!$this->confirmingClass) {
            return;
        }

        $courseClass = CourseClass::where('id', $this->confirmingClass['id'] ?? $this->confirmingClass->id)->first();

        if (! $courseClass) {
            return;
        }

        $userId = Auth::id();
        $userEmail = Auth::user()->email;

        // Chủ lớp (chủ chính hoặc đồng chủ) không thể tự tham gia lớp mình quản lý với vai trò học viên.
        // Trước đây so sánh $courseClass->teacher_id — cột không tồn tại (owner là owner_user_id) nên
        // điều kiện luôn false, chủ lớp vẫn vào được lớp của chính mình.
        if ($courseClass->isManagedBy($userId)) {
            session()->flash('status', 'Bạn đang là giảng viên của lớp học này.');
            $this->reset(['class_code', 'confirmingClass']);
            $this->showModal = false;

            return $this->redirectRoute('lecturer.classes.show', ['ma_user' => $userId, 'courseClass' => $courseClass->id], navigate: true);
        }

        $existingMember = ClassMember::with('profile')
            ->where('class_id', $courseClass->id)
            ->where(function ($query) use ($userId, $userEmail) {
                $query->where('user_id', $userId)
                    ->orWhereHas('profile', fn ($profile) => $profile->where('email', $userEmail));
            })->first();

        if ($existingMember) {
            if (is_null($existingMember->user_id)) {
                $existingMember->update([
                    'user_id' => $userId,
                    'status' => ClassMember::STATUS_ACTIVE,
                    'status_changed_at' => null,
                ]);
                $existingMember->syncProfile([
                    'full_name' => $this->full_name,
                    'email' => $userEmail,
                ]);

                Auth::user()->notify(new \App\Notifications\ClassJoinedNotification($courseClass));
                $this->notifyClassManagers($courseClass, new \App\Notifications\ClassMemberJoined($courseClass, Auth::user()));
                \App\Events\StudentJoinedClass::dispatch((string) $courseClass->id);

                app(AuditLogService::class)->log('class_joined', [
                    'class_id'   => $courseClass->id,
                    'table_name' => 'class_members',
                    'row_id'     => $existingMember->id,
                    'new_values' => ['class_name' => $courseClass->name, 'join_key' => $courseClass->join_key],
                ]);

                session()->flash('status', 'Đã liên kết tài khoản của bạn với danh sách học viên trong lớp!');
                $this->reset(['class_code', 'confirmingClass']);
                $this->dispatch('class-joined');
                
                return $this->redirectRoute('student.classes.show', ['ma_user' => $userId, 'courseClass' => $courseClass->id], navigate: true);
            }

            session()->flash('status', 'Bạn đã là thành viên của lớp học này.');
            $this->reset(['class_code', 'confirmingClass']);
            $this->showModal = false;

            return $this->redirectRoute('student.classes.show', ['ma_user' => $userId, 'courseClass' => $courseClass->id], navigate: true);
        }

        if (!$courseClass->require_approval) {
            // Khóa nguyên tử theo lớp: đảm bảo kiểm-tra-giới-hạn và tạo thành viên diễn ra
            // tuần tự cho cùng một lớp. Nếu không có khóa, hai học viên bấm tham gia đồng thời
            // đều đọc được sĩ số chưa đầy rồi cùng tạo -> vượt giới hạn (vd đầy 50 vẫn lên 51).
            $lock = \Illuminate\Support\Facades\Cache::lock('class-join:' . $courseClass->id, 10);

            try {
                // Chờ tối đa 5s để giành khóa; nếu không được thì báo bận, không tạo.
                if (! $lock->block(5)) {
                    $this->dispatch('toast', message: 'Hệ thống đang bận xử lý yêu cầu tham gia, vui lòng thử lại.', type: 'error');
                    return;
                }

                // Chặn khi lớp đã đạt giới hạn số học viên của gói chủ lớp (đếm TRONG khóa).
                if ($courseClass->owner) {
                    $maxStudents = app(\App\Services\SubscriptionService::class)->maxStudentsPerClass($courseClass->owner);
                    $activeCount = ClassMember::where('class_id', $courseClass->id)
                        ->where('status', ClassMember::STATUS_ACTIVE)
                        ->count();

                    if ($maxStudents > 0 && $activeCount >= $maxStudents) {
                        $this->confirmingClass = null;
                        $this->addError('class_code', "Lớp đã đạt giới hạn {$maxStudents} học viên nên bạn không thể tham gia lúc này. Vui lòng liên hệ giảng viên.");
                        $this->dispatch('toast', message: 'Lớp đã đủ số lượng học viên, bạn không thể tham gia.', type: 'error');
                        return;
                    }
                }

                $member = ClassMember::create([
                    'class_id' => $courseClass->id,
                    'user_id' => $userId,
                    'status' => ClassMember::STATUS_ACTIVE,
                ]);
            } finally {
                $lock->release();
            }

            $member->syncProfile([
                'full_name' => $this->full_name,
                'email' => $userEmail,
            ]);

            Auth::user()->notify(new \App\Notifications\ClassJoinedNotification($courseClass));
            $this->notifyClassManagers($courseClass, new \App\Notifications\ClassMemberJoined($courseClass, Auth::user()));
            \App\Events\StudentJoinedClass::dispatch((string) $courseClass->id);

            app(AuditLogService::class)->log('class_joined', [
                'class_id'   => $courseClass->id,
                'table_name' => 'class_members',
                'row_id'     => $member->id,
                'new_values' => ['class_name' => $courseClass->name, 'join_key' => $courseClass->join_key],
            ]);

            session()->flash('status', 'Đã tham gia lớp học thành công!');
            $this->reset(['class_code', 'confirmingClass']);
            $this->dispatch('class-joined');

            return $this->redirectRoute('student.classes.show', ['ma_user' => $userId, 'courseClass' => $courseClass->id], navigate: true);
        }

        // Nhánh yêu cầu duyệt: vẫn kiểm tra giới hạn trước khi tạo yêu cầu (chặn xếp hàng vô ích khi đầy).
        if ($courseClass->owner) {
            $maxStudents = app(\App\Services\SubscriptionService::class)->maxStudentsPerClass($courseClass->owner);
            $activeCount = ClassMember::where('class_id', $courseClass->id)
                ->where('status', ClassMember::STATUS_ACTIVE)
                ->count();

            if ($maxStudents > 0 && $activeCount >= $maxStudents) {
                $this->confirmingClass = null;
                $this->addError('class_code', "Lớp đã đạt giới hạn {$maxStudents} học viên nên bạn không thể tham gia lúc này. Vui lòng liên hệ giảng viên.");
                $this->dispatch('toast', message: 'Lớp đã đủ số lượng học viên, bạn không thể tham gia.', type: 'error');
                return;
            }
        }

        // Nếu yêu cầu duyệt -> Bắt buộc phải qua bước duyệt
        $existingRequest = \App\Models\ClassJoinRequest::where('class_id', $courseClass->id)
            ->where('user_id', $userId)
            ->whereIn('status', [\App\Models\ClassJoinRequest::STATUS_PENDING, 'pending'])
            ->first();

        if ($existingRequest) {
            session()->flash('status', 'Bạn đã gửi yêu cầu tham gia lớp này rồi, vui lòng chờ giảng viên phê duyệt.');
            $this->reset(['class_code', 'confirmingClass']);
            return;
        }

        \App\Models\ClassJoinRequest::create([
            'class_id' => $courseClass->id,
            'user_id' => $userId,
            'status' => \App\Models\ClassJoinRequest::STATUS_PENDING,
        ]);

        $this->notifyClassManagers($courseClass, new \App\Notifications\ClassJoinRequestReceived($courseClass, Auth::user()));
        \App\Events\StudentJoinedClass::dispatch((string) $courseClass->id);

        session()->flash('status', 'Yêu cầu tham gia đã được gửi và đang chờ giảng viên xác nhận!');

        $this->reset(['class_code', 'confirmingClass']);
    }

    /**
     * Gửi thông báo tới những người quản lý lớp (chủ chính + đồng chủ đã nhận lời mời).
     */
    protected function notifyClassManagers(CourseClass $courseClass, \Illuminate\Notifications\Notification $notification): void
    {
        $managers = collect();

        if ($courseClass->owner) {
            $managers->push($courseClass->owner);
        }

        $courseClass->coOwners()
            ->wherePivotNotNull('accepted_at')
            ->get()
            ->each(fn ($coOwner) => $managers->push($coOwner));

        $managers
            ->filter(fn ($user) => $user && (string) $user->id !== (string) Auth::id())
            ->unique('id')
            ->each(fn ($user) => $user->notify($notification));
    }

    public function render(): View
    {
        return view('livewire.student.join-class');
    }
}
