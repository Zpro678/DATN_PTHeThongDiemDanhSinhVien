<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\Attendance\AttendanceCreate;
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
            ->get(route('lecturer.attendance.create'))
            ->assertOk()
            ->assertSee('Tạo buổi điểm danh')
            ->assertSee('Lớp demo điểm danh')
            ->assertDontSee('validation.required');

        $courseClass = CourseClass::query()
            ->where('owner_user_id', $user->id)
            ->where('join_key', 'DEMO-'.$user->id.'-ATT')
            ->firstOrFail();

        $this->assertSame(4, ClassMember::query()->where('class_id', $courseClass->id)->count());
    }

    public function test_manual_attendance_can_start_with_auto_demo_class(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AttendanceCreate::class)
            ->set('name', 'Buổi điểm danh thủ công')
            ->set('meetingEndTime', now()->addMinutes(90)->format('H:i'))
            ->call('createManualSession')
            ->assertHasNoErrors();

        $session = ClassSession::query()
            ->where('created_by', $user->id)
            ->firstOrFail();

        $this->assertSame('active', $session->status);
        $this->assertSame(4, AttendanceRecord::query()->where('class_session_id', $session->id)->count());
    }

    public function test_manual_attendance_session_can_search_by_name_or_email(): void
    {
        [$owner, $session] = $this->createManualSession();

        $this->actingAs($owner)
            ->get(route('lecturer.attendance.manual.session', $session))
            ->assertOk()
            ->assertSee('Danh sách học viên')
            ->assertSee('Tìm theo tên hoặc mã số...');

        Livewire::actingAs($owner)
            ->test(ManualAttendanceSession::class, ['session' => $session->id])
            ->set('search', 'beta@demo.test')
            ->call('searchStudents')
            ->assertSee('Manual Beta Hidden')
            ->assertDontSee('Manual Alpha Target')
            ->set('search', 'Alpha Target')
            ->call('searchStudents')
            ->assertSee('Manual Alpha Target')
            ->assertDontSee('Manual Beta Hidden');
    }

    public function test_creating_attendance_without_students_prompts_import(): void
    {
        $owner = User::factory()->create();
        // Lớp KHÔNG có sinh viên active -> không được tạo điểm danh, phải báo import trước.
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);

        Livewire::actingAs($owner)
            ->test(AttendanceCreate::class)
            ->set('classId', (string) $courseClass->id)
            ->set('name', 'Buổi thủ công')
            ->set('meetingEndTime', now()->addMinutes(90)->format('H:i'))
            ->call('createManualSession')
            ->assertHasErrors(['classId'])
            ->assertRedirect(route('lecturer.classes.show', ['ma_user' => $owner->id, 'courseClass' => $courseClass->id, 'openImport' => 1]));

        // Không có buổi/phiên nào được tạo.
        $this->assertSame(0, ClassSession::query()->where('class_id', $courseClass->id)->count());
    }

    /**
     * @return array{User, ClassSession}
     */
    private function createManualSession(): array
    {
        $owner = User::factory()->create();
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $meeting = \App\Models\ClassMeeting::factory()->create([
            'class_id' => $courseClass->id,
            'user_Created' => $owner->id,
            'date' => now()->addDay()->toDateString(),
            'status' => 'active',
        ]);
        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id,
            'meeting_id' => $meeting->id,
            'created_by' => $owner->id,
            'date' => $meeting->date,
            'qr_token' => null,
            'token_expires_at' => null,
            'status' => 'active',
        ]);

        collect([
            ['alpha@demo.test', 'Manual Alpha Target'],
            ['beta@demo.test', 'Manual Beta Hidden'],
            ['gamma@demo.test', 'Manual Gamma Extra'],
        ])->each(function (array $student) use ($courseClass, $session): void {
            $member = ClassMember::factory()->withoutProfile()->create([
                'class_id' => $courseClass->id,
            ]);
            $member->syncProfile(['email' => $student[0], 'full_name' => $student[1]]);

            AttendanceRecord::factory()->create([
                'class_session_id' => $session->id,
                'class_member_id' => $member->id,
                'status' => 'pending',
            ]);
        });

        return [$owner, $session];
    }
}
