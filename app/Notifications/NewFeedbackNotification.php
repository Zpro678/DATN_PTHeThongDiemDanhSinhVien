<?php

namespace App\Notifications;

use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFeedbackNotification extends Notification
{
    use Queueable, ChecksNotificationPreferences;

    /** @var array<int, string> */
    public array $supportedChannels = ['database', 'mail'];

    /**
     * Create a new notification instance.
     */
    public function __construct(public \App\Models\SystemFeedback $feedback)
    {
        //
    }

    public function toArray(object $notifiable): array
    {
        $sender = $this->feedback->user->name ?? 'Người dùng';
        
        return [
            'title'   => 'Phản hồi hệ thống mới',
            'message' => "{$sender} vừa gửi một phản hồi: {$this->feedback->title}",
            'url'     => route('admin.feedbacks', ['ma_user' => $notifiable->id], false),
            'icon'    => 'message-square',
            'type'    => 'new_feedback',
        ];
    }
}
