<?php

namespace Tests\Feature;

use App\Events\NotificationReceived;
use App\Livewire\Profile\EditProfile;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_defaults_to_database_notifications_only(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->wantsNotificationChannel('database'));
        $this->assertTrue($user->wantsNotificationChannel('broadcast'));
        $this->assertFalse($user->wantsNotificationChannel('mail'));
        $this->assertFalse($user->wantsNotificationChannel('telegram'));
    }

    public function test_notification_service_respects_disabled_database_channel(): void
    {
        Event::fake([NotificationReceived::class]);

        $user = User::factory()->create([
            'notification_preferences' => [
                'database' => false,
                'mail' => false,
            ],
        ]);

        app(NotificationService::class)->push($user->id, 'App\Notifications\Test', 'Tiêu đề', 'Nội dung');

        $this->assertSame(0, $user->notifications()->count());
        Event::assertNotDispatched(NotificationReceived::class);
    }

    public function test_notification_service_sends_mail_when_only_mail_channel_is_enabled(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'notification_preferences' => [
                'database' => false,
                'mail' => true,
            ],
        ]);

        app(NotificationService::class)->push($user->id, 'App\Notifications\Test', 'Tiêu đề', 'Nội dung');

        Notification::assertSentTo(
            $user,
            GenericNotification::class,
            fn (GenericNotification $notification, array $channels): bool => $channels === ['mail'],
        );
    }

    public function test_profile_page_can_save_notification_preferences(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->set('notificationPreferences.database', false)
            ->set('notificationPreferences.mail', true)
            ->call('saveNotificationPreferences')
            ->assertHasNoErrors();

        $this->assertSame([
            'database' => false,
            'mail' => true,
        ], $user->refresh()->notification_preferences);
    }

    public function test_profile_page_can_toggle_mail_notifications(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'database' => true,
                'mail' => false,
            ],
        ]);

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->call('toggleNotificationPreference', 'mail')
            ->assertHasNoErrors();

        $this->assertSame([
            'database' => true,
            'mail' => true,
        ], $user->refresh()->notification_preferences);
    }
}
