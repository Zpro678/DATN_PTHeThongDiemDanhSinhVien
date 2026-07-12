<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockedUserMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_access_authenticated_route(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_blocked_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        // Admin khóa tài khoản trong khi phiên vẫn đang hoạt động.
        $user->update(['status' => 'blocked']);

        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
