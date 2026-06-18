<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->words(2, true),
            'price' => fake()->randomElement([0, 99000, 199000, 299000]),
            'max_classes' => fake()->numberBetween(1, 20),
            'can_export_excel' => fake()->boolean(),
            'created_at' => now(),
        ];
    }
}
