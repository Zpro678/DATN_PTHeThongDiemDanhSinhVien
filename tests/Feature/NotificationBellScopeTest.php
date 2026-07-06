<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use App\Livewire\NotificationBell;
use Tests\TestCase;

class NotificationBellScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_scopes_to_current_user(): void
    {
        $a = User::factory()->create(['name' => 'Student A']);
        $b = User::factory()->create(['name' => 'Student B']);
        $svc = app(NotificationService::class);
        $svc->push($a->id, 'App\Notifications\Test', 'ThongBaoCuaA', 'A');
        $svc->push($b->id, 'App\Notifications\Test', 'ThongBaoCuaB', 'B');

        Livewire::actingAs($a)->test(NotificationBell::class)
            ->assertSee('ThongBaoCuaA')->assertDontSee('ThongBaoCuaB');
    }

    public function test_full_page_layout_bell_scopes_to_current_user(): void
    {
        $a = User::factory()->create(['name' => 'Student A']);
        $b = User::factory()->create(['name' => 'Student B']);
        $svc = app(NotificationService::class);
        $svc->push($a->id, 'App\Notifications\Test', 'ThongBaoCuaA', 'A');
        $svc->push($b->id, 'App\Notifications\Test', 'ThongBaoCuaB', 'B');

        URL::defaults(['ma_user' => $a->id]);
        $res = $this->actingAs($a)->get(route('dashboard'));
        $res->assertOk();
        $res->assertSee('ThongBaoCuaA');
        $res->assertDontSee('ThongBaoCuaB');
    }

    public function test_delete_one_and_delete_all_notifications(): void
    {
        $a = User::factory()->create();
        $svc = app(NotificationService::class);
        $svc->push($a->id, 'App\Notifications\Test', 'T1', 'x');
        $svc->push($a->id, 'App\Notifications\Test', 'T2', 'y');

        $firstId = $a->notifications()->orderBy('created_at')->value('id');

        // Xóa 1 thông báo (popup "Xóa" gọi $wire.deleteNotification).
        Livewire::actingAs($a)->test(NotificationBell::class)
            ->call('deleteNotification', $firstId);
        $this->assertSame(1, $a->notifications()->count());

        // Xóa tất cả (popup "Xóa" gọi $wire.deleteAll).
        Livewire::actingAs($a)->test(NotificationBell::class)
            ->call('deleteAll');
        $this->assertSame(0, $a->notifications()->count());
    }
}
