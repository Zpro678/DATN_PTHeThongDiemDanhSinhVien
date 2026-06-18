<?php

namespace App\Livewire\Lecturer\Students;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveRequestReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class LeaveRequestShow extends Component
{
    public int $leaveRequestId;

    public bool $showRejectForm = false;

    public string $rejectedReason = '';

    public function mount(int $leaveRequest): void
    {
        $this->leaveRequestId = $this->ownedRequest($leaveRequest)->id;
    }

    public function approve(LeaveRequestReviewService $reviewService): void
    {
        $reviewService->approve($this->ownedRequest($this->leaveRequestId), $this->reviewer());
        session()->flash('status', 'Đơn xin nghỉ đã được duyệt.');
    }

    public function reject(LeaveRequestReviewService $reviewService): void
    {
        $validated = $this->validate([
            'rejectedReason' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'rejectedReason.required' => 'Vui lòng nhập lý do từ chối.',
            'rejectedReason.min' => 'Lý do từ chối phải có ít nhất 5 ký tự.',
        ]);

        $reviewService->reject($this->ownedRequest($this->leaveRequestId), $this->reviewer(), $validated['rejectedReason']);
        $this->showRejectForm = false;
        session()->flash('status', 'Đơn xin nghỉ đã bị từ chối.');
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

        $recentRecords = $leaveRequest->classMember->attendanceRecords()
            ->with('classSession:id,name,date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('livewire.lecturer.students.leave-requests.show', compact('leaveRequest', 'recentRecords'))
            ->layout('layouts.user', ['title' => 'Chi tiết đơn xin nghỉ']);
    }
}
