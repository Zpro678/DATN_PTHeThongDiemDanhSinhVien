<?php

namespace Database\Factories;

use App\Models\ClassMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassMemberProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_member_id' => ClassMember::factory(),
            'student_code' => 'SV'.fake()->unique()->numerify('########'),
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
