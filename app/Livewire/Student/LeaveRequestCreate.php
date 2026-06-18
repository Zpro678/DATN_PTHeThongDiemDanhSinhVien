<?php

namespace App\Livewire\Student;

use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class LeaveRequestCreate extends Component
{
    use WithFileUploads;

    public $class_id = '';
    public $class_session_id = '';
    public $reason = '';
    public $proof_images = [];

    public function getClassesProperty()
    {
        return ClassMember::with('courseClass')
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->get()
            ->pluck('courseClass');
    }

    public function getSessionsProperty()
    {
        if (!$this->class_id) {
            return [];
        }

        return ClassSession::where('class_id', $this->class_id)
            ->orderBy('date')
            ->get();
    }

    public function submit()
    {
        $this->validate([
            'class_id' => 'required|exists:classes,id',
            'class_session_id' => 'required|exists:class_sessions,id',
            'reason' => 'required|string|min:10|max:1000',
            'proof_images.*' => 'nullable|image|max:2048', // 2MB Max
        ]);

        $member = ClassMember::where('class_id', $this->class_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Check if already requested
        $existing = LeaveRequest::where('class_member_id', $member->id)
            ->where('class_session_id', $this->class_session_id)
            ->first();

        if ($existing) {
            $this->addError('class_session_id', 'Bạn đã gửi đơn xin phép cho buổi học này rồi.');
            return;
        }

        $proofPaths = [];
        if (!empty($this->proof_images)) {
            foreach ($this->proof_images as $image) {
                $proofPaths[] = $image->store('leave_proofs', 'public');
            }
        }

        LeaveRequest::create([
            'class_member_id' => $member->id,
            'class_session_id' => $this->class_session_id,
            'reason' => $this->reason,
            'proof_image' => $proofPaths,
            'status' => 'pending',
        ]);

        session()->flash('status', 'Đơn xin nghỉ phép đã được gửi thành công. Vui lòng chờ giảng viên duyệt.');

        $this->reset(['class_id', 'class_session_id', 'reason', 'proof_images']);
    }

    public function removeImage($index)
    {
        if (isset($this->proof_images[$index])) {
            unset($this->proof_images[$index]);
            // Re-index array
            $this->proof_images = array_values($this->proof_images);
        }
    }

    public function render(): View
    {
        return view('livewire.student.leave-request-create')->layout('layouts.user', ['title' => 'Gửi đơn xin phép']);
    }
}
