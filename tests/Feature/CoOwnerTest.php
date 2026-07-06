<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\ClassSettings;
use App\Models\CourseClass;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CoOwnerTest extends TestCase
{
    use RefreshDatabase;

    private function classFor(User $owner): CourseClass
    {
        return CourseClass::factory()->create(['owner_user_id' => $owner->id]);
    }

    public function test_managed_by_scope_covers_owner_and_co_owner_but_not_outsider(): void
    {
        $owner = User::factory()->create();
        $coOwner = User::factory()->create();
        $outsider = User::factory()->create();
        $class = $this->classFor($owner);

        $class->coOwners()->attach($coOwner->id, ['role' => 'co_owner', 'accepted_at' => now()]);

        $this->assertTrue(CourseClass::managedBy($owner->id)->whereKey($class->id)->exists());
        $this->assertTrue(CourseClass::managedBy($coOwner->id)->whereKey($class->id)->exists());
        $this->assertFalse(CourseClass::managedBy($outsider->id)->whereKey($class->id)->exists());

        $this->assertTrue($class->isManagedBy($coOwner->id));
        $this->assertFalse($class->isManagedBy($outsider->id));
        $this->assertTrue($class->isPrimaryOwner($owner->id));
        $this->assertFalse($class->isPrimaryOwner($coOwner->id));
    }

    public function test_primary_owner_can_add_and_remove_co_owner_and_invitee_is_notified(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'dongchu@vidu.com']);
        $class = $this->classFor($owner);

        Livewire::actingAs($owner)->test(ClassSettings::class, ['courseClass' => $class])
            ->set('coOwnerEmail', 'dongchu@vidu.com')
            ->call('addCoOwner')
            ->assertHasNoErrors();

        $this->assertTrue($class->fresh()->isManagedBy($invitee->id));
        $this->assertSame(1, Notification::where('notifiable_id', $invitee->id)->count());

        Livewire::actingAs($owner)->test(ClassSettings::class, ['courseClass' => $class])
            ->call('removeCoOwner', $invitee->id);

        $this->assertFalse($class->fresh()->isManagedBy($invitee->id));
    }

    public function test_add_co_owner_rejects_unknown_email(): void
    {
        $owner = User::factory()->create();
        $class = $this->classFor($owner);

        Livewire::actingAs($owner)->test(ClassSettings::class, ['courseClass' => $class])
            ->set('coOwnerEmail', 'khongtontai@vidu.com')
            ->call('addCoOwner')
            ->assertHasErrors('coOwnerEmail');

        $this->assertSame(0, $class->coOwners()->count());
    }

    public function test_co_owner_cannot_open_class_settings_page(): void
    {
        $owner = User::factory()->create();
        $coOwner = User::factory()->create();
        $class = $this->classFor($owner);
        $class->coOwners()->attach($coOwner->id, ['role' => 'co_owner', 'accepted_at' => now()]);

        // Cài đặt lớp là quyền của CHỦ CHÍNH: đồng chủ mở phải bị 403.
        Livewire::actingAs($coOwner)->test(ClassSettings::class, ['courseClass' => $class])
            ->assertForbidden();
    }
}
