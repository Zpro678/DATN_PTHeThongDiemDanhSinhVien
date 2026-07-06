<?php

namespace App\Livewire\Student;

use App\Models\ClassMember;
use App\Models\ClassMeeting;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class LeaveRequestCreate extends Component
{
    use WithFileUploads;

    #[Url]
    public $class_id = '';

    #[Url]
    public $class_meeting_id = '';

    public $reason = '';

    public $proof_images = [];

    public $existing_images = [];

    #[Computed]
    public function classes()
    {
        return CourseClass::whereHas('members', function ($query) {
            $query->where('user_id', auth()->id())
                  ->where('status', ClassMember::STATUS_ACTIVE);
        })
        ->withMax('sessions', 'created_at')
        ->orderByDesc('sessions_max_created_at')
        ->orderByDesc('created_at')
        ->get();
    }

    #[Computed]
    public function meetings()
    {
        if (! $this->class_id) {
            return [];
        }

        return ClassMeeting::where('class_id', $this->class_id)
            ->where('date', '>=', now()->subDays(14)->toDateString())
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();
    }

    public function submit()
    {
        $this->validate([
            'class_id' => 'required|exists:classes,id',
            'class_meeting_id' => 'required|exists:class_meetings,id',
            'reason' => 'required|string|min:10|max:1000',
            'proof_images.*' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB Max
        ], [
            'class_id.required' => 'Vui lòng chọn lớp học.',
            'class_id.exists' => 'Lớp học không tồn tại.',
            'class_meeting_id.required' => 'Vui lòng chọn buổi học.',
            'class_meeting_id.exists' => 'Buổi học không tồn tại.',
            'reason.required' => 'Vui lòng nhập lý do xin nghỉ.',
            'reason.min' => 'Lý do xin nghỉ quá ngắn (tối thiểu 10 ký tự).',
            'reason.max' => 'Lý do xin nghỉ quá dài (tối đa 1000 ký tự).',
            'proof_images.*.mimes' => 'Tệp đính kèm phải là hình ảnh (JPG, PNG) hoặc PDF.',
            'proof_images.*.max' => 'Tệp đính kèm không được vượt quá 5MB.',
        ]);

        $member = ClassMember::where('class_id', $this->class_id)
            ->where('user_id', auth()->id())
            ->where('status', ClassMember::STATUS_ACTIVE)
            ->firstOrFail();

        // Check if already requested
        $existing = LeaveRequest::where('class_member_id', $member->id)
            ->where('class_meeting_id', $this->class_meeting_id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            session()->flash('error', 'Bạn đã gửi đơn xin nghỉ thất bại. Bạn đã có đơn xin phép cho buổi học này rồi.');

            return redirect()->route('student.leave-requests.history');
        }

        $proofPaths = [];
        if (! empty($this->proof_images)) {
            foreach ($this->proof_images as $image) {
                $proofPaths[] = $image->storeAs('leave_proofs', $image->getClientOriginalName(), 'local');
            }
        }

        $leaveRequest = LeaveRequest::create([
            'class_member_id' => $member->id,
            'class_meeting_id' => $this->class_meeting_id,
            'reason' => $this->reason,
            'proof_image' => $proofPaths,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $owner = $member->courseClass->owner;
        if ($owner) {
            $owner->notify(new \App\Notifications\LeaveRequestSubmitted($leaveRequest));
        }

        session()->flash('success', 'Bạn đã gửi đơn xin nghỉ thành công.');

        app(AuditLogService::class)->log('leave_request_submitted', [
            'class_id'   => $this->class_id,
            'table_name' => 'leave_requests',
            'row_id'     => $leaveRequest->id,
            'new_values' => [
                'class_meeting_id' => $this->class_meeting_id,
                'reason'           => $this->reason,
            ],
        ]);

        return redirect()->route('student.leave-requests.history');
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
        return view('livewire.student.leave-request-create', [
            'isEdit' => false,
        ])->layout('layouts.user', ['title' => 'Gửi đơn xin phép']);
    }
}
