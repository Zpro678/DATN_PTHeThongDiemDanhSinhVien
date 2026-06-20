<?php

namespace App\Livewire\Student;

use App\Models\ClassMember;
use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JoinClass extends Component
{
    public $class_code = '';
    public $student_code = '';
    public $full_name = '';

    public function mount()
    {
        $this->full_name = Auth::user()->name;
    }

    public function submit()
    {
        $this->validate([
            'class_code' => 'required|string',
            'student_code' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
        ]);

        $courseClass = CourseClass::where('code', $this->class_code)->first();

        if (!$courseClass) {
            $this->addError('class_code', 'Không tìm thấy lớp học với mã này.');
            return;
        }

        $userId = Auth::id();

        // Check if already a member
        $isMember = ClassMember::where('class_id', $courseClass->id)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('student_code', $this->student_code);
            })->exists();

        if ($isMember) {
            $this->addError('class_code', 'Bạn đã là thành viên của lớp học này (hoặc mã sinh viên đã được sử dụng).');
            return;
        }

        // Luôn thêm sinh viên vào lớp ngay lập tức (không cần chờ duyệt)
        ClassMember::create([
            'class_id'     => $courseClass->id,
            'user_id'      => $userId,
            'student_code' => $this->student_code,
            'full_name'    => $this->full_name,
            'status'       => 'active',
        ]);

        session()->flash('status', 'Bạn đã tham gia lớp học thành công!');
        $this->reset(['class_code', 'student_code']);
    }

    public function render(): View
    {
        return view('livewire.student.join-class')->layout('layouts.user', ['title' => 'Tham gia lớp']);
    }
}
