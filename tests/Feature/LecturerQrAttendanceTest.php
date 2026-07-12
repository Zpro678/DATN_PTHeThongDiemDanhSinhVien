<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\Attendance\QrAttendanceCreate;
use App\Livewire\Lecturer\Attendance\QrAttendanceSession;
use App\Livewire\Lecturer\ClassSettings;
use App\Livewire\Student\AttendanceCheckIn;
use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
            ->set('meetingName', 'Buổi điểm danh QR')
            ->set('name', 'Phiên QR')
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
            ->assertSee('Danh sách học viên')
            ->assertSee('Tìm theo tên hoặc mã số');

        $record = AttendanceRecord::query()
            ->where('class_session_id', $session->id)
            ->whereHas('classMember.profile', fn ($query) => $query->where('email', 'qr-beta@demo.test'))
            ->firstOrFail();

        Livewire::actingAs($owner)
            ->test(QrAttendanceSession::class, ['session' => $session->id])
            ->set('search', 'qr-beta@demo.test')
            ->assertSee('Qr Beta Target')
            ->assertDontSee('Qr Alpha Hidden')
            ->call('setStatus', $record->id, 'present')
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
            ['qr-alpha@demo.test', 'Qr Alpha Hidden'],
            ['qr-beta@demo.test', 'Qr Beta Target'],
            ['qr-gamma@demo.test', 'Qr Gamma Extra'],
        ])->each(function (array $student) use ($courseClass, $session): void {
            $member = ClassMember::factory()->withoutProfile()->create([
                'class_id' => $courseClass->id,
            ]);
            $member->syncProfile(['email' => $student[0], 'full_name' => $student[1]]);

            AttendanceRecord::factory()->create([
                'class_session_id' => $session->id,
                'class_member_id' => $member->id,
                'status' => 'pending',
                'check_in_time' => null,
            ]);
        });

        return [$owner, $session];
    }

    public function test_refresh_token_rotates_qr_token_and_shortens_expiry(): void
    {
        [$owner, $session] = $this->createQrSession();
        $oldToken = $session->qr_token;

        Livewire::actingAs($owner)
            ->test(QrAttendanceSession::class, ['session' => $session->id])
            ->call('refreshToken');

        $session->refresh();

        // Token QR phải ĐỔI thật (không chỉ đổi ảnh).
        $this->assertNotSame($oldToken, $session->qr_token);

        // Hạn token ngắn theo nhịp làm mới (không còn là 15 phút như trước).
        $ttl = ClassSession::qrTokenTtlSecondsFor($session->qr_refresh_rate);
        $this->assertTrue($session->token_expires_at->lessThanOrEqualTo(now()->addSeconds($ttl + 2)));
    }

    public function test_stale_qr_token_is_rejected_after_rotation(): void
    {
        [$owner, $session] = $this->createQrSession();
        $oldToken = $session->qr_token;

        $session->rotateQrToken();
        $this->assertNotSame($oldToken, $session->fresh()->qr_token);

        // Sinh viên quét ẢNH CHỤP mã cũ -> token không còn khớp -> bị từ chối ngay khi mở trang.
        Livewire::test(AttendanceCheckIn::class, ['token' => $oldToken])
            ->assertSet('statusMessage', 'Mã điểm danh không hợp lệ hoặc không tồn tại.');
    }

    public function test_check_in_completes_even_after_qr_token_expired_while_session_open(): void
    {
        $base = Carbon::create(2026, 7, 5, 8, 0, 0);
        $this->travelTo($base);

        $student = User::factory()->create();
        $owner = User::factory()->create();
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $meeting = ClassMeeting::factory()->create([
            'class_id' => $courseClass->id,
            'user_Created' => $owner->id,
            'date' => $base->toDateString(),
            'start_time' => '00:00:00',
            'end_time' => '23:59:00', // Buổi mở cả ngày -> chắc chắn còn trong giờ.
            'status' => 'active',
        ]);
        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id,
            'meeting_id' => $meeting->id,
            'created_by' => $owner->id,
            'date' => $base->toDateString(),
            'status' => 'active',
            'qr_token' => 'ROTATETOKEN1',
            'qr_refresh_rate' => 10,
            'token_expires_at' => $base->copy()->addSeconds(15),
            'gps_latitude' => null, // Tắt GPS để test đúng nhánh xác thực token.
            'gps_longitude' => null,
            'gps_radius' => null,
        ]);
        $member = ClassMember::create([
            'class_id' => $courseClass->id,
            'user_id' => $student->id,
            'status' => ClassMember::STATUS_ACTIVE,
        ]);
        AttendanceRecord::factory()->create([
            'class_session_id' => $session->id,
            'class_member_id' => $member->id,
            'status' => 'pending',
            'check_in_time' => null,
        ]);

        // Quét khi token còn hạn -> mở được trang.
        $component = Livewire::actingAs($student)
            ->test(AttendanceCheckIn::class, ['token' => 'ROTATETOKEN1']);

        // Bấm điểm danh MUỘN, sau khi cửa sổ token QR đã trôi qua (buổi vẫn trong giờ).
        $this->travelTo($base->copy()->addSeconds(60));
        $component->call('checkIn', null)->assertSet('isSuccess', true);

        $this->assertDatabaseHas('attendance_records', [
            'class_session_id' => $session->id,
            'class_member_id' => $member->id,
            'status' => 'present',
        ]);

        $this->travelBack();
    }

    public function test_qr_checkin_always_records_present_not_late(): void
    {
        $base = Carbon::create(2026, 7, 5, 8, 0, 0);
        $this->travelTo($base);

        $owner = User::factory()->create();
        // Ngưỡng đi muộn của lớp = 5 phút — dù quét trễ hơn ngưỡng, PHIÊN QR vẫn chỉ ghi 'present'
        // (đi muộn để dành cho tổng kết buổi), nên ngưỡng này KHÔNG còn tác động lúc quét.
        $courseClass = CourseClass::factory()->create([
            'owner_user_id' => $owner->id,
            'late_threshold' => 5,
        ]);
        // Buổi được tạo tại $base (mốc "mở phiên đầu tiên").
        $meeting = ClassMeeting::factory()->create([
            'class_id' => $courseClass->id,
            'user_Created' => $owner->id,
            'date' => $base->toDateString(),
            'start_time' => '00:00:00',
            'end_time' => '23:59:00',
            'status' => 'active',
        ]);
        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id,
            'meeting_id' => $meeting->id,
            'created_by' => $owner->id,
            'date' => $base->toDateString(),
            'status' => 'active',
            'qr_token' => 'LATETOKEN1',
            'token_expires_at' => $base->copy()->addDay(), // Còn hạn để mount qua được cả 2 lần quét.
            'gps_latitude' => null,
            'gps_longitude' => null,
            'gps_radius' => null,
        ]);

        $makeStudent = function () use ($courseClass, $session) {
            $user = User::factory()->create();
            $member = ClassMember::create([
                'class_id' => $courseClass->id,
                'user_id' => $user->id,
                'status' => ClassMember::STATUS_ACTIVE,
            ]);
            AttendanceRecord::factory()->create([
                'class_session_id' => $session->id,
                'class_member_id' => $member->id,
                'status' => 'pending',
                'check_in_time' => null,
            ]);

            return [$user, $member];
        };

        [$earlyUser, $earlyMember] = $makeStudent();
        [$lateUser, $lateMember] = $makeStudent();

        // Trong ngưỡng (3 phút < 5) -> có mặt.
        $this->travelTo($base->copy()->addMinutes(3));
        Livewire::actingAs($earlyUser)
            ->test(AttendanceCheckIn::class, ['token' => 'LATETOKEN1'])
            ->call('checkIn', null)
            ->assertSet('isSuccess', true);
        $this->assertDatabaseHas('attendance_records', [
            'class_session_id' => $session->id,
            'class_member_id' => $earlyMember->id,
            'status' => 'present',
        ]);

        // Quá ngưỡng (8 phút > 5) -> VẪN 'present' (không tự tính đi muộn ở phiên QR).
        $this->travelTo($base->copy()->addMinutes(8));
        Livewire::actingAs($lateUser)
            ->test(AttendanceCheckIn::class, ['token' => 'LATETOKEN1'])
            ->call('checkIn', null)
            ->assertSet('isSuccess', true);
        $this->assertDatabaseHas('attendance_records', [
            'class_session_id' => $session->id,
            'class_member_id' => $lateMember->id,
            'status' => 'present',
        ]);

        // Không record nào bị đánh 'late' ở mức phiên khi quét QR.
        $this->assertDatabaseMissing('attendance_records', [
            'class_session_id' => $session->id,
            'status' => 'late',
        ]);

        $this->travelBack();
    }

    /**
     * @return array{User, ClassSession}
     */
    private function createOpenQrSessionNoGps(User $owner): array
    {
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $meeting = ClassMeeting::factory()->create([
            'class_id' => $courseClass->id,
            'user_Created' => $owner->id,
            'date' => now()->toDateString(),
            'start_time' => '00:00:00',
            'end_time' => '23:59:00',
            'status' => 'active',
        ]);
        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id,
            'meeting_id' => $meeting->id,
            'created_by' => $owner->id,
            'date' => now()->toDateString(),
            'status' => 'active',
            'qr_token' => 'DEVTOKEN'.uniqid(),
            'token_expires_at' => now()->addDay(),
            'gps_latitude' => null,
            'gps_longitude' => null,
            'gps_radius' => null,
        ]);

        return [$courseClass, $session];
    }

    private function enrolStudentWithRecord(CourseClass $courseClass, ClassSession $session): array
    {
        $user = User::factory()->create();
        $member = ClassMember::create([
            'class_id' => $courseClass->id,
            'user_id' => $user->id,
            'status' => ClassMember::STATUS_ACTIVE,
        ]);
        AttendanceRecord::factory()->create([
            'class_session_id' => $session->id,
            'class_member_id' => $member->id,
            'status' => 'pending',
            'check_in_time' => null,
        ]);

        return [$user, $member];
    }

    public function test_same_device_id_flags_both_students_logs_scan_and_notifies(): void
    {
        $owner = User::factory()->create();
        [$courseClass, $session] = $this->createOpenQrSessionNoGps($owner);
        [$userA, $memberA] = $this->enrolStudentWithRecord($courseClass, $session);
        [$userB, $memberB] = $this->enrolStudentWithRecord($courseClass, $session);

        // A điểm danh trên máy DEV-SAME.
        Livewire::actingAs($userA)->test(AttendanceCheckIn::class, ['token' => $session->qr_token])
            ->call('checkIn', null, 'DEV-SAME-0001')
            ->assertSet('isSuccess', true);
        // B điểm danh trên CHÍNH máy đó -> phát hiện điểm danh hộ.
        Livewire::actingAs($userB)->test(AttendanceCheckIn::class, ['token' => $session->qr_token])
            ->call('checkIn', null, 'DEV-SAME-0001')
            ->assertSet('isSuccess', true);

        // Cả 2 bản ghi đều bị gắn cờ trùng máy (để GV thấy đủ 2 SV).
        $this->assertSame('device_duplicate', AttendanceRecord::where('class_member_id', $memberA->id)->value('gps_fraud_flag'));
        $this->assertSame('device_duplicate', AttendanceRecord::where('class_member_id', $memberB->id)->value('gps_fraud_flag'));
        $this->assertSame('DEV-SAME-0001', AttendanceRecord::where('class_member_id', $memberB->id)->value('device_id'));

        // Nhật ký quét được ghi cho cả 2 lần.
        $this->assertSame(2, \App\Models\CheckInScan::where('class_session_id', $session->id)->count());

        // Giảng viên nhận thông báo điểm danh hộ, link mở phiên kèm bộ lọc same_device.
        $notif = \App\Models\Notification::query()
            ->where('notifiable_id', $owner->id)
            ->where('data->title', 'Phát hiện gian lận (Điểm danh hộ)')
            ->first();
        $this->assertNotNull($notif);
        $this->assertStringContainsString('filter=same_device', $notif->data['url']);

        // Bộ lọc "cùng 1 máy" ở phiên trả về đúng 2 SV dùng chung máy.
        Livewire::actingAs($owner)->test(QrAttendanceSession::class, ['session' => $session->id])
            ->set('statusFilter', 'same_device')
            ->assertViewHas('records', fn ($records) => $records->count() === 2)
            ->assertViewHas('sameDeviceCount', 2);
    }

    public function test_same_device_promotes_prior_fraud_flag_and_highlights_group(): void
    {
        $owner = User::factory()->create();
        [$courseClass, $session] = $this->createOpenQrSessionNoGps($owner);
        [$userA, $memberA] = $this->enrolStudentWithRecord($courseClass, $session);
        [$userB, $memberB] = $this->enrolStudentWithRecord($courseClass, $session);

        $userA->forceFill(['name' => 'Shared Device Alpha'])->save();
        $userB->forceFill(['name' => 'Shared Device Beta'])->save();

        Livewire::actingAs($userA)->test(AttendanceCheckIn::class, ['token' => $session->qr_token])
            ->call('checkIn', null, 'DEV-SAME-FLAG')
            ->assertSet('isSuccess', true);

        AttendanceRecord::where('class_member_id', $memberA->id)->update([
            'gps_fraud_flag' => 'impossible_travel',
            'note' => 'Nghi ngờ di chuyển bất khả thi.',
        ]);

        Livewire::actingAs($userB)->test(AttendanceCheckIn::class, ['token' => $session->qr_token])
            ->call('checkIn', null, 'DEV-SAME-FLAG')
            ->assertSet('isSuccess', true);

        $recordA = AttendanceRecord::where('class_member_id', $memberA->id)->firstOrFail();
        $this->assertSame('device_duplicate', $recordA->gps_fraud_flag);
        $this->assertStringContainsString('Cảnh báo trước đó: impossible_travel.', (string) $recordA->note);
        $this->assertSame('device_duplicate', AttendanceRecord::where('class_member_id', $memberB->id)->value('gps_fraud_flag'));

        Livewire::actingAs($owner)->test(QrAttendanceSession::class, ['session' => $session->id])
            ->set('statusFilter', 'same_device')
            ->assertViewHas('records', fn ($records) => $records->count() === 2)
            ->assertViewHas('sameDeviceCount', 2)
            ->assertViewHas('sharedDeviceIds', fn (array $deviceIds) => in_array('DEV-SAME-FLAG', $deviceIds, true))
            ->assertSeeHtml('class="truncate text-[15.5px] font-semibold text-red-600">Shared Device Alpha</p>')
            ->assertSeeHtml('class="truncate text-[15.5px] font-semibold text-red-600">Shared Device Beta</p>');
    }

    public function test_different_device_ids_do_not_trigger_false_duplicate(): void
    {
        $owner = User::factory()->create();
        [$courseClass, $session] = $this->createOpenQrSessionNoGps($owner);
        [$userA, $memberA] = $this->enrolStudentWithRecord($courseClass, $session);
        [$userB, $memberB] = $this->enrolStudentWithRecord($courseClass, $session);

        // Cùng IP/UA (như trên NAT) nhưng device_id KHÁC nhau -> KHÔNG được báo nhầm điểm danh hộ.
        Livewire::actingAs($userA)->test(AttendanceCheckIn::class, ['token' => $session->qr_token])
            ->call('checkIn', null, 'DEV-AAAA-1111')
            ->assertSet('isSuccess', true);
        Livewire::actingAs($userB)->test(AttendanceCheckIn::class, ['token' => $session->qr_token])
            ->call('checkIn', null, 'DEV-BBBB-2222')
            ->assertSet('isSuccess', true);

        $this->assertNull(AttendanceRecord::where('class_member_id', $memberA->id)->value('gps_fraud_flag'));
        $this->assertNull(AttendanceRecord::where('class_member_id', $memberB->id)->value('gps_fraud_flag'));

        Livewire::actingAs($owner)->test(QrAttendanceSession::class, ['session' => $session->id])
            ->assertViewHas('sameDeviceCount', 0);
    }

    public function test_device_check_toggle_off_disables_duplicate_detection(): void
    {
        $owner = User::factory()->create();
        [$courseClass, $session] = $this->createOpenQrSessionNoGps($owner);
        $session->forceFill(['device_check' => false])->save(); // Tắt kiểm tra thiết bị cho phiên.

        [$userA, $memberA] = $this->enrolStudentWithRecord($courseClass, $session);
        [$userB, $memberB] = $this->enrolStudentWithRecord($courseClass, $session);

        // Cùng device_id nhưng device_check TẮT -> không gắn cờ trùng máy.
        Livewire::actingAs($userA)->test(AttendanceCheckIn::class, ['token' => $session->qr_token])
            ->call('checkIn', null, 'DEV-OFF-0001')->assertSet('isSuccess', true);
        Livewire::actingAs($userB)->test(AttendanceCheckIn::class, ['token' => $session->qr_token])
            ->call('checkIn', null, 'DEV-OFF-0001')->assertSet('isSuccess', true);

        $this->assertNull(AttendanceRecord::where('class_member_id', $memberA->id)->value('gps_fraud_flag'));
        $this->assertNull(AttendanceRecord::where('class_member_id', $memberB->id)->value('gps_fraud_flag'));
    }

    public function test_proxy_device_used_by_many_students_escalates_to_lecturer(): void
    {
        $owner = User::factory()->create();
        [$courseClass, $session] = $this->createOpenQrSessionNoGps($owner);

        // 3 sinh viên cùng dùng MỘT máy -> chạm ngưỡng leo thang (PROXY_DEVICE_STUDENT_THRESHOLD = 3).
        foreach (range(1, 3) as $i) {
            [$user] = $this->enrolStudentWithRecord($courseClass, $session);
            Livewire::actingAs($user)->test(AttendanceCheckIn::class, ['token' => $session->qr_token])
                ->call('checkIn', null, 'DEV-PROXY-0001')->assertSet('isSuccess', true);
        }

        $notif = \App\Models\Notification::query()
            ->where('notifiable_id', $owner->id)
            ->where('data->title', 'Cảnh báo máy điểm danh hộ')
            ->first();
        $this->assertNotNull($notif);
        $this->assertSame(3, (int) ($notif->data['distinct_students'] ?? 0));
    }

    public function test_impossible_travel_is_flagged(): void
    {
        $base = Carbon::create(2026, 7, 5, 8, 0, 0);
        $this->travelTo($base);

        $owner = User::factory()->create();
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $student = User::factory()->create();
        $member = ClassMember::create([
            'class_id' => $courseClass->id,
            'user_id' => $student->id,
            'status' => ClassMember::STATUS_ACTIVE,
        ]);

        $meeting = ClassMeeting::factory()->create([
            'class_id' => $courseClass->id, 'user_Created' => $owner->id,
            'date' => $base->toDateString(), 'start_time' => '00:00:00', 'end_time' => '23:59:00', 'status' => 'active',
        ]);

        // Lần điểm danh TRƯỚC ở TP.HCM (đã có GPS ghi nhận).
        $prevSession = ClassSession::factory()->create([
            'class_id' => $courseClass->id, 'meeting_id' => $meeting->id, 'created_by' => $owner->id,
            'date' => $base->toDateString(), 'status' => 'active', 'qr_token' => 'IT-PREV',
            'token_expires_at' => $base->copy()->addDay(), 'gps_latitude' => 10.762622, 'gps_longitude' => 106.660172, 'gps_radius' => 500,
        ]);
        AttendanceRecord::factory()->create([
            'class_session_id' => $prevSession->id, 'class_member_id' => $member->id,
            'status' => 'present', 'check_in_time' => $base, 'gps_latitude_recorded' => 10.762622, 'gps_longitude_recorded' => 106.660172,
        ]);

        // Lần điểm danh HIỆN TẠI ở Hà Nội (~1140km) ngay sau đó -> bất khả thi.
        $curSession = ClassSession::factory()->create([
            'class_id' => $courseClass->id, 'meeting_id' => $meeting->id, 'created_by' => $owner->id,
            'date' => $base->toDateString(), 'status' => 'active', 'qr_token' => 'IT-CUR',
            'token_expires_at' => $base->copy()->addDay(), 'gps_latitude' => 21.028511, 'gps_longitude' => 105.804817, 'gps_radius' => 500,
        ]);
        $curRecord = AttendanceRecord::factory()->create([
            'class_session_id' => $curSession->id, 'class_member_id' => $member->id,
            'status' => 'pending', 'check_in_time' => null,
        ]);

        // Cấp check_token GPS hợp lệ tại Hà Nội (trong bán kính của phiên hiện tại).
        \App\Models\GpsVerification::create([
            'session_id' => $curSession->id, 'member_id' => $member->id,
            'token' => 'IT-VERIFY-TOKEN', 'check_token' => 'IT-CHECK-TOKEN',
            'lat' => 21.028511, 'lng' => 105.804817, 'accuracy' => 20,
            'is_used' => false, 'expires_at' => $base->copy()->addMinutes(2),
            'ip_address' => '127.0.0.1',
        ]);

        Livewire::actingAs($student)->test(AttendanceCheckIn::class, ['token' => 'IT-CUR'])
            ->call('checkIn', 'IT-CHECK-TOKEN', 'DEV-IT-0001')
            ->assertSet('isSuccess', true);

        $curRecord->refresh();
        $this->assertSame('impossible_travel', $curRecord->gps_fraud_flag);
        $this->assertStringContainsString('bất khả thi', (string) $curRecord->note);

        $this->travelBack();
    }

    public function test_share_token_link_stays_valid_after_qr_rotation(): void
    {
        $base = Carbon::create(2026, 7, 5, 8, 0, 0);
        $this->travelTo($base);

        $owner = User::factory()->create();
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $meeting = ClassMeeting::factory()->create([
            'class_id' => $courseClass->id, 'user_Created' => $owner->id,
            'date' => $base->toDateString(), 'start_time' => '00:00:00', 'end_time' => '23:59:00', 'status' => 'active',
        ]);
        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id, 'meeting_id' => $meeting->id, 'created_by' => $owner->id,
            'date' => $base->toDateString(), 'status' => 'active',
            'qr_token' => 'ROTATE-OLD-TOKEN', 'token_expires_at' => $base->copy()->addSeconds(15),
            'qr_refresh_rate' => 10, 'gps_latitude' => null, 'gps_longitude' => null, 'gps_radius' => null,
        ]);

        // Phiên mới phải tự có share_token ổn định.
        $shareToken = $session->share_token;
        $this->assertNotEmpty($shareToken);
        $this->assertNotSame('ROTATE-OLD-TOKEN', $shareToken);

        $student = User::factory()->create();
        $member = ClassMember::create([
            'class_id' => $courseClass->id, 'user_id' => $student->id, 'status' => ClassMember::STATUS_ACTIVE,
        ]);
        AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'pending', 'check_in_time' => null,
        ]);

        // Xoay QR nhiều lần -> qr_token cũ biến mất khỏi DB.
        $session->rotateQrToken();
        $session->rotateQrToken();

        // Link theo qr_token CŨ -> không còn hợp lệ (đúng tinh thần chống chụp lại ảnh QR).
        Livewire::actingAs($student)
            ->test(AttendanceCheckIn::class, ['token' => 'ROTATE-OLD-TOKEN'])
            ->assertSet('session', null)
            ->assertSet('isSuccess', false);

        // Link chia sẻ ổn định (share_token) -> VẪN mở & điểm danh được dù QR đã xoay.
        Livewire::actingAs($student)
            ->test(AttendanceCheckIn::class, ['token' => $shareToken])
            ->call('checkIn', null)
            ->assertSet('isSuccess', true);

        $this->assertDatabaseHas('attendance_records', [
            'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'present',
        ]);

        $this->travelBack();
    }

    // Lưu ý: cấu hình GPS mức lớp đã bị loại bỏ khỏi schema (DBML mới);
    // GPS giờ chỉ thiết lập ở mức phiên điểm danh, nên test cũ về GPS mặc định của lớp đã được gỡ.
}
