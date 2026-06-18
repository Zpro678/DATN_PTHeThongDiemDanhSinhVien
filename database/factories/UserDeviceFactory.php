<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserDeviceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fcm_token' => Str::random(180),
            'device_name' => fake()->randomElement(['Chrome on Windows', 'Safari on iPhone', 'Android App']),
            'last_active_at' => now(),
            'created_at' => now(),
        ];
    }
}
