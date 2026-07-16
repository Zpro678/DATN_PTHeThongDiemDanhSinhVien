<?php

namespace App\Notifications;

use App\Models\MeetingSummary;
use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

class AttendanceResultNotification extends Notification
{
    use Queueable, ChecksNotificationPreferences;

    /** @var array<int, string> */
    public array $supportedChannels = ['database', 'mail'];

    public function __construct(public MeetingSummary $summary) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (\App\Models\Setting::get('enable_email_notifications', '1') == '1' && $notifiable->email) {
            $channels[] = 'mail';
        }

         // Lưu ý: Đảm bảo Telegram Channel đã được cấu hình trong hệ thống
        if ($notifiable->telegram_chat_id && $notifiable->wantsNotificationChannel('telegram')) {
            $channels[] = \App\Channels\SafeTelegramChannel::class;
        }
        return $channels;
    }

    public function toMail(object $notifiable)
    {
        $data = $this->toArray($notifiable);
        $mail = (new MailMessage)
                ->subject($data['title'])
                ->greeting("Chào ". ($notifiable->name ?? 'bạn') . ",");

        $paragraphs = explode("\n\n", $data['message']);
        foreach($paragraphs as $p) {
            $mail->line(trim($p));
        }
        
        return $mail->action('Xem chi tiết các phiên', $data['url'] ?? url('/'));
    }

    public function toTelegram(object $notifiable)
    {
        $data = $this->toArray($notifiable);
        $message = TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("*" . $data['title'] . "*\n\n" . $data['message']);
            
        if (isset($data['url'])) {
            $message->button('Xem chi tiết các phiên', $data['url']);
        }
        
        return $message;
    }

     public function toArray(object $notifiable): array
    {
        $statusKey = $this->summary->status;
        $className = $this->summary->meeting->courseClass->name ?? 'Lớp học';
        $meetingDate = $this->summary->meeting->date->format('d/m/Y');
        
        $title = 'Kết quả điểm danh';
        $message = '';
        $level = 'info';
        // Tuỳ biến nội dung dựa trên trạng thái
        if ($statusKey === 'absent' || $statusKey === 'late') {
            $statusName = $statusKey === 'absent' ? 'vắng' : 'đi muộn';
            $title = "Cảnh báo: Hôm nay bạn {$statusName} môn {$className}";
            
            $message = "Hôm nay bạn đã bị đánh dấu là [{$statusName}] trong buổi học ngày {$meetingDate} môn {$className}.\n\n";
            $message .= "⚠️ LƯU Ý: Bạn có 48 tiếng kể từ thời gian buổi kết thúc để nhấn vào link bên dưới xem chi tiết các phiên và khiếu nại lên giảng viên (nếu có sai sót) trước khi hệ thống chốt tổng kết.";
            
            // Đổi màu thông báo trên chuông Web (đỏ cho vắng, vàng cho muộn)
            $level = $statusKey === 'absent' ? 'danger' : 'warning'; 
        } else {
            $statusName = $statusKey === 'present' ? 'có mặt' : 'vắng có phép';
            $title = "Kết quả điểm danh môn {$className}";
            $message = "Hôm nay bạn được ghi nhận là [{$statusName}] trong buổi học ngày {$meetingDate} môn {$className}.";
            $level = 'success'; // Màu xanh trên chuông Web
        }
        return [
            'title'            => $title,
            'message'          => $message,
            'meeting_id'       => $this->summary->meeting_id,
            'class_id'         => $this->summary->meeting->course_class_id,
            'type'             => 'attendance_result',
            'level'            => $level, // Được NotificationService tự động lấy để tô màu giao diện
            'icon'             => 'calendar-check',
            // Đường dẫn trỏ thẳng về lịch sử điểm danh cá nhân để họ đối chiếu
            'url'              => route('student.attendance.history', ['ma_user' => $notifiable->getKey()]),
        ];
    }


    // public function toArray_bak(object $notifiable): array
    // {
    //     $statusKey = $this->summary->status;
    //     $className = $this->summary->meeting->courseClass-> name ?? 'Lớp học';
    //     $meetingDate = $this->summary->meeting->date->format('d/m/Y');
        
    //     $title = 'Kết quả điểm danh';
    //     $message = '';
    //     $level = 'info';

    //     // Tùy biến nội dung trên trạng thái
    //     if($statusKey === 'absent' || $statusKey === 'late') {
    //         $statusName = $statusKey === 'absent'? 'Vắng' : 'đi muộn';
    //         $title ="Cảnh báo: Hôm nay bạn {$statusName} môn {$className} ngày {$meetingDate}";
            
    //         $message = "Hôm nay bạn đã bị đánh dấu là [{$statusName}] trong buổi học ngày {$meetingDate} môn {$className}.\n\n";
    //         $message .= "⚠️ LƯU Ý: Bạn có 48 tiếng kể từ thời gian buổi kết thúc để nhấn vào link bên dưới xem chi tiết các phiên và khiếu nại lên giảng viên (nếu có sai sót) trước khi hệ thống chốt tổng kết.";
        
    //         $level = $statusKey === 'absent' ? 'danger' : 'warning'; 
    //     }
    //     else{
    //         $statusName =$statusKey === 'present' ? 'Có mặt' : 'Có phép';
    //      $title = "Kết quả điểm danh môn {$className}";
    //         $message = "Hôm nay bạn được ghi nhận là [{$statusName}] trong buổi học ngày {$meetingDate} môn {$className}.";
    //         $level = 'success'; // Màu xanh trên chuông Web   
    //     }
    //     $statusLabels = [
    //         'present' => 'Có mặt',
    //         'late' => 'Đi muộn',
    //         'absent' => 'Vắng',
    //         'excused' => 'Có phép',
    //     ];

     

    //     return [
    //         'title'            => 'Kết quả điểm danh',
    //         'message'          => "Bạn được đánh giá là [{$status}] trong buổi học ngày {$meetingDate} môn {$className}.",
    //         'meeting_id'       => $this->summary->meeting_id,
    //         'class_id'         => $this->summary->meeting->course_class_id,
    //         'type'             => 'attendance_result',
    //         'icon'             => 'calendar-check',
    //         'url'              => route('student.attendance.history', ['ma_user' => $notifiable->getKey()]),
    //     ];
    // }
}
