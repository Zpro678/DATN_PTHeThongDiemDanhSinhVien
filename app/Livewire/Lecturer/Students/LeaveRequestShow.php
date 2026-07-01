<?php

namespace App\Livewire\Lecturer\Students;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveRequestReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class LeaveRequestShow extends Component
{
    use WithPagination;
    public int $leaveRequestId;

    public bool $showRejectForm = false;
    public bool $showApproveForm = false;

    public string $rejectedReason = '';

    public function mount(int $leaveRequest): void
    {
        $this->leaveRequestId = $this->ownedRequest($leaveRequest)->id;
    }

    public function confirmApprove(LeaveRequestReviewService $reviewService): void
    {
        $leaveRequest = $this->ownedRequest($this->leaveRequestId);
        $reviewService->approve($leaveRequest, $this->reviewer());
        $this->showApproveForm = false;
        $this->dispatch('toast', message: 'Bạn đã duyệt đơn xin nghỉ phép của sinh viên ' . $leaveRequest->classMember->student_code . ' thành công.', type: 'success');
    }

    public function reject(LeaveRequestReviewService $reviewService): void
    {
        $validated = $this->validate([
            'rejectedReason' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'rejectedReason.required' => 'Vui lòng nhập lý do từ chối.',
            'rejectedReason.min' => 'Lý do từ chối phải có ít nhất 5 ký tự.',
        ]);

        $leaveRequest = $this->ownedRequest($this->leaveRequestId);
        $reviewService->reject($leaveRequest, $this->reviewer(), $validated['rejectedReason']);
        $this->showRejectForm = false;
        $this->dispatch('toast', message: 'Bạn đã từ chối đơn xin nghỉ phép của sinh viên ' . $leaveRequest->classMember->student_code . ' thành công.', type: 'success');
    }

    private function ownedRequest(int $requestId): LeaveRequest
    {
        return LeaveRequest::query()
            ->whereHas('classMember.courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($requestId);
    }

    private function reviewer(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    public function render(): View
    {
        $leaveRequest = $this->ownedRequest($this->leaveRequestId)->load([
            'classMember.courseClass',
            'classMember.user',
            'classSession',
            'reviewer',
        ]);

        $approvedLeaveRequests = $leaveRequest->classMember->leaveRequests()
            ->with('classSession:id,name,date')
            ->where('status', 'approved')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.lecturer.students.leave-requests.show', compact('leaveRequest', 'approvedLeaveRequests'))
            ->layout('layouts.user', ['title' => 'Chi tiết đơn xin nghỉ']);
    }
}
