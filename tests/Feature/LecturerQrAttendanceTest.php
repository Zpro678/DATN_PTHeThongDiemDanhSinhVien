<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\Attendance\QrAttendanceCreate;
use App\Livewire\Lecturer\Attendance\QrAttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LecturerQrAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_classes_gets_demo_class_on_qr_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('lecturer.attendance.qr.create'))
            ->assertOk()
            ->assertSee('Thiết lập điểm danh')
            ->assertSee('Lớp demo điểm danh QR')
            ->assertSee('Bắt đầu phát mã');

        $courseClass = CourseClass::query()
            ->where('owner_user_id', $user->id)
            ->where('code', 'DEMO-'.$user->id.'-QR')
            ->firstOrFail();

        $this->assertSame(8, ClassMember::query()->where('class_id', $courseClass->id)->count());
    }

    public function test_qr_attendance_can_start_with_auto_demo_class(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(QrAttendanceCreate::class)
            ->call('save')
            ->assertHasNoErrors();

        $session = ClassSession::query()
            ->where('created_by', $user->id)
            ->firstOrFail();

        $this->assertSame('active', $session->status);
        $this->assertNotNull($session->qr_token);
        $this->assertNotNull($session->token_expires_at);
        $this->assertSame(8, AttendanceRecord::query()->where('class_session_id', $session->id)->count());
    }

    public function test_qr_attendance_station_can_search_and_update_status(): void
    {
        [$owner, $session] = $this->createQrSession();

        $this->actingAs($owner)
            ->get(route('lecturer.attendance.qr.session', $session))
            ->assertOk()
            ->assertSee('Trạm chờ điểm danh')
            ->assertSee('Tìm MSSV, tên');

        $record = AttendanceRecord::query()
            ->where('class_session_id', $session->id)
            ->whereHas('classMember', fn ($query) => $query->where('student_code', 'QR002'))
            ->firstOrFail();

        Livewire::actingAs($owner)
            ->test(QrAttendanceSession::class, ['session' => $session->id])
            ->set('search', 'QR002')
            ->assertSee('Trần Gia Bảo')
            ->assertDontSee('Nguyễn Minh Anh')
            ->call('updateStatus', $record->id, 'present')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('attendance_records', [
            'id' => $record->id,
            'status' => 'present',
        ]);
    }

    /**
     * @return array{User, ClassSession}
     */
    private function createQrSession(): array
    {
        $owner = User::factory()->create();
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id,
            'created_by' => $owner->id,
            'status' => 'active',
            'qr_token' => 'QRSESSIONTOKEN',
            'token_expires_at' => now()->addMinutes(15),
        ]);

        collect([
            ['QR001', 'Nguyễn Minh Anh'],
            ['QR002', 'Trần Gia Bảo'],
            ['QR003', 'Lê Hoàng Nam'],
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
                'check_in_time' => null,
            ]);
        });

        return [$owner, $session];
    }
}
