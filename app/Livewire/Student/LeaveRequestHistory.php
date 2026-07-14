<?php

namespace App\Livewire\Student;

use App\Models\LeaveRequest;
use Livewire\Component;
use Livewire\WithPagination;

class LeaveRequestHistory extends Component
{
    use WithPagination;

    public int $perPage = 20;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $requests = LeaveRequest::with(['classMeeting.courseClass', 'reviewer'])
            ->whereHas('classMember', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.student.leave-request-history', [
            'requests' => $requests,
        ])->layout('layouts.user', ['title' => 'Lịch sử đơn xin nghỉ phép']);
    }

    public $requestToDelete = null;

    public function confirmDelete($id)
    {
        $this->requestToDelete = $id;
        $this->dispatch('open-modal', 'confirm-request-deletion');
    }

    public function deleteRequest()
    {
        if (!$this->requestToDelete) return;

        $request = LeaveRequest::whereHas('classMember', function ($query) {
            $query->where('user_id', auth()->id());
        })->where('status', 'pending')->findOrFail($this->requestToDelete);

        $request->delete(); // Xóa mềm

        $this->requestToDelete = null;
        $this->dispatch('close-modal', 'confirm-request-deletion');
        session()->flash('success', 'Xóa đơn xin nghỉ phép thành công!');
    }
}
