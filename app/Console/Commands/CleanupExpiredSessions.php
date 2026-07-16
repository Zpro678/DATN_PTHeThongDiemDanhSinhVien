<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClassMeeting;
use App\Services\AttendanceCalculator;
use Carbon\Carbon;

class CleanupExpiredSessions extends Command
{
    // Tên lệnh dùng trong console
    protected $signature = 'attendance:cleanup-expired-sessions';
    
    protected $description = 'Xoá các phiên điểm danh của buổi học sau 48 tiếng kể từ khi kết thúc, giữ lại bảng tổng kết buổi.';

    public function handle()
    {
        // Lấy thời điểm 48 tiếng trước
        $cutoffTime = Carbon::now()->subHours(48);

        // Truy vấn tối ưu: Chỉ lấy các buổi ĐÃ ĐÓNG (closed), cách đây > 48h
        // và CHỈ LẤY những buổi vẫn còn phiên (whereHas 'sessions') để tránh lặp lại những buổi đã xoá.
        $meetings = ClassMeeting::where('status', 'closed')
            ->where('updated_at', '<=', $cutoffTime)
            ->whereHas('sessions')
            ->with('sessions') // Tải sẵn sessions để tránh N+1 query
            ->get();

        $count = 0;

        foreach ($meetings as $meeting) {
            // 1. Chốt hạ dữ liệu cuối cùng vào bảng meeting_summaries
            AttendanceCalculator::syncSummaries($meeting);

            // 2. Tiến hành xóa chi tiết các phiên (Sessions) & (Records)
            foreach ($meeting->sessions as $session) {
                // Nếu bạn muốn xoá vĩnh viễn (giải phóng dung lượng) dùng forceDelete()
                // Nếu hệ thống dùng SoftDeletes và bạn chỉ muốn xoá tạm ẩn thì dùng delete()
                $session->attendanceRecords()->forceDelete();
                $session->forceDelete();
            }

            $count++;
        }

        $this->info("Đã dọn dẹp chi tiết phiên điểm danh cho {$count} buổi học cũ hơn 48 tiếng.");
    }
}
