<?php

namespace Tests\Feature;

use App\Events\NotificationReceived;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationRealtimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_broadcasts_realtime_event_to_recipient(): void
    {
        Event::fake([NotificationReceived::class]);
        $user = User::factory()->create();

        app(NotificationService::class)->push($user->id, 'App\Notifications\Test', 'Tiêu đề', 'Nội dung');

        Event::assertDispatched(NotificationReceived::class, fn ($e) => $e->userId === $user->id);
    }

    public function test_database_notify_also_broadcasts_realtime(): void
    {
        Event::fake([NotificationReceived::class]);
        $user = User::factory()->create();

        // Thông báo database chuẩn qua notify() cũng phải phát tín hiệu realtime.
        $user->notify(new \Tests\Fixtures\PlainDatabaseNotification());

        Event::assertDispatched(NotificationReceived::class, fn ($e) => $e->userId === $user->id);
    }
}
