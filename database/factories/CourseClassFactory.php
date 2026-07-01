<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_user_id' => User::factory(),
            'join_key' => 'CLS'.fake()->unique()->numerify('####'),
            'name' => fake()->randomElement(['Lập trình Web', 'Cơ sở dữ liệu', 'Công nghệ phần mềm', 'Mạng máy tính']),
            'description' => fake()->sentence(),
            'late_threshold' => 15,
            'deduct_excused_absence' => false,
            'require_approval' => fake()->boolean(),
            'status' => 'active',
            'total_sessions' => 15,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'archived']);
    }
}
