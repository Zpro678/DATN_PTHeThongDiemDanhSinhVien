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

        if ($courseClass->teacher_id === $userId) {
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
            $member = ClassMember::create([
                'class_id' => $courseClass->id,
                'user_id' => $userId,
                'status' => ClassMember::STATUS_ACTIVE,
            ]);
            
            $member->syncProfile([
                'full_name' => $this->full_name,
                'email' => $userEmail,
            ]);

            Auth::user()->notify(new \App\Notifications\ClassJoinedNotification($courseClass));

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

        session()->flash('status', 'Yêu cầu tham gia đã được gửi và đang chờ giảng viên xác nhận!');

        $this->reset(['class_code', 'confirmingClass']);
    }

    public function render(): View
    {
        return view('livewire.student.join-class');
    }
}
