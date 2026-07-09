<?php

namespace App\Notifications;

use App\Models\CourseClass;
use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClassJoinRejectedNotification extends Notification
{
    use Queueable, ChecksNotificationPreferences;

    public CourseClass $courseClass;

    /** @var array<int, string> */
    public array $supportedChannels = ['database', 'mail'];

    /**
     * Create a new notification instance.
     */
    public function __construct(CourseClass $courseClass)
    {
        $this->courseClass = $courseClass;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Từ chối tham gia lớp',
            'message' => 'Yêu cầu tham gia lớp học ' . $this->courseClass->name . ' của bạn đã bị từ chối.',
            'type' => 'error',
            'url' => '#',
        ];
    }
}
