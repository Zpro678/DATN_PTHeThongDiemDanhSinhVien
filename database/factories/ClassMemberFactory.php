<?php

namespace Database\Factories;

use App\Models\ClassMember;
use App\Models\ClassMemberProfile;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_id' => CourseClass::factory(),
            'user_id' => User::factory(),
            'status' => ClassMember::STATUS_ACTIVE,
            'status_changed_at' => null,
        ];
    }

    public function configure(): static
    {
        // Mặc định kèm hồ sơ danh tính để roster/export có tên SV.
        return $this->afterCreating(function (ClassMember $member) {
            if (! $member->profile()->exists()) {
                ClassMemberProfile::factory()->for($member)->create();
            }
        });
    }

    /**
     * Không tạo hồ sơ danh tính kèm theo.
     */
    public function withoutProfile(): static
    {
        return $this->afterCreating(fn (ClassMember $member) => $member->profile()->delete());
    }

    public function unlinked(): static
    {
        return $this->state(fn (array $attributes) => ['user_id' => null]);
    }

    public function left(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClassMember::STATUS_LEFT,
            'status_changed_at' => now(),
        ]);
    }

    public function removed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClassMember::STATUS_REMOVED,
            'status_changed_at' => now(),
        ]);
    }
}
