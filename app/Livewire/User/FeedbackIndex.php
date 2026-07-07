<?php

namespace App\Livewire\User;

use App\Models\SystemFeedback;
use App\Models\User;
use App\Notifications\NewFeedbackNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class FeedbackIndex extends Component
{
    use WithFileUploads;

    public $showCreateModal = false;

    #[Rule('required|min:5|max:100')]
    public $title = '';

    #[Rule('required|in:bug,feature,other')]
    public $type = 'bug';

    #[Rule('required|min:10|max:1000')]
    public $content = '';

    #[Rule('nullable|image|max:2048')]
    public $attachment;

    public function openCreateModal()
    {
        $this->reset(['title', 'type', 'content', 'attachment']);
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function submit()
    {
        $this->validate();

        $path = null;
        if ($this->attachment) {
            $path = $this->attachment->store('feedbacks', 'public');
        }

        $feedback = SystemFeedback::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'type' => $this->type,
            'content' => $this->content,
            'attachment_path' => $path,
            'status' => 'pending',
        ]);

        // Send notification to all admins
        $admins = User::whereIn('role', ['ADMIN', 'SUPER_ADMIN'])->get();
        Notification::send($admins, new NewFeedbackNotification($feedback));

        $this->closeCreateModal();
        $this->dispatch('notify', message: 'Gửi phản hồi thành công!', type: 'success');
    }

    public function render()
    {
        $feedbacks = SystemFeedback::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.user.feedback-index', [
            'feedbacks' => $feedbacks,
        ]);
    }
}
