<?php

namespace Tests\Feature;

use App\Livewire\User\CreateClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_open_the_create_class_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('create-class'))
            ->assertOk()
            ->assertSee('Thêm lớp học mới')
            ->assertSee('Lưu lớp học');
    }

    public function test_user_can_create_a_course_class_from_the_livewire_form(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateClass::class)
            ->set('name', 'Kiểm thử phần mềm')
            ->set('code', 'test-2026-01')
            ->set('subjectCode', 'SWE401')
            ->set('semester', 'HK1 2026-2027')
            ->set('description', 'Lớp học được tạo từ Livewire.')
            ->set('totalSessions', 12)
            ->set('lessonsPerSession', 3)
            ->set('requireApproval', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('managed-classes'));

        $this->assertDatabaseHas('classes', [
            'owner_user_id' => $user->id,
            'name' => 'Kiểm thử phần mềm',
            'code' => 'TEST-2026-01',
            'subject_code' => 'SWE401',
            'semester' => 'HK1 2026-2027',
            'total_sessions' => 12,
            'lessons_per_session' => 3,
            'require_approval' => true,
            'status' => 'active',
        ]);
    }

    public function test_class_creation_entry_points_link_to_the_create_page(): void
    {
        $user = User::factory()->create();
        $createUrl = route('create-class');

        foreach (['dashboard', 'classes', 'managed-classes'] as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee($createUrl, false);
        }
    }
}
