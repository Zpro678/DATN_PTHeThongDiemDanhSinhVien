<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\TelegramBot;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Đăng ký webhook Telegram trỏ về server hiện tại để nhận /start (liên kết Chat ID).
 *
 * Dùng sau khi deploy lên domain HTTPS công khai:
 *   php artisan telegram:set-webhook https://ten-mien/telegram/webhook
 * Không truyền URL thì lấy theo config('app.url').
 */
class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url? : URL webhook công khai (HTTPS)}';

    protected $description = 'Đăng ký webhook Telegram Bot để tự động liên kết Chat ID';

    public function handle(TelegramBot $bot): int
    {
        if (blank($bot->token())) {
            $this->error('Chưa cấu hình telegram_bot_token. Nhập token ở trang cấu hình admin trước.');

            return self::FAILURE;
        }

        $url = $this->argument('url') ?: rtrim((string) config('app.url'), '/').'/telegram/webhook';

        if (! str_starts_with($url, 'https://')) {
            $this->error("Telegram chỉ chấp nhận webhook HTTPS công khai. URL hiện tại: {$url}");

            return self::FAILURE;
        }

        $secret = Setting::get('telegram_webhook_secret');
        if (blank($secret)) {
            $secret = Str::random(40);
            Setting::set('telegram_webhook_secret', $secret);
        }

        $res = $bot->setWebhook($url, $secret);

        if (($res['ok'] ?? false) === true) {
            $this->info("✅ Đã đăng ký webhook: {$url}");

            return self::SUCCESS;
        }

        $this->error('❌ Lỗi đăng ký webhook: '.json_encode($res, JSON_UNESCAPED_UNICODE));

        return self::FAILURE;
    }
}
