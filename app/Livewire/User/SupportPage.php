<?php

namespace App\Livewire\User;

use App\Models\SystemFeedback;
use App\Models\User;
use App\Notifications\NewFeedbackNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class SupportPage extends Component
{
    use WithFileUploads, WithPagination;

    public $showDetailModal = false;
    public $detailFeedback = null;

    #[Rule('required|min:5|max:100')]
    public $title = '';

    #[Rule('required|in:bug,feature,other')]
    public $type = 'bug';

    #[Rule('required|min:10|max:1000')]
    public $content = '';

    #[Rule([
        'attachments' => 'nullable|array|max:3',
        'attachments.*' => 'image|max:2048',
    ])]
    public $attachments = [];

    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }

    public function submit()
    {
        $this->validate();

        $paths = [];
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $paths[] = $attachment->store('feedbacks', 'public');
            }
        }

        $feedback = SystemFeedback::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'type' => $this->type,
            'content' => $this->content,
            'attachment_path' => !empty($paths) ? $paths : null,
            'status' => 'pending',
        ]);

        // Send notification to all admins
        $admins = User::whereIn('role', ['ADMIN', 'SUPER_ADMIN'])->get();
        Notification::send($admins, new NewFeedbackNotification($feedback));

        $this->reset(['title', 'type', 'content', 'attachments']);
        $this->resetPage();
        
        $this->dispatch('toast', message: 'Gửi yêu cầu hỗ trợ thành công!', type: 'success');
    }

    public $showCancelModal = false;
    public $feedbackToCancelId = null;

    public function viewDetails($id)
    {
        $this->detailFeedback = SystemFeedback::where('user_id', Auth::id())->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->detailFeedback = null;
    }

    public function confirmCancel($id)
    {
        $this->feedbackToCancelId = $id;
        $this->showCancelModal = true;
    }

    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->feedbackToCancelId = null;
    }

    public function executeCancel()
    {
        $feedback = SystemFeedback::where('id', $this->feedbackToCancelId)->where('user_id', Auth::id())->first();

        if ($feedback && $feedback->status === 'pending') {
            $feedback->update(['status' => 'cancelled']);
            $this->dispatch('toast', message: 'Đã hủy yêu cầu hỗ trợ.', type: 'success');
        } else {
            $this->dispatch('toast', message: 'Không thể hủy yêu cầu này.', type: 'error');
        }

        $this->closeCancelModal();
    }

    public function render()
    {
        $feedbacks = SystemFeedback::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('livewire.user.support-page', [
            'feedbacks' => $feedbacks,
        ])->layout('layouts.user', ['title' => 'Góp ý & Phản hồi']);
    }
}
