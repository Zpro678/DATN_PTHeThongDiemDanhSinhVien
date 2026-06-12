<?php

namespace Database\Factories;

use App\Models\CourseClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseClass>
 */
class CourseClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'owner_id' => User::factory(),
            'code' => fake()->unique()->bothify('CLASS-###'),
            'name' => fake()->randomElement([
                'Lap trinh Web',
                'Co so du lieu',
                'Phan tich thiet ke he thong',
                'Cong nghe phan mem',
            ]),
            'description' => fake()->optional()->sentence(),
            'subject_code' => fake()->bothify('IT###'),
            'semester' => fake()->randomElement(['2025-2026 HK1', '2025-2026 HK2']),
            'require_approval' => fake()->boolean(30),
            'status' => 'active',
            'total_sessions' => 15,
            'lessons_per_session' => 3,
        ];
    }
}
