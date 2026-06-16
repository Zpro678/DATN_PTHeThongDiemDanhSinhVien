<?php

namespace Database\Factories;

use App\Models\CheckInScan;
use App\Models\ClassSession;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckInScan>
 */
class CheckInScanFactory extends Factory
{
    public function definition(): array
    {
        $isValid = fake()->boolean(85);

        return [
            'tenant_id' => Tenant::factory(),
            'class_session_id' => ClassSession::factory(),
            'user_id' => fake()->optional(0.3)->randomElement([User::factory()]),
            'student_code_attempt' => fake()->bothify('SV20####'),
            'scan_type' => fake()->randomElement(['qr', 'gps', 'link']),
            'payload_signature' => hash('sha256', fake()->uuid()),
            'is_valid' => $isValid,
            'fail_reason' => $isValid ? null : fake()->randomElement(['timeout', 'invalid_signature', 'out_of_range']),
            'ip_address' => fake()->ipv4(),
            'device_info' => fake()->userAgent(),
            'scanned_at' => now()->subMinutes(fake()->numberBetween(0, 60)),
        ];
    }
}
