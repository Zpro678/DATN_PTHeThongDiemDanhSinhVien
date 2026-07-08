<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackRepliedNotification extends Notification implements ShouldQueue
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Phản hồi của bạn đã được trả lời')
                    ->greeting('Chào ' . ($notifiable->name ?? 'bạn') . ',')
                    ->line('Quản trị viên đã trả lời phản hồi "' . $this->feedback->title . '" của bạn.')
                    ->line('Nội dung trả lời từ Admin:')
                    ->line('"' . $this->feedback->admin_reply . '"')
                    ->action('Xem chi tiết', route('support') . '#feedback-' . $this->feedback->id)
                    ->line('Cảm ơn bạn đã đóng góp ý kiến để giúp hệ thống tốt hơn!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Phản hồi đã được trả lời',
            'message' => "Admin đã trả lời phản hồi '{$this->feedback->title}' của bạn.",
            'url'     => route('support', [], false) . '#feedback-' . $this->feedback->id,
            'icon'    => 'check-circle',
            'type'    => 'feedback_replied',
        ];
    }
}
