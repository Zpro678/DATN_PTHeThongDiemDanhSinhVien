<?php

namespace Database\Factories;

use App\Models\ClassMember;
use App\Models\CourseClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceSummaryFactory extends Factory
{
    public function definition(): array
    {
        $absent = fake()->numberBetween(0, 5);

        return [
            'class_id' => CourseClass::factory(),
            'class_member_id' => ClassMember::factory(),
            'total_present' => fake()->numberBetween(5, 12),
            'total_late' => fake()->numberBetween(0, 3),
            'total_absent' => $absent,
            'total_excused' => fake()->numberBetween(0, 2),
            'is_banned_from_exam' => $absent > 3,
            'updated_at' => now(),
        ];
    }
}
