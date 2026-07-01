<?php

namespace Database\Factories;

use App\Models\PlanConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plan_tier' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomElement([0, 99000, 199000, 299000]),
            'duration_days' => 30,
            'is_active' => true,
        ];
    }

    /**
     * Tạo kèm bản ghi cấu hình giới hạn 1-1.
     */
    public function withConfig(array $config = []): static
    {
        return $this->afterCreating(function ($plan) use ($config) {
            PlanConfig::factory()->for($plan)->create($config);
        });
    }
}
