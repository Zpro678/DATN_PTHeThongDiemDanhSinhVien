<?php

namespace App\Livewire\Student;

use App\Livewire\Concerns\DeniesAccess;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LeaveRequestShow extends Component
{
    use DeniesAccess;

    public LeaveRequest $leaveRequest;

    public function mount(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->classMember->user_id !== auth()->id()) {
            $this->denyAccess('student.leave-requests.history', 'Bạn không có quyền xem đơn xin nghỉ này.');
            return;
        }

        $this->leaveRequest = $leaveRequest->load(['classMember.courseClass', 'classMeeting', 'reviewer']);
    }

    public function render(): View
    {
        return view('livewire.student.leave-request-show')->layout('layouts.user', ['title' => 'Chi tiết đơn xin nghỉ']);
    }
}
