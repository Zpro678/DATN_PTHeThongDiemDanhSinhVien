<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Phát khi dữ liệu điểm danh/chuyên cần của một lớp thay đổi (lưu điểm danh thủ công,
 * chốt phiên QR, tổng kết buổi, học viên bị lưu trữ/khôi phục...). Dùng chung kênh
 * "class.{classId}" với StudentJoinedClass để mọi trang đang mở của lớp (danh sách
 * phiên, quản lý học viên, tổng kết buổi, lịch sử điểm danh...) tự làm mới không cần F5.
 *
 * Dùng ShouldBroadcastNow để bắn tức thì, không phụ thuộc queue worker.
 */
class ClassDataUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $classId;

    public function __construct(string $classId)
    {
        $this->classId = $classId;
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('class.' . $this->classId),
        ];
    }
}
