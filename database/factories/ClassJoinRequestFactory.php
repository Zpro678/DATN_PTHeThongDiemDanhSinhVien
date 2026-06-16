<?php

namespace Database\Factories;

use App\Models\ClassJoinRequest;
use App\Models\CourseClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassJoinRequest>
 */
class ClassJoinRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'class_id' => CourseClass::factory(),
            'user_id' => User::factory(),
            'student_code' => fake()->unique()->bothify('SV20####'),
            'full_name' => fake()->name(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}
