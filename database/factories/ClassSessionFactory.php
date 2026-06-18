<?php

namespace Database\Factories;

use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClassSessionFactory extends Factory
{
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'class_id' => CourseClass::factory(),
            'created_by' => User::factory(),
            'name' => 'Buổi '.fake()->numberBetween(1, 15),
            'date' => $date,
            'start_time' => '07:00:00',
            'end_time' => '09:30:00',
            'qr_token' => (string) Str::uuid(),
            'token_expires_at' => fake()->dateTimeBetween('now', '+30 minutes'),
            'gps_latitude' => 10.762622,
            'gps_longitude' => 106.660172,
            'gps_radius' => 100,
            'status' => 'pending',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'token_expires_at' => now()->addMinutes(15),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'token_expires_at' => now()->subMinute(),
        ]);
    }
}
