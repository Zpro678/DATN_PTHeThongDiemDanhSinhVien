<?php

namespace Database\Factories;

use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassMeetingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_id' => CourseClass::factory(),
            'user_Created' => User::factory(),
            'name' => 'Buổi '.fake()->numberBetween(1, 15),
            'date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'start_time' => '07:00:00',
            'end_time' => '09:30:00',
            'status' => 'active',
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'closed']);
    }
}
