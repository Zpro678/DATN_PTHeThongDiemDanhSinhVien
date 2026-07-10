<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\TelegramBot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Nhận update từ Telegram (webhook) để TỰ ĐỘNG liên kết Chat ID với tài khoản.
 *
 * Luồng: người dùng bấm "Liên kết Telegram" ở Hồ sơ -> hệ thống tạo mã dùng-một-lần
 * lưu ở cache (tg_link:{code} => userId) và mở deep-link https://t.me/<bot>?start=<code>
 * -> Telegram gửi "/start <code>" tới bot -> webhook này tra mã ra user và lưu
 * telegram_chat_id, bật kênh Telegram. Không cần bảng mới.
 */
class TelegramWebhookController extends Controller
{
    public function handle(Request $request, TelegramBot $bot): JsonResponse
    {
        // Xác thực: Telegram gửi kèm header đúng bằng secret đã đặt khi setWebhook.
        $secret = Setting::get('telegram_webhook_secret');
        if ($secret && ! hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            abort(403);
        }

        $message = $request->input('message') ?? $request->input('edited_message');
        $chatId = data_get($message, 'chat.id');
        $text = trim((string) data_get($message, 'text', ''));

        if (! $chatId || $text === '') {
            return response()->json(['ok' => true]);
        }

        if (str_starts_with($text, '/start')) {
            return $this->handleStart($bot, $chatId, $text);
        }

        if (in_array($text, ['/stop', '/unlink', '/huy'], true)) {
            return $this->handleUnlink($bot, $chatId);
        }

        // Tin nhắn khác: nhắc cách dùng.
        $bot->sendMessage($chatId, 'Vào trang <b>Hồ sơ</b> trên ứng dụng và bấm "Liên kết Telegram" để nhận thông báo. Gửi /stop để hủy liên kết.');

        return response()->json(['ok' => true]);
    }

    private function handleStart(TelegramBot $bot, int|string $chatId, string $text): JsonResponse
    {
        $parts = preg_split('/\s+/', $text, 2);
        $code = trim($parts[1] ?? '');

        if ($code === '') {
            $bot->sendMessage($chatId, 'Xin chào! Để nhận thông báo, hãy vào trang <b>Hồ sơ</b> trên ứng dụng và bấm "Liên kết Telegram".');

            return response()->json(['ok' => true]);
        }

        // pull() = đọc và xóa ngay -> mã chỉ dùng được một lần.
        $userId = Cache::pull("tg_link:{$code}");
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            $bot->sendMessage($chatId, '❌ Mã liên kết không hợp lệ hoặc đã hết hạn. Vui lòng thử lại từ trang Hồ sơ.');

            return response()->json(['ok' => true]);
        }

        $user->forceFill([
            'telegram_chat_id' => (string) $chatId,
            'notification_preferences' => array_merge($user->notificationPreferences(), ['telegram' => true]),
        ])->save();

        $bot->sendMessage($chatId, "✅ Đã liên kết Telegram với tài khoản <b>{$user->name}</b>. Từ giờ bạn sẽ nhận thông báo tại đây.");

        return response()->json(['ok' => true]);
    }

    private function handleUnlink(TelegramBot $bot, int|string $chatId): JsonResponse
    {
        $user = User::where('telegram_chat_id', (string) $chatId)->first();

        if ($user) {
            $user->forceFill([
                'telegram_chat_id' => null,
                'notification_preferences' => array_merge($user->notificationPreferences(), ['telegram' => false]),
            ])->save();
            $bot->sendMessage($chatId, 'Đã hủy liên kết. Bạn sẽ không nhận thông báo Telegram nữa.');
        } else {
            $bot->sendMessage($chatId, 'Tài khoản của bạn hiện chưa liên kết Telegram.');
        }

        return response()->json(['ok' => true]);
    }
}
