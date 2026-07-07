<?php

namespace App\Livewire\Admin;

use App\Models\SystemFeedback;
use App\Notifications\FeedbackRepliedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class FeedbackIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';
    
    public $showReplyModal = false;
    public $selectedFeedbackId = null;

    #[Rule('required|min:10|max:1000')]
    public $replyContent = '';

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function openReplyModal($feedbackId)
    {
        $this->selectedFeedbackId = $feedbackId;
        $this->replyContent = '';
        $this->resetValidation();
        $this->showReplyModal = true;
    }

    public function closeReplyModal()
    {
        $this->showReplyModal = false;
        $this->selectedFeedbackId = null;
    }

    public function submitReply()
    {
        $this->validate();

        $feedback = SystemFeedback::findOrFail($this->selectedFeedbackId);
        $feedback->update([
            'status' => 'resolved',
            'admin_reply' => $this->replyContent,
            'replied_by' => Auth::id(),
            'replied_at' => now(),
        ]);

        $feedback->user->notify(new FeedbackRepliedNotification($feedback));

        $this->closeReplyModal();
        $this->dispatch('notify', message: 'Đã trả lời phản hồi thành công!', type: 'success');
    }

    public function markAsInProgress($feedbackId)
    {
        $feedback = SystemFeedback::findOrFail($feedbackId);
        $feedback->update(['status' => 'in_progress']);
        $this->dispatch('notify', message: 'Đã đánh dấu đang xử lý.', type: 'info');
    }

    public function render()
    {
        $query = SystemFeedback::with(['user', 'replier'])->orderBy('created_at', 'desc');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.admin.feedback-index', [
            'feedbacks' => $query->paginate(15),
        ]);
    }
}
