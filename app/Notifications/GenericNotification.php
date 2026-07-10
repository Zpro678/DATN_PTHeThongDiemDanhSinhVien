<?php

namespace App\Notifications;

use App\Notifications\Traits\ChecksNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class GenericNotification extends Notification implements ShouldBroadcast
{
    use Queueable, ChecksNotificationPreferences;

    /**
     * Kênh 'broadcast' được thêm để mọi thông báo đi qua push()/GenericNotification
     * đều bắn realtime tới chuông (server.cjs relay Redis -> socket.io). Trait
     * ChecksNotificationPreferences ánh xạ 'broadcast' về tùy chọn 'database'.
     *
     * @var array<int, string>
     */
    public array $supportedChannels = ['database', 'broadcast', 'mail'];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly string $databaseType,
        private readonly array $data,
    ) {}

    public function databaseType(object $notifiable): string
    {
        return $this->databaseType;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->data;
    }

    /**
     * Tên sự kiện socket phía client đang lắng nghe (khớp các notification khác).
     */
    public function broadcastAs(): string
    {
        return 'NotificationEvent';
    }

    /**
     * Payload realtime — chỉ là tín hiệu để chuông gọi $wire.$refresh() tải lại từ DB
     * (đã scope theo người dùng), không lộ dữ liệu nhạy cảm qua kênh.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        // Bắn ngay (không qua queue worker) để đảm bảo realtime chỉ cần Redis + server.cjs.
        return (new BroadcastMessage([
            'notification' => $this->data,
        ]))->onConnection('sync');
    }
}
