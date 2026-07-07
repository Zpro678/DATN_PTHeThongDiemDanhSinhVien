<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackRepliedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public \App\Models\SystemFeedback $feedback)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Phản hồi đã được trả lời',
            'message' => "Admin đã trả lời phản hồi '{$this->feedback->title}' của bạn.",
            'url'     => route('user.feedbacks'),
            'icon'    => 'check-circle',
            'type'    => 'feedback_replied',
        ];
    }
}
