<?php

namespace Tests\Feature;

use App\Livewire\Admin\Packages\CouponCreate;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CouponPercentLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_percent_over_100_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)->test(CouponCreate::class)
            ->set('code', 'OVER100')
            ->set('type', 'PERCENT')
            ->set('value', 150)
            ->call('save')
            ->assertHasErrors(['value' => 'max']);

        $this->assertDatabaseMissing('coupons', ['code' => 'OVER100']);
    }

    public function test_percent_exactly_100_is_allowed(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)->test(CouponCreate::class)
            ->set('code', 'FULL100')
            ->set('type', 'PERCENT')
            ->set('value', 100)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('coupons', ['code' => 'FULL100']);
    }

    public function test_fixed_amount_over_100_is_allowed(): void
    {
        // FIXED là số tiền (VND) nên KHÔNG bị chặn trần 100.
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)->test(CouponCreate::class)
            ->set('code', 'FIXED50K')
            ->set('type', 'FIXED')
            ->set('value', 50000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('coupons', ['code' => 'FIXED50K']);
    }
}
