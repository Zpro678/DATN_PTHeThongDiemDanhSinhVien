<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestApproved extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $session = $this->leaveRequest->classSession;
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
}
