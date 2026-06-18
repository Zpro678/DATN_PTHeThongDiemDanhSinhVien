<?php

namespace Database\Factories;

use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'class_id' => CourseClass::factory(),
            'action' => fake()->randomElement(['LOGIN_SUCCESS', 'ATTENDANCE_UPDATED', 'SUBSCRIPTION_UPGRADED']),
            'table_name' => 'attendance_records',
            'row_id' => fake()->numberBetween(1, 1000),
            'old_values' => ['status' => 'absent'],
            'new_values' => ['status' => 'present'],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => now(),
        ];
    }
}
