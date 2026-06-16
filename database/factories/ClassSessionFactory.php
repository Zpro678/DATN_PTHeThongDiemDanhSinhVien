<?php

namespace Database\Factories;

use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ClassSession>
 */
class ClassSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'class_id' => CourseClass::factory(),
            'name' => 'Buoi '.fake()->numberBetween(1, 15),
            'date' => fake()->dateTimeBetween('-2 weeks', '+2 weeks'),
            'start_time' => fake()->time('H:i:s'),
            'end_time' => fake()->time('H:i:s'),
            'qr_token' => Str::uuid()->toString(),
            'token_expires_at' => now()->addMinutes(30),
            'gps_latitude' => fake()->latitude(10, 11),
            'gps_longitude' => fake()->longitude(106, 107),
            'gps_radius' => fake()->numberBetween(30, 50),
            'status' => fake()->randomElement(['pending', 'active', 'closed']),
        ];
    }
}
