<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Phát khi có học viên tham gia lớp (trực tiếp qua mã/link/QR), hoặc gửi/được
 * duyệt yêu cầu tham gia. Trang chi tiết lớp của giảng viên nghe kênh
 * "class.{classId}" để cập nhật sĩ số, danh sách học viên và số chờ duyệt realtime.
 *
 * Dùng ShouldBroadcastNow để bắn tức thì, không phụ thuộc queue worker
 * (giống StudentCheckedIn của bảng điểm danh QR).
 */
class StudentJoinedClass implements ShouldBroadcastNow
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
