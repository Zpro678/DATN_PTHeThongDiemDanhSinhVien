<?php

namespace App\Notifications;

use App\Models\CourseClass;
use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClassJoinedNotification extends Notification
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
            'title' => 'Tham gia lớp thành công',
            'message' => 'Bạn đã tham gia thành công lớp học ' . $this->courseClass->name,
            'type' => 'success',
            'url' => route('student.classes.show', [
                'ma_user' => $notifiable->id, 
                'courseClass' => $this->courseClass->id
            ]),
        ];
    }
}
