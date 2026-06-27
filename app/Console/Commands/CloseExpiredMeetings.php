<?php

namespace App\Console\Commands;

use App\Models\ClassMeeting;
use Illuminate\Console\Command;

class CloseExpiredMeetings extends Command
{
    protected $signature = 'attendance:close-expired';

    protected $description = 'Tự động chốt các buổi điểm danh đã quá giờ kết thúc.';

    public function handle(): int
    {
        $closed = 0;

        ClassMeeting::query()
            ->where('status', '!=', 'closed')
            ->whereNotNull('end_time')
            ->with('sessions')
            ->chunkById(200, function ($meetings) use (&$closed) {
                foreach ($meetings as $meeting) {
                    if ($meeting->closeIfExpired()) {
                        $closed++;
                    }
                }
            });

        $this->info("Đã chốt {$closed} buổi điểm danh quá giờ.");

        return self::SUCCESS;
    }
}
