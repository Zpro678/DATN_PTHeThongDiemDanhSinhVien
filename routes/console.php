<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động chốt các buổi điểm danh đã quá giờ kết thúc (cần chạy scheduler nền).
Schedule::command('transactions:sync-status')->everyFiveMinutes();
Schedule::command('attendance:close-expired')->everyMinute()->withoutOverlapping();

// Tự động sao lưu cơ sở dữ liệu và xóa file cũ vào 12h đêm mỗi ngày.
Schedule::command('db:backup')->dailyAt('00:00')->withoutOverlapping();
