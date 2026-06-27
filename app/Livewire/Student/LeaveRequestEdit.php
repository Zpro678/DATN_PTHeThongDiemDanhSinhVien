<?php

namespace App\Livewire\Student;

use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class LeaveRequestEdit extends Component
{
    use WithFileUploads;

    public LeaveRequest $leaveRequest;

    public $class_id = '';

    public $class_session_id = '';

    public $reason = '';

    public $proof_images = [];

    public $existing_images = [];

    public function mount(LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->status === 'pending', 403, 'Chỉ có thể sửa đơn đang chờ duyệt');
        abort_unless($leaveRequest->classMember->user_id === auth()->id(), 403);

        $this->leaveRequest = $leaveRequest;
        $this->class_id = $leaveRequest->classMember->class_id;
        $this->class_session_id = $leaveRequest->class_session_id;
        $this->reason = $leaveRequest->reason;
        $this->existing_images = $leaveRequest->proof_image ?? [];
    }

    #[Computed]
    public function classes()
    {
        return ClassMember::with('courseClass')
            ->whereHas('courseClass')
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->get()
            ->pluck('courseClass');
    }

    #[Computed]
    public function sessions()
    {
        if (! $this->class_id) {
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
        ], [
            'class_id.required' => 'Vui lòng chọn lớp học.',
            'class_id.exists' => 'Lớp học không tồn tại.',
            'class_session_id.required' => 'Vui lòng chọn buổi học.',
            'class_session_id.exists' => 'Buổi học không tồn tại.',
            'reason.required' => 'Vui lòng nhập lý do xin nghỉ.',
            'reason.min' => 'Lý do xin nghỉ quá ngắn (tối thiểu 10 ký tự).',
            'reason.max' => 'Lý do xin nghỉ quá dài (tối đa 1000 ký tự).',
            'proof_images.*.image' => 'Tệp đính kèm phải là hình ảnh.',
            'proof_images.*.max' => 'Hình ảnh không được vượt quá 2MB.',
        ]);

        $member = ClassMember::where('class_id', $this->class_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Check if changed session and already requested
        if ($this->class_session_id != $this->leaveRequest->class_session_id) {
            $existing = LeaveRequest::where('class_member_id', $member->id)
                ->where('class_session_id', $this->class_session_id)
                ->first();

            if ($existing) {
                session()->flash('error', 'Bạn đã có đơn xin phép cho buổi học này rồi.');
                return;
            }
        }

        $proofPaths = $this->existing_images;
        if (! empty($this->proof_images)) {
            foreach ($this->proof_images as $image) {
                $proofPaths[] = $image->store('leave_proofs', 'public');
            }
        }

        $this->leaveRequest->update([
            'class_member_id' => $member->id,
            'class_session_id' => $this->class_session_id,
            'reason' => $this->reason,
            'proof_image' => $proofPaths,
        ]);

        session()->flash('success', 'Đã lưu thay đổi thành công.');

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

    public function removeExistingImage($index)
    {
        if (isset($this->existing_images[$index])) {
            unset($this->existing_images[$index]);
            $this->existing_images = array_values($this->existing_images);
        }
    }

    public function render(): View
    {
        return view('livewire.student.leave-request-create', [
            'isEdit' => true,
        ])->layout('layouts.user', ['title' => 'Chỉnh sửa đơn xin phép']);
    }
}
