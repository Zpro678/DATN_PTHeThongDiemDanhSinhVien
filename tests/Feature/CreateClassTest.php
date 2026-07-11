<?php

namespace Tests\Feature;

use App\Livewire\User\CreateClass;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class CreateClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_open_the_create_class_page(): void
    {
        // Trang tạo lớp qua middleware plan:create_class -> cần có gói FREE để currentPlan() phân giải.
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $user = User::factory()->create();
        URL::defaults(['ma_user' => $user->id]);

        $this->actingAs($user)
            ->get(route('create-class'))
            ->assertOk()
            ->assertSee('Thêm lớp học mới')
            ->assertSee('Lưu lớp học');
    }

    public function test_user_can_create_a_course_class_from_the_livewire_form(): void
    {
        $user = User::factory()->create();
        URL::defaults(['ma_user' => $user->id]);

        Livewire::actingAs($user)
            ->test(CreateClass::class)
            ->set('name', 'Kiểm thử phần mềm')
            ->set('randomSuffix', '2401')
            ->set('description', 'Lớp học được tạo từ Livewire.')
            ->set('totalSessions', 20)
            ->set('requireApproval', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('managed-classes'));

        $this->assertDatabaseHas('classes', [
            'owner_user_id' => $user->id,
            'name' => 'Kiểm thử phần mềm',
            'join_key' => 'CLS2401',
            'total_sessions' => 20,
            'require_approval' => true,
            'status' => 'active',
        ]);
    }

    public function test_class_creation_entry_points_link_to_the_create_page(): void
    {
        $user = User::factory()->create();
        URL::defaults(['ma_user' => $user->id]);
        $createUrl = route('create-class');

        foreach (['dashboard', 'classes', 'managed-classes'] as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee($createUrl, false);
        }
    }

    public function test_managed_classes_page_renders_uuid_class_cards(): void
    {
        $user = User::factory()->create();
        URL::defaults(['ma_user' => $user->id]);

        CourseClass::factory()->create([
            'owner_user_id' => $user->id,
            'name' => 'Lớp UUID không lỗi',
            'join_key' => 'UUID-2026',
        ]);

        $this->actingAs($user)
            ->get(route('managed-classes'))
            ->assertOk()
            ->assertSee('Lớp UUID không lỗi')
            ->assertSee('UUID-2026');
    }
}
