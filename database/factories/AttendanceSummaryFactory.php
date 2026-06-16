<?php

namespace Database\Factories;

use App\Models\AttendanceSummary;
use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSummary>
 */
class AttendanceSummaryFactory extends Factory
{
    public function definition(): array
    {
        $absent = fake()->numberBetween(0, 5);

        return [
            'tenant_id' => Tenant::factory(),
            'class_id' => CourseClass::factory(),
            'class_member_id' => ClassMember::factory(),
            'total_present' => fake()->numberBetween(5, 12),
            'total_late' => fake()->numberBetween(0, 3),
            'total_absent' => $absent,
            'total_excused' => fake()->numberBetween(0, 2),
            'is_banned_from_exam' => $absent >= 4,
        ];
    }
}
