<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupOldCheckInScans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:cleanup-scans {--days=90 : Số ngày giữ lại dữ liệu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dọn dẹp dữ liệu check_in_scans cũ (mặc định 90 ngày) để giảm tải database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("Đang dọn dẹp các bản ghi check_in_scans cũ hơn {$days} ngày...");

        $count = \App\Models\CheckInScan::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Đã dọn dẹp thành công {$count} bản ghi rác.");
    }
}
