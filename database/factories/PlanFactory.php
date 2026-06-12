<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        $code = fake()->unique()->randomElement(['FREE', 'PRO']);

        return [
            'code' => $code,
            'name' => $code === 'FREE' ? 'Free' : 'Pro',
            'price' => $code === 'FREE' ? 0 : 99000,
            'max_classes' => $code === 'FREE' ? 3 : 100,
            'can_export_excel' => $code !== 'FREE',
        ];
    }
}
