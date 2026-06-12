<?php

namespace Database\Factories;

use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassMember>
 */
class ClassMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'class_id' => CourseClass::factory(),
            'student_code' => fake()->unique()->bothify('SV20####'),
            'full_name' => fake()->name(),
            'user_id' => fake()->optional(0.35)->randomElement([User::factory()]),
            'status' => 'active',
        ];
    }
}
