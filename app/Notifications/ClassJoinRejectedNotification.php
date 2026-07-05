<?php

namespace App\Notifications;

use App\Models\CourseClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassJoinRejectedNotification extends Notification
{
    use Queueable;

    public CourseClass $courseClass;

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
        return ['database'];
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
