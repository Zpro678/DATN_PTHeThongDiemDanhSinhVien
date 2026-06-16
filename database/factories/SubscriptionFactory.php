<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'start_date' => now()->subDays(fake()->numberBetween(1, 30)),
            'end_date' => now()->addDays(fake()->numberBetween(7, 60)),
            'status' => 'active',
        ];
    }
}
