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

#[Layout('components.admin-layout')]
class FeedbackIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';
    
    public $showReplyModal = false;
    public $selectedFeedbackId = null;
    
    public $showDetailModal = false;
    public $detailFeedback = null;
    
    public $showDeleteModal = false;
    public $feedbackToDeleteId = null;

    #[Rule('required|min:10|max:1000')]
    public $replyContent = '';

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function viewDetails($id)
    {
        $this->detailFeedback = SystemFeedback::with(['user', 'replier'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->detailFeedback = null;
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
        $this->dispatch('notify', message: 'Cập nhật trạng thái thành công', type: 'success');
    }

    public function confirmDelete($id)
    {
        $this->feedbackToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function executeDelete()
    {
        $feedback = SystemFeedback::findOrFail($this->feedbackToDeleteId);
        $feedback->delete();
        
        $this->closeDeleteModal();
        $this->dispatch('notify', message: 'Đã xóa phản hồi', type: 'success');
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->feedbackToDeleteId = null;
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
