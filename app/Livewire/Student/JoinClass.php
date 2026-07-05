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

    #[\Livewire\Attributes\On('open-join-class-modal')]
    public function openModal()
    {
        $this->full_name = Auth::user()->name;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['class_code']);
        $this->resetValidation();
    }

    public function submit()
    {
        $this->validate([
            'class_code' => 'required|string',
            'full_name' => 'required|string|max:255',
        ], [
            'class_code.required' => 'Vui lòng nhập mã lớp.',
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
        ]);

        $courseClass = CourseClass::where('join_key', $this->class_code)->first();

        if (! $courseClass) {
            $this->addError('class_code', 'Không tìm thấy lớp học với mã này.');

            return;
        }

        $userId = Auth::id();
        $userEmail = Auth::user()->email;

        $existingMember = ClassMember::with('profile')
            ->where('class_id', $courseClass->id)
            ->where(function ($query) use ($userId, $userEmail) {
                $query->where('user_id', $userId)
                    ->orWhereHas('profile', fn ($profile) => $profile->where('email', $userEmail));
            })->first();

        if ($existingMember) {
            // Nếu học viên đã có trong danh sách (được import) nhưng chưa liên kết user_id
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
                $this->reset(['class_code']);
                $this->dispatch('class-joined');
                return;
            }

            $this->addError('class_code', 'Bạn đã là thành viên của lớp học này.');
            return;
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
            $this->reset(['class_code']);
            $this->dispatch('class-joined');
            return;
        }

        // Nếu yêu cầu duyệt -> Bắt buộc phải qua bước duyệt
        $existingRequest = \App\Models\ClassJoinRequest::where('class_id', $courseClass->id)
            ->where('user_id', $userId)
            ->whereIn('status', [\App\Models\ClassJoinRequest::STATUS_PENDING, 'pending'])
            ->first();

        if ($existingRequest) {
            session()->flash('status', 'Bạn đã gửi yêu cầu tham gia lớp này rồi, vui lòng chờ giảng viên phê duyệt.');
            $this->reset(['class_code']);
            return;
        }

        \App\Models\ClassJoinRequest::create([
            'class_id' => $courseClass->id,
            'user_id' => $userId,
            'status' => \App\Models\ClassJoinRequest::STATUS_PENDING,
        ]);

        session()->flash('status', 'Yêu cầu tham gia đã được gửi và đang chờ giảng viên xác nhận!');

        $this->reset(['class_code']);
    }

    public function render(): View
    {
        return view('livewire.student.join-class');
    }
}
