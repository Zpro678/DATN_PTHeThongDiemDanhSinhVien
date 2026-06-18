<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonth(),
            'status' => 'expired',
        ]);
    }
}
