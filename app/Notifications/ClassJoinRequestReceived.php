<?php

namespace App\Notifications;

use App\Models\CourseClass;
use App\Models\User;
use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Gửi cho GIẢNG VIÊN (chủ lớp / đồng chủ) khi một sinh viên gửi YÊU CẦU tham gia
 * lớp cần phê duyệt. Giúp giảng viên biết để vào trang "Duyệt học viên".
 */
class ClassJoinRequestReceived extends Notification implements ShouldBroadcast
{
    use Queueable, ChecksNotificationPreferences;

    public CourseClass $courseClass;

    public User $student;

    /** @var array<int, string> */
    public array $supportedChannels = ['database', 'broadcast', 'mail'];

    public function __construct(CourseClass $courseClass, User $student)
    {
        $this->courseClass = $courseClass;
        $this->student = $student;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];
        if (\App\Models\Setting::get('enable_email_notifications', '1') == '1' && $notifiable->email) {
            if (!in_array('mail', $channels)) $channels[] = 'mail';
        }
        if (\App\Models\Setting::get('enable_telegram_notifications', '0') == '1' && $notifiable->telegram_chat_id && $notifiable->wantsNotificationChannel('telegram')) {
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
            ->action('Duyệt học viên', $data['url'] ?? url('/'));
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
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Yêu cầu tham gia lớp',
            'message' => 'Sinh viên ' . $this->student->name . ' đã gửi yêu cầu tham gia lớp ' . $this->courseClass->name . '. Vui lòng duyệt.',
            'class_id' => $this->courseClass->id,
            'type' => 'class_join_request',
            'level' => 'info',
            'icon' => 'log-in',
            'iconWrapper' => 'bg-blue-100 text-blue-600',
            'url' => route('lecturer.classes.pending-members', [
                'ma_user' => $notifiable->id,
                'courseClass' => $this->courseClass->id,
            ]),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NotificationEvent';
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return (new BroadcastMessage([
            'notification' => $this->toArray($notifiable),
        ]))->onConnection('sync');
    }
}
