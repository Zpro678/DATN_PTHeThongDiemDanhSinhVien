<?php

namespace Database\Factories;

use App\Models\ClassMeeting;
use App\Models\ClassMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetingSummaryFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['present', 'present', 'late', 'absent', 'excused']);
        $deduction = match ($status) {
            'late' => 0.5,
            'absent' => 1.0,
            default => 0.0,
        };

        return [
            'meeting_id' => ClassMeeting::factory(),
            'class_member_id' => ClassMember::factory(),
            'status' => $status,
            'deduction' => $deduction,
            'auto_status' => $status,
            'is_overridden' => false,
            'note' => null,
        ];
    }
}
