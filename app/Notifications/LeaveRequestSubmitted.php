<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;

class LeaveRequestSubmitted extends Notification implements ShouldBroadcast
{
    use Queueable, ChecksNotificationPreferences;

    public LeaveRequest $leaveRequest;

    /** @var array<int, string> */
    public array $supportedChannels = ['database', 'broadcast', 'mail'];

    /**
     * Create a new notification instance.
     */
    public function __construct(LeaveRequest $leaveRequest)
    {
        $this->leaveRequest = $leaveRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
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
        $member = $this->leaveRequest->classMember;
        $student = $member ? $member->user : auth()->user();
        $studentName = $this->leaveRequest->classMember->full_name ?? 'Học viên';
        $session = $this->leaveRequest->classMeeting;
        $class = $session?->courseClass;
        $className = $class?->name ?? 'Lớp học';
        $date = $session && $session->date ? $session->date->format('d/m/Y') : 'Buổi học';

        return [
            'title' => 'Đơn xin phép mới',
            'message' => "Sinh viên {$studentName} đã gửi đơn xin phép cho lớp {$className} vào ngày {$date}.",
            'leave_request_id' => $this->leaveRequest->id,
            'class_id' => $class?->id,
            'type' => 'leave_request',
            'icon' => 'file-text',
            'url' => route('lecturer.leave-requests.index', ['ma_user' => $notifiable->id, 'class' => $class ? $class->id : null]),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->leaveRequest->classMember->courseClass->owner_user_id),
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
