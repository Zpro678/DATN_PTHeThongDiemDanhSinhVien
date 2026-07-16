<?php

namespace App\Notifications;

use App\Models\CourseClass;
use App\Models\User;
use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Gửi cho GIẢNG VIÊN (chủ lớp / đồng chủ) khi một sinh viên THAM GIA THÀNH CÔNG
 * vào lớp (qua mã lớp, đường dẫn hoặc quét QR) mà không cần phê duyệt.
 */
class ClassMemberJoined extends Notification implements ShouldBroadcast
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
        if ($notifiable->telegram_chat_id && $notifiable->wantsNotificationChannel('telegram')) {
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
            ->action('Xem lớp học', $data['url'] ?? url('/'));
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
            'title' => 'Học viên mới tham gia lớp',
            'message' => 'Sinh viên ' . $this->student->name . ' vừa tham gia lớp ' . $this->courseClass->name . '.',
            'class_id' => $this->courseClass->id,
            'type' => 'class_member_joined',
            'level' => 'success',
            'icon' => 'user-check',
            'iconWrapper' => 'bg-emerald-100 text-emerald-600',
            'url' => route('lecturer.classes.show', [
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
