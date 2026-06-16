<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UserDevice>
 */
class UserDeviceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fcm_token' => Str::random(120),
            'device_name' => fake()->randomElement(['Chrome on Windows', 'Safari on iPhone', 'Chrome on Android']),
            'last_active_at' => now()->subMinutes(fake()->numberBetween(1, 1440)),
        ];
    }
}
