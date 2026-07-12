<?php

namespace Database\Factories;

use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CheckInScanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_session_id' => ClassSession::factory(),
            'user_id' => User::factory(),
            'scan_type' => fake()->randomElement(['qr', 'gps', 'link']),
            'payload_signature' => hash_hmac('sha256', Str::random(32), 'factory-secret'),
            'is_valid' => true,
            'fail_reason' => null,
            'ip_address' => fake()->ipv4(),
            'device_fingerprint' => hash('sha256', Str::random(32)),
            'scanned_at' => now(),
        ];
    }

    public function invalid(string $reason = 'invalid_signature'): static
    {
        return $this->state(fn (array $attributes) => [
            'is_valid' => false,
            'fail_reason' => $reason,
        ]);
    }
}
