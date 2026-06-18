<?php

namespace App\Livewire\Student;

use App\Models\LeaveRequest;
use Livewire\Component;

class LeaveRequestHistory extends Component
{
    public function render()
    {
        $requests = LeaveRequest::with(['classSession.courseClass', 'reviewer'])
            ->whereHas('classMember', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.student.leave-request-history', [
            'requests' => $requests,
        ])->layout('layouts.user', ['title' => 'Lịch sử đơn xin nghỉ phép']);
    }
}
