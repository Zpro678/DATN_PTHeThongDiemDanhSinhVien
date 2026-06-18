<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\Attendance\ManualAttendanceCreate;
use App\Livewire\Lecturer\Attendance\ManualAttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LecturerManualAttendanceCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_classes_gets_demo_class_on_manual_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('lecturer.attendance.manual.create'))
            ->assertOk()
            ->assertSee('Lớp demo điểm danh')
            ->assertSee('Tổng quan')
            ->assertDontSee('validation.required');

        $courseClass = CourseClass::query()
            ->where('owner_user_id', $user->id)
            ->where('code', 'DEMO-'.$user->id.'-MANUAL')
            ->firstOrFail();

        $this->assertSame(8, ClassMember::query()->where('class_id', $courseClass->id)->count());
    }

    public function test_manual_attendance_can_start_with_auto_demo_class(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManualAttendanceCreate::class)
            ->call('save')
            ->assertHasNoErrors();

        $session = ClassSession::query()
            ->where('created_by', $user->id)
            ->firstOrFail();

        $this->assertSame('active', $session->status);
        $this->assertSame(8, AttendanceRecord::query()->where('class_session_id', $session->id)->count());
    }

    public function test_manual_attendance_session_can_search_by_name_or_student_code(): void
    {
        [$owner, $session] = $this->createManualSession();

        $this->actingAs($owner)
            ->get(route('lecturer.attendance.manual.session', $session))
            ->assertOk()
            ->assertSee('Tìm kiếm')
            ->assertSee('Tìm theo tên hoặc mã số sinh viên');

        Livewire::actingAs($owner)
            ->test(ManualAttendanceSession::class, ['session' => $session->id])
            ->set('search', 'DEMO002')
            ->call('searchStudents')
            ->assertSee('Trần Thị Bình')
            ->assertDontSee('Nguyễn Văn An')
            ->set('search', 'An')
            ->call('searchStudents')
            ->assertSee('Nguyễn Văn An')
            ->assertDontSee('Trần Thị Bình');
    }

    /**
     * @return array{User, ClassSession}
     */
    private function createManualSession(): array
    {
        $owner = User::factory()->create();
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id,
            'created_by' => $owner->id,
            'qr_token' => null,
            'token_expires_at' => null,
            'status' => 'active',
        ]);

        collect([
            ['DEMO001', 'Nguyễn Văn An'],
            ['DEMO002', 'Trần Thị Bình'],
            ['DEMO003', 'Lê Minh Cường'],
        ])->each(function (array $student) use ($courseClass, $session): void {
            $member = ClassMember::factory()->create([
                'class_id' => $courseClass->id,
                'student_code' => $student[0],
                'full_name' => $student[1],
            ]);

            AttendanceRecord::factory()->create([
                'class_session_id' => $session->id,
                'class_member_id' => $member->id,
                'status' => 'pending',
            ]);
        });

        return [$owner, $session];
    }
}
