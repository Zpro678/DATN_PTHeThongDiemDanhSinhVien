<?php

namespace Tests\Feature;

use App\Livewire\Student\ClassShow;
use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Trang chi tiết lớp phía học viên: đảm bảo blade render được và mốc chuyên cần
 * bám theo cấu hình của lớp thay vì con số 80% viết cứng.
 */
class StudentClassShowViewTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: CourseClass, 1: User} */
    private function makeMembership(array $classAttributes = []): array
    {
        $owner = User::factory()->create();
        $class = CourseClass::factory()->create(['owner_user_id' => $owner->id] + $classAttributes);

        $student = User::factory()->create();
        $member = ClassMember::create([
            'class_id' => $class->id,
            'user_id' => $student->id,
            'status' => ClassMember::STATUS_ACTIVE,
        ]);
        $member->syncProfile(['full_name' => $student->name, 'email' => $student->email]);

        return [$class, $student];
    }

    public function test_page_renders_with_default_threshold(): void
    {
        [$class, $student] = $this->makeMembership();

        Livewire::actingAs($student)->test(ClassShow::class, ['courseClass' => $class])
            ->assertOk()
            ->assertSee('Tiến độ chuyên cần')
            ->assertSee('80% (Yêu cầu)')
            ->assertSee('80% trở lên');
    }

    public function test_threshold_marker_follows_class_configuration(): void
    {
        // Quỹ vắng 35% -> chuyên cần tối thiểu 65%, vạch đỏ phải nằm ở 65%.
        [$class, $student] = $this->makeMembership(['absence_limit_percent' => 35]);

        Livewire::actingAs($student)->test(ClassShow::class, ['courseClass' => $class])
            ->assertOk()
            ->assertSee('65% (Yêu cầu)')
            ->assertSee('65% trở lên')
            ->assertSee('left: 65%', escape: false)
            ->assertDontSee('80% (Yêu cầu)');
    }
}
