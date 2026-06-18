<?php

namespace Database\Factories;

use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassJoinRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_id' => CourseClass::factory(),
            'user_id' => User::factory(),
            'student_code' => 'SV'.fake()->unique()->numerify('########'),
            'full_name' => fake()->name(),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'approved']);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'rejected']);
    }
}
