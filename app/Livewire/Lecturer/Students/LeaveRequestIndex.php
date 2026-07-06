<?php

namespace App\Livewire\Lecturer\Students;

use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveRequestReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class LeaveRequestIndex extends Component
{
    use WithPagination;

    public string $status = 'pending';

    public string $search = '';

    public string $classFilter = 'all';

    public int $perPage = 10;

    public ?int $rejectingRequestId = null;
    public ?int $approvingRequestId = null;

    public string $rejectedReason = '';

    public function mount(string $status = 'pending'): void
    {
        abort_unless(in_array($status, ['pending', 'approved', 'rejected'], true), 404);
        $this->status = $status;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedClassFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function openApprove(int $requestId): void
    {
        $this->ownedRequest($requestId);
        $this->approvingRequestId = $requestId;
    }

    public function closeApprove(): void
    {
        $this->reset('approvingRequestId');
    }

    public function confirmApprove(LeaveRequestReviewService $reviewService): void
    {
        abort_unless($this->approvingRequestId, 403);
        $leaveRequest = $this->ownedRequest($this->approvingRequestId);
        $reviewService->approve($leaveRequest, $this->reviewer());

        $this->closeApprove();
        $this->dispatch('toast', message: 'Bạn đã duyệt đơn xin nghỉ phép của sinh viên ' . $leaveRequest->classMember->student_code . ' thành công.', type: 'success');
    }

    public function openReject(int $requestId): void
    {
        $this->ownedRequest($requestId);
        $this->rejectingRequestId = $requestId;
        $this->rejectedReason = '';
        $this->resetValidation();
    }

    public function closeReject(): void
    {
        $this->reset(['rejectingRequestId', 'rejectedReason']);
        $this->resetValidation();
    }

    public function reject(LeaveRequestReviewService $reviewService): void
    {
        $validated = $this->validate([
            'rejectingRequestId' => ['required', 'integer'],
            'rejectedReason' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'rejectedReason.required' => 'Vui lòng nhập lý do từ chối.',
            'rejectedReason.min' => 'Lý do từ chối phải có ít nhất 5 ký tự.',
        ]);

        $leaveRequest = $this->ownedRequest($validated['rejectingRequestId']);
        $reviewService->reject($leaveRequest, $this->reviewer(), $validated['rejectedReason']);

        $this->closeReject();
        $this->dispatch('toast', message: 'Bạn đã từ chối đơn xin nghỉ phép của sinh viên ' . $leaveRequest->classMember->student_code . ' thành công.', type: 'success');
    }

    private function reviewer(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    private function ownedRequest(int $requestId): LeaveRequest
    {
        return LeaveRequest::query()
            ->with('classMember')
            ->whereHas('classMember.courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($requestId);
    }

    public function render(): View
    {
        $classes = CourseClass::query()
            ->where('owner_user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'join_key', 'class_code']);

        $leaveRequests = LeaveRequest::query()
            ->with([
                'classMember:id,class_id,user_id',
                'classMember.profile:id,class_member_id,student_code,full_name,email',
                'classMember.courseClass:id,name,join_key,owner_user_id',
                'classMember.user:id,name,email,avatar',
                'classMeeting:id,class_id,name,date,start_time,end_time',
                'reviewer:id,name',
            ])
            ->whereHas('classMember.courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->where('status', $this->status)
            ->when($this->classFilter !== 'all', fn (Builder $query) => $query->whereHas('classMember', fn (Builder $query) => $query->where('class_id', $this->classFilter)))
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('reason', 'like', '%'.$this->search.'%')
                        ->orWhereHas('classMember.profile', fn (Builder $p) => $p->where('full_name', 'like', '%'.$this->search.'%')->orWhere('student_code', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('classMember.user', fn (Builder $u) => $u->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.lecturer.students.leave-requests.index', compact('classes', 'leaveRequests'))
            ->layout('layouts.user', ['title' => 'Đơn xin nghỉ']);
    }
}
