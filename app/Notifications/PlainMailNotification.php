<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Thông báo CHỈ gửi mail tới một địa chỉ email trần (không cần tài khoản).
 *
 * Dùng cho sinh viên mới chỉ có hồ sơ trong lớp (class_member_profiles) mà chưa
 * liên kết User. GenericNotification KHÔNG dùng được cho nhóm này vì nó bám vào
 * model User: kênh database/broadcast cần notifiable có bảng thông báo, còn
 * ChecksNotificationPreferences::via() đọc $notifiable->email và
 * ->telegram_chat_id — AnonymousNotifiable (Notification::route) không có.
 *
 * Không đính kèm nút "Xem chi tiết": người nhận chưa có tài khoản nên không có
 * trang lớp riêng để trỏ tới.
 */
class PlainMailNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $body,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting($this->title)
            ->line($this->body);
    }
}
