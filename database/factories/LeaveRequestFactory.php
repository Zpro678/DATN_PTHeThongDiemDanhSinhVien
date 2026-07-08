<?php

namespace Database\Factories;

use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_member_id' => ClassMember::factory(),
            'class_meeting_id' => \App\Models\ClassMeeting::factory(),
            'reason' => fake()->sentence(),
            'proof_image' => null,
            'status' => 'pending',
            'rejected_reason' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'created_at' => now(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejected_reason' => fake()->sentence(),
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }
}
