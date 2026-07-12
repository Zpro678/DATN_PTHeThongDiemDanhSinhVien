<?php

namespace App\Console\Commands;

use App\Mail\StudentImportNotificationMail;
use App\Models\PendingImportNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Rút hàng chờ email import (Outbox drain) và gửi có tiết chế tốc độ.
 *
 * Chạy theo scheduler mỗi phút với --limit N: mỗi phút tối đa N email được đẩy
 * vào hàng đợi -> throughput bị chặn ở N/phút dù có bao nhiêu tài khoản import
 * cùng lúc, nên SMTP không bao giờ bị bắn dồn. Lấy theo FIFO (created_at) để
 * công bằng giữa các tài khoản (ai import trước được gửi trước).
 */
class FlushImportNotifications extends Command
{
    protected $signature = 'import:flush-notifications {--limit=200 : Số email tối đa xử lý mỗi lần chạy}';

    protected $description = 'Gửi email thông báo import theo lô, có tiết chế tốc độ (Outbox drain).';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $rows = PendingImportNotification::query()
            ->where('status', PendingImportNotification::STATUS_PENDING)
            ->where('attempts', '<', PendingImportNotification::MAX_ATTEMPTS)
            ->orderBy('created_at') // FIFO — công bằng giữa các tài khoản.
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($rows as $row) {
            try {
                // Email đi vào hàng đợi 'mails' riêng (worker phải nghe queue này —
                // xem start-dev.bat). Tiết chế tốc độ đến từ --limit mỗi lần chạy scheduler.
                Mail::to($row->email)->queue(
                    (new StudentImportNotificationMail(
                        $row->class_name,
                        $row->join_key,
                        $row->full_name,
                        $row->email,
                    ))->onQueue('mails')
                );

                $row->update([
                    'status' => PendingImportNotification::STATUS_SENT,
                    'sent_at' => now(),
                ]);
                $sent++;
            } catch (\Throwable $e) {
                $row->increment('attempts');
                if ($row->attempts >= PendingImportNotification::MAX_ATTEMPTS) {
                    $row->update(['status' => PendingImportNotification::STATUS_FAILED]);
                }
                Log::error("Flush import mail failed for {$row->email}: ".$e->getMessage());
            }
        }

        $this->info("Đã đẩy {$sent}/{$rows->count()} email import vào hàng đợi.");

        return self::SUCCESS;
    }
}
