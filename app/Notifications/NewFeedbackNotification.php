<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFeedbackNotification extends Notification
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
        $channels = ['database'];
        if (\App\Models\Setting::get('enable_email_notifications', '1') == '1' && $notifiable->email) {
            if (!in_array('mail', $channels)) $channels[] = 'mail';
        }
        if (\App\Models\Setting::get('enable_telegram_notifications', '0') == '1' && $notifiable->telegram_chat_id) {
            if (!in_array(\App\Channels\SafeTelegramChannel::class, $channels)) {
                $channels[] = \App\Channels\SafeTelegramChannel::class;
            }
        }
        return $channels;
    }

    public function toMail(object $notifiable)
    {
        $data = $this->toArray($notifiable);
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject($data['title'] ?? 'SAMS Notification')
            ->line($data['message'] ?? '')
            ->action('Xem chi tiết', $data['url'] ?? url('/'));
    }

    public function toTelegram(object $notifiable)
    {
        $data = $this->toArray($notifiable);
        $message = \NotificationChannels\Telegram\TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("*" . ($data['title'] ?? 'SAMS') . "*\n\n" . ($data['message'] ?? ''));
            
        if (isset($data['url'])) {
            $message->button('Xem chi tiết', $data['url']);
        }
        
        return $message;
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
