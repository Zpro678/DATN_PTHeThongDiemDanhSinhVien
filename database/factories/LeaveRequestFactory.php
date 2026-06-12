<?php

namespace Database\Factories;

use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\LeaveRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'approved', 'rejected']);

        return [
            'tenant_id' => Tenant::factory(),
            'class_member_id' => ClassMember::factory(),
            'class_session_id' => ClassSession::factory(),
            'reason' => fake()->sentence(),
            'proof_image' => fake()->optional()->filePath(),
            'status' => $status,
            'rejected_reason' => $status === 'rejected' ? fake()->sentence() : null,
            'reviewed_by' => $status === 'pending' ? null : User::factory(),
            'reviewed_at' => $status === 'pending' ? null : now(),
        ];
    }
}
