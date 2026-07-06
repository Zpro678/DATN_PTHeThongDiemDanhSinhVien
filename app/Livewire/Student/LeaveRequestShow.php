<?php

namespace App\Livewire\Student;

use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LeaveRequestShow extends Component
{
    public LeaveRequest $leaveRequest;

    public function mount(LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->classMember->user_id === auth()->id(), 403);
        $this->leaveRequest = $leaveRequest->load(['classMember.courseClass', 'classMeeting', 'reviewer']);
    }

    public function render(): View
    {
        return view('livewire.student.leave-request-show')->layout('layouts.user', ['title' => 'Chi tiết đơn xin nghỉ']);
    }
}
