<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TelegramTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test {chat_id? : Chat ID của người nhận}';

    protected $description = 'Gửi một tin nhắn test qua Telegram';

    public function handle(\App\Services\TelegramBot $bot)
    {
        $chatId = $this->argument('chat_id');
        if (!$chatId) {
            $user = \App\Models\User::whereNotNull('telegram_chat_id')->first();
            if ($user) {
                $chatId = $user->telegram_chat_id;
                $this->info("Không truyền chat_id, sử dụng chat_id của user: {$user->name} ({$chatId})");
            } else {
                $this->error('Vui lòng truyền vào chat_id hoặc liên kết một tài khoản với Telegram trước.');
                return;
            }
        }

        $res = $bot->sendMessage($chatId, '🔔 Đây là tin nhắn test từ hệ thống điểm danh!');
        
        if ($res) {
            $this->info("✅ Đã gửi tin nhắn test thành công đến Chat ID: {$chatId}");
        } else {
            $this->error("❌ Gửi tin nhắn thất bại. Hãy kiểm tra lại cấu hình bot.");
        }
    }
}
