<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CouponFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement([Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED]);

        return [
            'code' => Str::upper(fake()->unique()->bothify('SALE####')),
            'applicable_plan_id' => null,
            'type' => $type,
            'value' => $type === Coupon::TYPE_PERCENT ? fake()->numberBetween(5, 50) : fake()->randomElement([20000, 50000, 100000]),
            'usage_limit' => fake()->randomElement([null, 50, 100]),
            'used_count' => 0,
            'valid_from' => now(),
            'valid_until' => now()->addMonth(),
            'is_active' => true,
            'created_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
