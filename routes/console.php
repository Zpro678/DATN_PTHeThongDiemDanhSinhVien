<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động chốt các buổi điểm danh đã quá giờ kết thúc (cần chạy scheduler nền).
Schedule::command('attendance:close-expired')->everyMinute()->withoutOverlapping();
