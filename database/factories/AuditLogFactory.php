<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['LOGIN_SUCCESS', 'CLASS_CREATED', 'ATTENDANCE_UPDATED']),
            'table_name' => fake()->randomElement(['users', 'classes', 'attendance_records']),
            'row_id' => fake()->numberBetween(1, 100),
            'old_values' => null,
            'new_values' => ['note' => fake()->sentence()],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
