<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmitted extends Notification
{
    use Queueable;

    public LeaveRequest $leaveRequest;

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
        return ['database'];
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
        $class = $member ? $member->courseClass : null;
        $session = $this->leaveRequest->classSession;
        $date = $session && $session->date ? $session->date->format('d/m/Y') : 'Buổi học';

        return [
            'title' => 'Đơn xin phép mới',
            'message' => "Sinh viên {$student->name} đã gửi đơn xin phép cho lớp " . ($class ? $class->join_key : '') . " vào ngày {$date}.",
            'leave_request_id' => $this->leaveRequest->id,
            'class_id' => $class ? $class->id : null,
            'type' => 'leave_request',
            'icon' => 'file-text',
            'url' => route('lecturer.leave-requests.index', ['ma_user' => $notifiable->id, 'class' => $class ? $class->id : null]),
        ];
    }
}
