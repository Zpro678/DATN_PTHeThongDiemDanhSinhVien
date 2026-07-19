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

    public function test_co_owner_can_open_and_save_class_settings(): void
    {
        $owner = User::factory()->create();
        $coOwner = User::factory()->create();
        $class = $this->classFor($owner);
        $class->coOwners()->attach($coOwner->id, ['role' => 'co_owner', 'accepted_at' => now()]);

        // Đồng chủ mở được trang cài đặt và sửa được cấu hình lớp.
        Livewire::actingAs($coOwner)->test(ClassSettings::class, ['courseClass' => $class])
            ->assertOk()
            ->assertSee('Bạn đang xem với vai trò')
            ->assertDontSee('Thêm đồng chủ theo email')
            ->assertDontSee('Xóa lớp học')
            ->set('name', 'Tên do đồng chủ đổi')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Tên do đồng chủ đổi', $class->fresh()->name);
    }

    public function test_primary_owner_sees_co_owner_and_delete_controls(): void
    {
        $owner = User::factory()->create();
        $class = $this->classFor($owner);

        Livewire::actingAs($owner)->test(ClassSettings::class, ['courseClass' => $class])
            ->assertOk()
            ->assertSee('Thêm đồng chủ theo email')
            ->assertSee('Xóa lớp học')
            ->assertDontSee('Bạn đang xem với vai trò');
    }

    public function test_outsider_cannot_open_class_settings_page(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $class = $this->classFor($owner);

        Livewire::actingAs($outsider)->test(ClassSettings::class, ['courseClass' => $class])
            ->assertForbidden();
    }

    public function test_co_owner_cannot_add_or_remove_co_owners(): void
    {
        $owner = User::factory()->create();
        $coOwner = User::factory()->create();
        $someone = User::factory()->create();
        $class = $this->classFor($owner);
        $class->coOwners()->attach($coOwner->id, ['role' => 'co_owner', 'accepted_at' => now()]);

        // Ẩn nút trong blade là chưa đủ — method Livewire gọi thẳng được, phải chặn ở server.
        Livewire::actingAs($coOwner)->test(ClassSettings::class, ['courseClass' => $class])
            ->set('coOwnerEmail', $someone->email)
            ->call('addCoOwner')
            ->assertForbidden();

        Livewire::actingAs($coOwner)->test(ClassSettings::class, ['courseClass' => $class])
            ->call('removeCoOwner', $coOwner->id)
            ->assertForbidden();

        // Danh sách đồng chủ không đổi: vẫn đúng 1 người.
        $this->assertSame(1, $class->coOwners()->count());
    }

    public function test_co_owner_cannot_delete_class(): void
    {
        $owner = User::factory()->create();
        $coOwner = User::factory()->create();
        $class = $this->classFor($owner);
        $class->coOwners()->attach($coOwner->id, ['role' => 'co_owner', 'accepted_at' => now()]);

        Livewire::actingAs($coOwner)->test(ClassSettings::class, ['courseClass' => $class])
            ->call('confirmDelete')
            ->assertForbidden();

        Livewire::actingAs($coOwner)->test(ClassSettings::class, ['courseClass' => $class])
            ->call('deleteClass')
            ->assertForbidden();

        // fresh() bỏ qua global scope nên vẫn trả về bản ghi đã soft-delete —
        // phải dùng assertNotSoftDeleted mới bắt được lỗi thật.
        $this->assertNotSoftDeleted($class);
    }

    public function test_primary_owner_still_manages_co_owners_and_deletes_class(): void
    {
        $owner = User::factory()->create();
        $class = $this->classFor($owner);

        Livewire::actingAs($owner)->test(ClassSettings::class, ['courseClass' => $class])
            ->call('confirmDelete')
            ->assertOk()
            ->call('deleteClass');

        $class->refresh();
        $this->assertEquals('archived', $class->status);
    }
}
