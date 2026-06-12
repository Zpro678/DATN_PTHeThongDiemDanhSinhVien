<?php

namespace Database\Factories;

use App\Models\AttendanceRule;
use App\Models\CourseClass;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRule>
 */
class AttendanceRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'class_id' => CourseClass::factory(),
            'rule_type' => fake()->randomElement(['warning', 'ban_exam', 'auto_drop']),
            'threshold_value' => fake()->randomElement([10.00, 20.00, 30.00]),
            'condition_operator' => '>=',
            'is_active' => true,
        ];
    }
}
