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

// Dọn dẹp log điểm danh (check_in_scans) cũ hơn 90 ngày vào 1h sáng mỗi ngày.
Schedule::command('attendance:cleanup-scans --days=90')->dailyAt('01:00')->withoutOverlapping();

// Rút hàng chờ email thông báo import (Outbox) và gửi có tiết chế: tối đa 200 mail/phút.
// Nhờ vậy dù nhiều tài khoản import cùng lúc, email không bị bắn dồn làm SMTP quá tải.
Schedule::command('import:flush-notifications --limit=200')->everyMinute()->withoutOverlapping();


// Tự động dọn dẹp các phiên điểm danh của buổi học đã kết thúc qua 48 tiếng (chạy mỗi giờ 1 lần)
Schedule::command('attendance:cleanup-expired-sessions')->hourly()->withoutOverlapping();