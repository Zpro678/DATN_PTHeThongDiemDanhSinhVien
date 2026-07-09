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
