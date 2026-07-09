<?php

namespace App\Notifications;

use App\Models\MeetingSummary;
use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceResultNotification extends Notification
{
    use Queueable, ChecksNotificationPreferences;

    /** @var array<int, string> */
    public array $supportedChannels = ['database', 'mail'];

    public function __construct(public MeetingSummary $summary) {}

    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'present' => 'Có mặt',
            'late' => 'Đi muộn',
            'absent' => 'Vắng',
            'excused' => 'Có phép',
        ];

        $status = $statusLabels[$this->summary->status] ?? $this->summary->status;
        $className = $this->summary->meeting->courseClass->name ?? 'Lớp học';
        $meetingDate = $this->summary->meeting->date->format('d/m/Y');

        return [
            'title'            => 'Kết quả điểm danh',
            'message'          => "Bạn được đánh giá là [{$status}] trong buổi học ngày {$meetingDate} môn {$className}.",
            'meeting_id'       => $this->summary->meeting_id,
            'class_id'         => $this->summary->meeting->course_class_id,
            'type'             => 'attendance_result',
            'icon'             => 'calendar-check',
            'url'              => route('student.attendance.history', ['ma_user' => $notifiable->getKey()]),
        ];
    }
}
