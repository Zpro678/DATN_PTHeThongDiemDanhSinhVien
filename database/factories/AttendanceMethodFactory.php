<?php

namespace Database\Factories;

use App\Models\AttendanceMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceMethod>
 */
class AttendanceMethodFactory extends Factory
{
    public function definition(): array
    {
        $code = fake()->randomElement(['manual', 'qr', 'gps', 'link']);

        return [
            'code' => $code,
            'name' => ucfirst($code),
        ];
    }
}
