<?php

namespace Database\Factories;

use App\Models\ClassMember;
use App\Models\ClassSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GpsVerificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => ClassSession::factory(),
            'member_id' => ClassMember::factory(),
            'token' => Str::random(64),
            'check_token' => null,
            'ip_address' => fake()->ipv4(),
            'lat' => 10.762622,
            'lng' => 106.660172,
            'accuracy' => fake()->randomFloat(2, 5, 50),
            'is_used' => false,
            'expires_at' => now()->addMinutes(5),
        ];
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => ['is_used' => true]);
    }
}
