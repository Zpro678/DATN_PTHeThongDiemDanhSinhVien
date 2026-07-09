<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanConfigFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'max_classes' => fake()->numberBetween(1, 20),
            'max_students_per_class' => fake()->randomElement([50, 100, 200]),
            'can_export_excel' => fake()->boolean(),
        ];
    }
}
