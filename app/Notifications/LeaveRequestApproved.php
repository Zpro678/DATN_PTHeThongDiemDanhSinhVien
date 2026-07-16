<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;

class LeaveRequestApproved extends Notification implements ShouldBroadcast
{
    use Queueable, ChecksNotificationPreferences;

    /** @var array<int, string> */
    public array $supportedChannels = ['database', 'broadcast', 'mail'];

    public function __construct(public LeaveRequest $leaveRequest) {}

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

    public function toArray(object $notifiable): array
    {
        $session = $this->leaveRequest->classMeeting;
        $class   = $this->leaveRequest->classMember?->courseClass;
        $date    = $session?->date?->format('d/m/Y') ?? 'Buổi học';

        return [
            'title'            => 'Đơn xin nghỉ được duyệt',
            'message'          => "Đơn xin nghỉ của bạn cho lớp " . ($class?->join_key ?? '') . " ngày {$date} đã được duyệt.",
            'leave_request_id' => $this->leaveRequest->id,
            'class_id'         => $class?->id,
            'type'             => 'leave_request_approved',
            'icon'             => 'check-circle',
            'url'              => route('student.leave-requests.show', [
                'ma_user'      => $notifiable->id,
                'leaveRequest' => $this->leaveRequest->id,
            ]),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->leaveRequest->classMember->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NotificationEvent';
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'notification' => $this->toArray($notifiable),
        ]);
    }
}
