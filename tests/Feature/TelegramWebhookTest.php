<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function setSecret(): string
    {
        $secret = 'test-secret-token';
        Setting::set('telegram_webhook_secret', $secret);
        Setting::set('telegram_bot_token', '123:ABC');

        return $secret;
    }

    public function test_start_with_valid_code_links_chat_id(): void
    {
        Http::fake(); // chặn gọi Telegram thật (sendMessage)
        $secret = $this->setSecret();

        $user = User::factory()->create(['telegram_chat_id' => null]);
        Cache::put('tg_link:CODE123', $user->id, now()->addMinutes(15));

        $res = $this->withHeaders(['X-Telegram-Bot-Api-Secret-Token' => $secret])
            ->postJson('/telegram/webhook', [
                'message' => ['chat' => ['id' => 555000], 'text' => '/start CODE123'],
            ]);

        $res->assertOk();
        $user->refresh();

        $this->assertSame('555000', $user->telegram_chat_id);
        $this->assertTrue($user->notificationPreferences()['telegram']);
        $this->assertFalse(Cache::has('tg_link:CODE123'), 'Mã liên kết phải bị tiêu thụ (dùng một lần).');
    }

    public function test_wrong_secret_is_forbidden(): void
    {
        Http::fake();
        $this->setSecret();

        $this->withHeaders(['X-Telegram-Bot-Api-Secret-Token' => 'sai-secret'])
            ->postJson('/telegram/webhook', [
                'message' => ['chat' => ['id' => 1], 'text' => '/start x'],
            ])
            ->assertForbidden();
    }

    public function test_invalid_code_does_not_link(): void
    {
        Http::fake();
        $secret = $this->setSecret();

        $user = User::factory()->create(['telegram_chat_id' => null]);

        $this->withHeaders(['X-Telegram-Bot-Api-Secret-Token' => $secret])
            ->postJson('/telegram/webhook', [
                'message' => ['chat' => ['id' => 777], 'text' => '/start KHONG-TON-TAI'],
            ])
            ->assertOk();

        $this->assertNull($user->fresh()->telegram_chat_id);
    }

    public function test_stop_unlinks_chat_id(): void
    {
        Http::fake();
        $secret = $this->setSecret();

        $user = User::factory()->create(['telegram_chat_id' => '999888']);

        $this->withHeaders(['X-Telegram-Bot-Api-Secret-Token' => $secret])
            ->postJson('/telegram/webhook', [
                'message' => ['chat' => ['id' => 999888], 'text' => '/stop'],
            ])
            ->assertOk();

        $this->assertNull($user->fresh()->telegram_chat_id);
    }
}
