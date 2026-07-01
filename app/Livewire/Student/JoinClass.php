<?php

namespace App\Livewire\Student;

use App\Models\ClassMember;
use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JoinClass extends Component
{
    // Mã lớp học mà sinh viên muốn tham gia
    public $class_code = '';

    // Mã số sinh viên của người dùng
    public $student_code = '';

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
        $this->reset(['class_code', 'student_code']);
        $this->resetValidation();
    }

    public function submit()
    {
        $this->validate([
            'class_code' => 'required|string',
            'student_code' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
        ], [
            'class_code.required' => 'Vui lòng nhập mã lớp.',
            'student_code.required' => 'Vui lòng nhập mã học viên.',
            'student_code.max' => 'Mã học viên không được vượt quá 50 ký tự.',
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
        ]);

        $courseClass = CourseClass::where('join_key', $this->class_code)->first();

        if (! $courseClass) {
            $this->addError('class_code', 'Không tìm thấy lớp học với mã này.');

            return;
        }

        $userId = Auth::id();

        $existingMember = ClassMember::with('profile')
            ->where('class_id', $courseClass->id)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('profile', fn ($profile) => $profile->where('student_code', $this->student_code));
            })->first();

        if ($existingMember) {
            // Nếu học viên đã có trong danh sách (được import) nhưng chưa liên kết user_id
            if (is_null($existingMember->user_id) && $existingMember->student_code === $this->student_code) {
                $existingMember->update([
                    'user_id' => $userId,
                    'status' => ClassMember::STATUS_ACTIVE,
                    'status_changed_at' => null,
                ]);
                $existingMember->syncProfile([
                    'student_code' => $this->student_code,
                    'full_name' => $this->full_name,
                    'email' => Auth::user()?->email,
                ]);
                session()->flash('status', 'Đã liên kết tài khoản của bạn với danh sách học viên trong lớp!');
                $this->reset(['class_code', 'student_code']);
                $this->dispatch('class-joined');
                return;
            }

            $this->addError('class_code', 'Bạn đã là thành viên của lớp học này (hoặc mã sinh viên đã được sử dụng).');
            return;
        }

        if ($courseClass->require_approval) {
            $existingRequest = \App\Models\ClassJoinRequest::where('class_id', $courseClass->id)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                session()->flash('status', 'Bạn đã gửi yêu cầu tham gia lớp này rồi, vui lòng chờ giảng viên phê duyệt.');
                $this->reset(['class_code', 'student_code']);
                return;
            }

            \App\Models\ClassJoinRequest::create([
                'class_id' => $courseClass->id,
                'user_id' => $userId,
                'student_code' => $this->student_code,
                'full_name' => $this->full_name,
                'status' => 'pending',
            ]);

            session()->flash('status', 'Yêu cầu tham gia lớp của bạn đã được gửi và đang chờ giảng viên phê duyệt!');
        } else {
            // Thêm sinh viên vào lớp ngay lập tức
            $member = ClassMember::create([
                'class_id' => $courseClass->id,
                'user_id' => $userId,
                'status' => ClassMember::STATUS_ACTIVE,
            ]);

            $member->syncProfile([
                'student_code' => $this->student_code,
                'full_name' => $this->full_name,
                'email' => Auth::user()?->email,
            ]);

            session()->flash('status', 'Bạn đã tham gia lớp học thành công!');
            $this->dispatch('class-joined');
        }

        $this->reset(['class_code', 'student_code']);
    }

    public function render(): View
    {
        return view('livewire.student.join-class');
    }
}
