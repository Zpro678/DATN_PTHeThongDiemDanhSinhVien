<?php

namespace Database\Factories;

use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_id' => CourseClass::factory(),
            'student_code' => 'SV'.fake()->unique()->numerify('########'),
            'full_name' => fake()->name(),
            'user_id' => User::factory(),
            'status' => 'active',
        ];
    }

    public function unlinked(): static
    {
        return $this->state(fn (array $attributes) => ['user_id' => null]);
    }

    public function dropped(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'dropped']);
    }
}
