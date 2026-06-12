<?php

namespace Database\Factories;

use App\Models\AttendanceMethod;
use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['present', 'late', 'absent', 'excused']);

        return [
            'tenant_id' => Tenant::factory(),
            'class_session_id' => ClassSession::factory(),
            'class_member_id' => ClassMember::factory(),
            'status' => $status,
            'method_id' => AttendanceMethod::factory(),
            'is_verified' => fake()->boolean(),
            'check_in_time' => in_array($status, ['present', 'late'], true) ? now()->subMinutes(fake()->numberBetween(1, 20)) : null,
            'ip_address' => fake()->ipv4(),
            'device_info' => fake()->userAgent(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
