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
