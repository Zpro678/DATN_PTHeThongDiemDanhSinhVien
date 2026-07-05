<?php

namespace App\Notifications;

use App\Models\CourseClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassJoinedNotification extends Notification
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
