<?php

namespace Database\Factories;

use App\Models\ClassMember;
use App\Models\ClassSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttendanceRecordFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['present', 'present', 'present', 'late', 'absent']);

        return [
            'class_session_id' => ClassSession::factory(),
            'class_member_id' => ClassMember::factory(),
            'status' => $status,
            'is_account' => true,
            'check_in_time' => in_array($status, ['present', 'late'], true) ? now() : null,
            'ip_address' => fake()->ipv4(),
            'device_fingerprint' => hash('sha256', Str::random(32)),
            'distance_meters' => fake()->randomFloat(2, 1, 120),
            'note' => null,
        ];
    }

    public function excused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'excused',
            'check_in_time' => null,
        ]);
    }
}
