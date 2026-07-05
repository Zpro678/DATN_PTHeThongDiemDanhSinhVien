<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Phát tín hiệu realtime khi một người dùng vừa nhận thông báo mới.
 *
 * Chỉ mang userId (KHÔNG kèm nội dung thông báo) và broadcast trên kênh công khai
 * notifications.{userId}. Client chỉ dùng tín hiệu này để yêu cầu chuông thông báo
 * tải lại dữ liệu từ DB (đã scope theo tài khoản đăng nhập), nên nội dung không bị lộ
 * qua websocket. Dùng ShouldBroadcastNow để gửi ngay (đồng bộ với StudentCheckedIn).
 */
class NotificationReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $userId) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('notifications.' . $this->userId),
        ];
    }
}
