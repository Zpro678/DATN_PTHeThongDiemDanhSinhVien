<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * Bao gói các lời gọi Telegram Bot API dùng chung cho webhook (nhận /start để liên
 * kết Chat ID), command đăng ký webhook và các tiện ích gửi tin nhắn.
 *
 * Token lấy từ Setting 'telegram_bot_token' (admin nhập ở trang cấu hình) — cùng
 * nguồn mà AppServiceProvider nạp vào config('services.telegram-bot-api.token').
 */
class TelegramBot
{
    /** Token bot hiện hành (Setting ưu tiên, fallback config). */
    public function token(): ?string
    {
        return Setting::get('telegram_bot_token') ?: config('services.telegram-bot-api.token');
    }

    public function api(string $method): string
    {
        return "https://api.telegram.org/bot{$this->token()}/{$method}";
    }

    /**
     * Gửi một tin nhắn văn bản tới chat. Trả false nếu chưa có token hoặc API lỗi.
     */
    public function sendMessage(int|string $chatId, string $text): bool
    {
        if (blank($this->token())) {
            return false;
        }

        $res = Http::asForm()->post($this->api('sendMessage'), [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ]);

        return $res->ok() && $res->json('ok') === true;
    }

    /**
     * Đăng ký webhook để Telegram đẩy update về server (kèm secret_token để xác thực).
     *
     * @return array<string, mixed>
     */
    public function setWebhook(string $url, string $secret): array
    {
        return Http::asForm()->post($this->api('setWebhook'), [
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => json_encode(['message']),
            'drop_pending_updates' => true,
        ])->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getMe(): array
    {
        return Http::get($this->api('getMe'))->json() ?? [];
    }

    /**
     * Username của bot (bỏ @). Ưu tiên Setting, nếu chưa có thì hỏi getMe rồi lưu lại.
     */
    public function username(): ?string
    {
        $cached = Setting::get('telegram_bot_username');
        if ($cached) {
            return $cached;
        }

        $username = $this->getMe()['result']['username'] ?? null;
        if ($username) {
            Setting::set('telegram_bot_username', $username);
        }

        return $username;
    }
}
