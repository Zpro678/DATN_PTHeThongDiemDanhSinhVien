<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\Attendance\QrAttendanceCreate;
use App\Livewire\Lecturer\Attendance\QrAttendanceSession;
use App\Livewire\Lecturer\ClassSettings;
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
            ->assertSee('THIẾT LẬP ĐIỂM DANH')
            ->assertSee('Lớp demo điểm danh QR')
            ->assertSee('Bắt đầu phát mã');

        $courseClass = CourseClass::query()
            ->where('owner_user_id', $user->id)
            ->where('join_key', 'DEMO-'.$user->id.'-QR')
            ->firstOrFail();

        $this->assertSame(8, ClassMember::query()->where('class_id', $courseClass->id)->count());
    }

    public function test_qr_attendance_can_start_with_auto_demo_class(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(QrAttendanceCreate::class)
            ->set('name', 'Buổi điểm danh QR')
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
            ->whereHas('classMember.profile', fn ($query) => $query->where('student_code', 'QR002'))
            ->firstOrFail();

        Livewire::actingAs($owner)
            ->test(QrAttendanceSession::class, ['session' => $session->id])
            ->set('search', 'QR002')
            ->assertSee('Qr Beta Target')
            ->assertDontSee('Qr Alpha Hidden')
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
            'status' => 'active',
            'qr_token' => 'QRSESSIONTOKEN',
            'token_expires_at' => now()->addMinutes(15),
        ]);

        collect([
            ['QR001', 'Qr Alpha Hidden'],
            ['QR002', 'Qr Beta Target'],
            ['QR003', 'Qr Gamma Extra'],
        ])->each(function (array $student) use ($courseClass, $session): void {
            $member = ClassMember::factory()->withoutProfile()->create([
                'class_id' => $courseClass->id,
            ]);
            $member->syncProfile(['student_code' => $student[0], 'full_name' => $student[1]]);

            AttendanceRecord::factory()->create([
                'class_session_id' => $session->id,
                'class_member_id' => $member->id,
                'status' => 'pending',
                'check_in_time' => null,
            ]);
        });

        return [$owner, $session];
    }

    // Lưu ý: cấu hình GPS mức lớp đã bị loại bỏ khỏi schema (DBML mới);
    // GPS giờ chỉ thiết lập ở mức phiên điểm danh, nên test cũ về GPS mặc định của lớp đã được gỡ.
}
