<?php

namespace Tests\Feature;

use App\Livewire\Student\AttendanceCheckIn;
use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\GpsVerification;
use App\Models\Notification;
use App\Models\User;
use App\Services\GpsValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class GpsFakeDetectionTest extends TestCase
{
    use RefreshDatabase;

    private function service(): GpsValidationService
    {
        return app(GpsValidationService::class);
    }

    public function test_perfect_accuracy_is_suspicious(): void
    {
        // IP nội bộ -> không có tín hiệu IP; chỉ accuracy quá đẹp -> đủ ngưỡng.
        $r = $this->service()->computeFakeGpsScore(10.0, 106.0, 0.5, ['altitude' => 5.0], '127.0.0.1');
        $this->assertGreaterThanOrEqual(GpsValidationService::FAKE_GPS_SUSPICION_THRESHOLD, $r['score']);
    }

    public function test_static_samples_alone_are_not_flagged(): void
    {
        // Điện thoại THẬT đứng yên (định vị WiFi/cell, phổ biến với iPhone trong nhà) cho toạ độ
        // TRÙNG nhau nhưng accuracy hợp lý + có độ cao. Chỉ mình tín hiệu "toạ độ tĩnh" KHÔNG được
        // vượt ngưỡng -> tránh dán nhãn "Sai GPS" oan cho sinh viên có mặt thật.
        $samples = [['lat' => 10.0, 'lng' => 106.0], ['lat' => 10.0, 'lng' => 106.0], ['lat' => 10.0, 'lng' => 106.0]];
        $r = $this->service()->computeFakeGpsScore(10.0, 106.0, 20.0, ['samples' => $samples, 'altitude' => 5.0], '127.0.0.1');
        $this->assertLessThan(GpsValidationService::FAKE_GPS_SUSPICION_THRESHOLD, $r['score']);
    }

    public function test_static_samples_with_missing_altitude_stay_below_threshold(): void
    {
        // Cả hai tín hiệu YẾU cùng lúc (toạ độ tĩnh + thiếu độ cao) — đều do máy thật đứng yên trên
        // WiFi sinh ra — gộp lại vẫn chỉ 1 điểm, KHÔNG tự vượt ngưỡng.
        $samples = [['lat' => 10.0, 'lng' => 106.0], ['lat' => 10.0, 'lng' => 106.0], ['lat' => 10.0, 'lng' => 106.0]];
        $r = $this->service()->computeFakeGpsScore(10.0, 106.0, 20.0, ['samples' => $samples, 'altitude' => null], '127.0.0.1');
        $this->assertLessThan(GpsValidationService::FAKE_GPS_SUSPICION_THRESHOLD, $r['score']);
    }

    public function test_static_samples_corroborate_a_strong_signal(): void
    {
        // Khi ĐÃ có tín hiệu MẠNH (độ chính xác bất thường ≤1m), toạ độ tĩnh cộng thêm điểm -> nghi ngờ.
        $samples = [['lat' => 10.0, 'lng' => 106.0], ['lat' => 10.0, 'lng' => 106.0], ['lat' => 10.0, 'lng' => 106.0]];
        $r = $this->service()->computeFakeGpsScore(10.0, 106.0, 0.5, ['samples' => $samples, 'altitude' => 5.0], '127.0.0.1');
        $this->assertGreaterThanOrEqual(GpsValidationService::FAKE_GPS_SUSPICION_THRESHOLD, $r['score']);
    }

    public function test_ip_far_from_gps_scores_high(): void
    {
        config(['attendance.gps_ip_check' => true]); // chấm IP chỉ chạy khi đã tin proxy.
        // IP nói Hà Nội, GPS nói TP.HCM (~1140km) -> lệch lớn.
        Http::fake(['ip-api.com/*' => Http::response(['status' => 'success', 'lat' => 21.028511, 'lon' => 105.804817], 200)]);

        $r = $this->service()->computeFakeGpsScore(10.762622, 106.660172, 20.0, ['altitude' => 5.0], '8.8.8.8');

        $this->assertGreaterThanOrEqual(3, $r['score']);
        $this->assertStringContainsString('IP', implode('; ', $r['reasons']));
    }

    public function test_real_looking_gps_is_not_flagged(): void
    {
        // accuracy hợp lý + toạ độ có rung + có độ cao + IP nội bộ -> dưới ngưỡng.
        $samples = [['lat' => 10.762620, 'lng' => 106.660170], ['lat' => 10.762625, 'lng' => 106.660176]];
        $r = $this->service()->computeFakeGpsScore(10.762622, 106.660172, 18.0, ['samples' => $samples, 'altitude' => 7.5], '127.0.0.1');
        $this->assertLessThan(GpsValidationService::FAKE_GPS_SUSPICION_THRESHOLD, $r['score']);
    }

    public function test_checkin_flags_suspected_mock_from_verification_score(): void
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
            'date' => $base->toDateString(), 'status' => 'active', 'qr_token' => 'FAKE-CUR',
            'token_expires_at' => $base->copy()->addDay(),
            'gps_latitude' => 10.762622, 'gps_longitude' => 106.660172, 'gps_radius' => 500,
        ]);
        $student = User::factory()->create();
        $member = ClassMember::create([
            'class_id' => $courseClass->id, 'user_id' => $student->id, 'status' => ClassMember::STATUS_ACTIVE,
        ]);
        $record = AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'pending', 'check_in_time' => null,
        ]);

        // check_token hợp lệ, ở trong bán kính, nhưng đã bị chấm điểm nghi vấn ở bước verify.
        GpsVerification::create([
            'session_id' => $session->id, 'member_id' => $member->id,
            'token' => 'FVT', 'check_token' => 'FCT',
            'lat' => 10.762622, 'lng' => 106.660172, 'accuracy' => 0.5,
            'fraud_score' => 2, 'fraud_reasons' => 'độ chính xác bất thường (0.5m)',
            'is_used' => false, 'expires_at' => $base->copy()->addMinutes(2), 'ip_address' => '127.0.0.1',
        ]);

        Livewire::actingAs($student)->test(AttendanceCheckIn::class, ['token' => 'FAKE-CUR'])
            ->call('checkIn', 'FCT', 'DEV-FAKE-0001')
            ->assertSet('isSuccess', true);

        $record->refresh();
        $this->assertSame('suspected_mock', $record->gps_fraud_flag);
        $this->assertStringContainsString('giả lập vị trí', (string) $record->note);

        $this->travelBack();
    }

    public function test_vpn_proxy_is_flagged_and_skips_ip_distance(): void
    {
        config(['attendance.gps_ip_check' => true]); // chấm IP chỉ chạy khi đã tin proxy.
        // IP báo proxy=true dù toạ độ IP TRÙNG GPS: vẫn phải gắn cờ VPN (không so khoảng cách IP nữa).
        Http::fake(['ip-api.com/*' => Http::response([
            'status' => 'success', 'lat' => 10.762622, 'lon' => 106.660172,
            'proxy' => true, 'hosting' => false, 'mobile' => false,
        ], 200)]);

        $r = $this->service()->computeFakeGpsScore(10.762622, 106.660172, 20.0, ['altitude' => 5.0], '8.8.8.8');

        $this->assertTrue($r['vpn']);
        $this->assertGreaterThanOrEqual(GpsValidationService::FAKE_GPS_SUSPICION_THRESHOLD, $r['score']);
        $this->assertStringContainsString('VPN', implode('; ', $r['reasons']));
    }

    public function test_ip_check_disabled_by_default_never_flags_vpn(): void
    {
        // Mặc định (chưa khai báo TRUSTED_PROXIES) cổng chấm IP TẮT: dù IP báo proxy hay lệch xa,
        // KHÔNG được gắn cờ -> an toàn tuyệt đối, không báo nhầm khi chạy sau proxy chưa cấu hình.
        config(['attendance.gps_ip_check' => false]);
        Http::fake(['ip-api.com/*' => Http::response([
            'status' => 'success', 'lat' => 21.028511, 'lon' => 105.804817,
            'proxy' => true, 'hosting' => true, 'mobile' => false,
        ], 200)]);

        $r = $this->service()->computeFakeGpsScore(10.762622, 106.660172, 20.0, ['altitude' => 5.0], '8.8.8.8');

        $this->assertFalse($r['vpn']);
        $this->assertLessThan(GpsValidationService::FAKE_GPS_SUSPICION_THRESHOLD, $r['score']);
    }

    public function test_within_accuracy_margin_is_not_out_of_radius(): void
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
            'date' => $base->toDateString(), 'status' => 'active', 'qr_token' => 'ACC-CUR',
            'token_expires_at' => $base->copy()->addDay(),
            'gps_latitude' => 10.762622, 'gps_longitude' => 106.660172, 'gps_radius' => 50,
        ]);
        $student = User::factory()->create();
        $member = ClassMember::create([
            'class_id' => $courseClass->id, 'user_id' => $student->id, 'status' => ClassMember::STATUS_ACTIVE,
        ]);
        $record = AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'pending', 'check_in_time' => null,
        ]);

        // Cách lớp ~78m (lat +0.0007) nhưng accuracy 40m -> khoảng cách hiệu dụng ~38m < bán kính 50m
        // => KHÔNG bị coi là ngoài bán kính (sửa lỗi "ở gần mà báo xa").
        GpsVerification::create([
            'session_id' => $session->id, 'member_id' => $member->id,
            'token' => 'ACCT1', 'check_token' => 'ACCC1',
            'lat' => 10.763322, 'lng' => 106.660172, 'accuracy' => 40.0,
            'fraud_score' => 0, 'fraud_reasons' => '',
            'is_used' => false, 'expires_at' => $base->copy()->addMinutes(2), 'ip_address' => '127.0.0.1',
        ]);

        Livewire::actingAs($student)->test(AttendanceCheckIn::class, ['token' => 'ACC-CUR'])
            ->call('checkIn', 'ACCC1', 'DEV-ACC-0001')
            ->assertSet('isSuccess', true)
            ->assertSet('isOutOfRadius', false);

        $record->refresh();
        $this->assertSame('present', $record->status);
        $this->assertNull($record->gps_fraud_flag, 'Trong biên độ sai số thì không gắn cờ ngoài bán kính.');

        $this->travelBack();
    }

    public function test_hair_width_excess_is_not_reported_as_out_of_radius(): void
    {
        // Bán kính 50m, cách lớp ~75m, sai số ±25m -> khoảng cách hiệu dụng ~50m, tức SÁT MÉP.
        // Trước đây bị gắn cờ với số mét vượt làm tròn thành 0 ("vượt 0m" — vô nghĩa với sinh viên);
        // nay biên nhiễu nuốt phần dư này nên coi như vẫn ở TRONG bán kính.
        $this->assertNull(GpsValidationService::metersOutsideRadius(75.0, 25.0, 50));
        $this->assertNull(GpsValidationService::metersOutsideRadius(80.0, 25.0, 50));

        // Ra xa thật thì vẫn báo, và số mét vượt luôn > 0.
        $this->assertSame(30, GpsValidationService::metersOutsideRadius(105.0, 25.0, 50));
    }

    public function test_vpn_checkin_is_blocked_and_nothing_is_recorded(): void
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
            'date' => $base->toDateString(), 'status' => 'active', 'qr_token' => 'VPN-CUR',
            'token_expires_at' => $base->copy()->addDay(),
            'gps_latitude' => 10.762622, 'gps_longitude' => 106.660172, 'gps_radius' => 500,
        ]);
        $student = User::factory()->create();
        $member = ClassMember::create([
            'class_id' => $courseClass->id, 'user_id' => $student->id, 'status' => ClassMember::STATUS_ACTIVE,
        ]);
        $record = AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'pending', 'check_in_time' => null,
        ]);

        // Ở TRONG bán kính (toạ độ trùng lớp) nhưng vé xác thực mang dấu VPN/proxy (vé cũ cấp trước
        // khi bật chặn) -> bước check-in phải TỪ CHỐI, không được lưu bất cứ thứ gì.
        GpsVerification::create([
            'session_id' => $session->id, 'member_id' => $member->id,
            'token' => 'VPNT1', 'check_token' => 'VPNC1',
            'lat' => 10.762622, 'lng' => 106.660172, 'accuracy' => 20.0,
            'fraud_score' => 3, 'fraud_reasons' => 'kết nối qua VPN/proxy (vị trí mạng bị che giấu)',
            'is_used' => false, 'expires_at' => $base->copy()->addMinutes(2), 'ip_address' => '127.0.0.1',
        ]);

        Livewire::actingAs($student)->test(AttendanceCheckIn::class, ['token' => 'VPN-CUR'])
            ->call('checkIn', 'VPNC1', 'DEV-VPN-0001')
            ->assertSet('isSuccess', false)
            ->assertSet('isGpsError', true)
            // Sinh viên được báo rõ: bị từ chối, chưa ghi nhận, hãy tắt VPN rồi thử lại.
            ->assertSee('TỪ CHỐI')
            ->assertSee('VPN/proxy');

        $record->refresh();
        $this->assertSame('pending', $record->status, 'Dùng VPN thì KHÔNG được ghi nhận có mặt.');
        $this->assertNull($record->check_in_time);
        $this->assertNull($record->gps_fraud_flag);

        // Vẫn để lại dấu vết ở nhật ký quét để giảng viên rà soát lần thử bị chặn.
        $this->assertDatabaseHas('check_in_scans', [
            'class_session_id' => $session->id,
            'is_valid' => false,
            'fail_reason' => 'vpn_blocked',
        ]);

        $this->travelBack();
    }

    public function test_verify_blocks_and_issues_no_check_token_when_behind_vpn(): void
    {
        // Chặn ngay từ bước xác minh vị trí: không cấp check_token -> bước check-in không thể lưu.
        config(['attendance.gps_ip_check' => true]);
        Http::fake(['ip-api.com/*' => Http::response([
            'status' => 'success', 'lat' => 10.762622, 'lon' => 106.660172,
            'proxy' => true, 'hosting' => false, 'mobile' => false,
        ], 200)]);

        [$session, $member] = $this->makeSessionAndMember();
        $verification = $this->service()->issueVerificationToken($session, $member, '8.8.8.8');

        $result = $this->service()->verifyLocation(
            $verification->token, 10.762622, 106.660172, 20.0, '8.8.8.8', ['altitude' => 5.0]
        );

        $this->assertFalse($result['success']);
        $this->assertSame('vpn', $result['blocked']);
        $this->assertNull($result['check_token']);
        $this->assertStringContainsString('VPN/proxy', $result['error']);

        $verification->refresh();
        $this->assertNull($verification->check_token, 'Không được cấp check_token khi đang dùng VPN.');
        $this->assertStringContainsString('VPN/proxy', (string) $verification->fraud_reasons);
    }

    public function test_device_duplicate_note_names_both_students(): void
    {
        $base = Carbon::create(2026, 7, 5, 8, 0, 0);
        $this->travelTo($base);

        $owner = User::factory()->create();
        $courseClass = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $meeting = ClassMeeting::factory()->create([
            'class_id' => $courseClass->id, 'user_Created' => $owner->id,
            'date' => $base->toDateString(), 'start_time' => '00:00:00', 'end_time' => '23:59:00', 'status' => 'active',
        ]);
        // Buổi KHÔNG yêu cầu GPS -> bỏ qua khối GPS, chỉ còn kiểm tra trùng thiết bị (device_check mặc định bật).
        $session = ClassSession::factory()->create([
            'class_id' => $courseClass->id, 'meeting_id' => $meeting->id, 'created_by' => $owner->id,
            'date' => $base->toDateString(), 'status' => 'active', 'qr_token' => 'DUP-CUR',
            'token_expires_at' => $base->copy()->addDay(),
            'gps_latitude' => null, 'gps_longitude' => null, 'gps_radius' => null,
        ]);

        $studentA = User::factory()->create(['name' => 'Nguyễn Văn A']);
        $studentB = User::factory()->create(['name' => 'Trần Thị B']);
        $memberA = ClassMember::create(['class_id' => $courseClass->id, 'user_id' => $studentA->id, 'status' => ClassMember::STATUS_ACTIVE]);
        $memberB = ClassMember::create(['class_id' => $courseClass->id, 'user_id' => $studentB->id, 'status' => ClassMember::STATUS_ACTIVE]);
        $recordA = AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $memberA->id, 'status' => 'pending', 'check_in_time' => null,
        ]);
        $recordB = AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $memberB->id, 'status' => 'pending', 'check_in_time' => null,
        ]);

        // A điểm danh trước bằng thiết bị DEV-SAME.
        Livewire::actingAs($studentA)->test(AttendanceCheckIn::class, ['token' => 'DUP-CUR'])
            ->call('checkIn', null, 'DEV-SAME-0001')
            ->assertSet('isSuccess', true);

        // B điểm danh sau CŨNG bằng DEV-SAME -> bị phát hiện trùng thiết bị với A.
        Livewire::actingAs($studentB)->test(AttendanceCheckIn::class, ['token' => 'DUP-CUR'])
            ->call('checkIn', null, 'DEV-SAME-0001')
            ->assertSet('isSuccess', true);

        $recordA->refresh();
        $recordB->refresh();

        // Ghi chú của CẢ HAI bản ghi nêu rõ trùng với AI, và đều bị gắn cờ device_duplicate.
        $this->assertSame('device_duplicate', $recordB->gps_fraud_flag);
        $this->assertStringContainsString('Trùng thiết bị với Nguyễn Văn A', (string) $recordB->note);
        $this->assertSame('device_duplicate', $recordA->gps_fraud_flag);
        $this->assertStringContainsString('Trùng thiết bị với Trần Thị B', (string) $recordA->note);

        $this->travelBack();
    }

    public function test_out_of_radius_still_records_present_but_flags_warning(): void
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
            'date' => $base->toDateString(), 'status' => 'active', 'qr_token' => 'OOR-CUR',
            'token_expires_at' => $base->copy()->addDay(),
            'gps_latitude' => 10.762622, 'gps_longitude' => 106.660172, 'gps_radius' => 500,
        ]);
        $student = User::factory()->create();
        $member = ClassMember::create([
            'class_id' => $courseClass->id, 'user_id' => $student->id, 'status' => ClassMember::STATUS_ACTIVE,
        ]);
        $record = AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'pending', 'check_in_time' => null,
        ]);

        // Vị trí ở Hà Nội (~1140km) -> ngoài bán kính 500m, nhưng NAY VẪN cho điểm danh.
        GpsVerification::create([
            'session_id' => $session->id, 'member_id' => $member->id,
            'token' => 'OORT1', 'check_token' => 'OORC1',
            'lat' => 21.028511, 'lng' => 105.804817, 'accuracy' => 20.0,
            'fraud_score' => 0, 'fraud_reasons' => '',
            'is_used' => false, 'expires_at' => $base->copy()->addMinutes(2), 'ip_address' => '127.0.0.1',
        ]);

        Livewire::actingAs($student)->test(AttendanceCheckIn::class, ['token' => 'OOR-CUR'])
            ->call('checkIn', 'OORC1', 'DEV-OOR-0001')
            ->assertSet('isSuccess', true)
            // Sinh viên được CẢNH BÁO ngay trên màn hình rằng đã điểm danh ngoài bán kính.
            ->assertSet('isOutOfRadius', true)
            ->assertSet('metersOutside', fn ($m) => $m > 0)
            ->assertSee('NGOÀI bán kính');

        $record->refresh();
        // Vẫn được ghi nhận CÓ MẶT kèm giờ điểm danh, chỉ GẮN CỜ VÀNG để chủ lớp rà soát.
        $this->assertSame('present', $record->status);
        $this->assertNotNull($record->check_in_time, 'Ngoài bán kính nay VẪN ghi giờ điểm danh.');
        $this->assertSame('out_of_radius', $record->gps_fraud_flag);
        $this->assertNotNull($record->distance_meters);
        $this->assertGreaterThan($session->gps_radius, $record->distance_meters);
        $this->assertStringContainsString('Ngoài bán kính', (string) $record->note);

        $this->travelBack();
    }

    public function test_out_of_radius_notifies_lecturer_with_meters_outside(): void
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
            'date' => $base->toDateString(), 'status' => 'active', 'qr_token' => 'OORN-CUR',
            'token_expires_at' => $base->copy()->addDay(),
            'gps_latitude' => 10.762622, 'gps_longitude' => 106.660172, 'gps_radius' => 500,
        ]);
        $student = User::factory()->create();
        $member = ClassMember::create([
            'class_id' => $courseClass->id, 'user_id' => $student->id, 'status' => ClassMember::STATUS_ACTIVE,
        ]);
        AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'pending', 'check_in_time' => null,
        ]);

        GpsVerification::create([
            'session_id' => $session->id, 'member_id' => $member->id,
            'token' => 'OORNT0', 'check_token' => 'OORNC1',
            'lat' => 21.028511, 'lng' => 105.804817, 'accuracy' => 20.0,
            'fraud_score' => 0, 'fraud_reasons' => '',
            'is_used' => false, 'expires_at' => $base->copy()->addMinutes(5), 'ip_address' => '127.0.0.1',
        ]);

        Livewire::actingAs($student)->test(AttendanceCheckIn::class, ['token' => 'OORN-CUR'])
            ->call('checkIn', 'OORNC1', 'DEV-OORN-0001')
            ->assertSet('isSuccess', true);

        // CHỦ LỚP nhận đúng 1 cảnh báo "ngoài bán kính" kèm số mét vượt; sinh viên KHÔNG bị làm phiền.
        $ownerNotification = Notification::where('notifiable_id', $owner->id)
            ->where('type', 'App\\Notifications\\GpsOutOfRadiusWarning')
            ->first();
        $this->assertNotNull($ownerNotification, 'Chủ lớp phải nhận cảnh báo điểm danh ngoài bán kính.');
        $this->assertGreaterThan(0, (int) ($ownerNotification->data['meters_outside'] ?? 0));
        $this->assertSame('warning', $ownerNotification->data['level'] ?? null);

        $this->assertSame(0, Notification::where('notifiable_id', $student->id)->count(),
            'Ngoài bán kính chỉ cảnh báo chủ lớp, không làm phiền sinh viên.');

        $this->travelBack();
    }

    public function test_verify_returns_mock_warning_when_score_over_threshold(): void
    {
        // accuracy quá đẹp (<=1m) -> đủ ngưỡng nghi giả lập -> client phải nhận cảnh báo "mock"
        // để hiện hộp thoại yêu cầu TẮT app giả lập vị trí trước khi điểm danh.
        [$session, $member] = $this->makeSessionAndMember();
        $verification = $this->service()->issueVerificationToken($session, $member, "127.0.0.1");

        $result = $this->service()->verifyLocation(
            $verification->token, 10.762622, 106.660172, 0.5, "127.0.0.1", ["altitude" => 5.0]
        );

        $this->assertTrue($result["success"]);
        $this->assertContains("mock", $result["warnings"]);
        $this->assertNotContains("vpn", $result["warnings"]);
    }

    public function test_verify_returns_no_warning_for_normal_signal(): void
    {
        // Tín hiệu bình thường -> không hộp thoại nào, điểm danh trôi thẳng.
        [$session, $member] = $this->makeSessionAndMember();
        $verification = $this->service()->issueVerificationToken($session, $member, "127.0.0.1");

        $result = $this->service()->verifyLocation(
            $verification->token, 10.762622, 106.660172, 12.0, "127.0.0.1",
            ["altitude" => 5.0, "samples" => [["lat" => 10.762622, "lng" => 106.660172], ["lat" => 10.762700, "lng" => 106.660200]]]
        );

        $this->assertTrue($result["success"]);
        $this->assertSame([], $result["warnings"]);
    }

    /** @return array{0: ClassSession, 1: ClassMember} */
    private function makeSessionAndMember(): array
    {
        $owner = User::factory()->create();
        $courseClass = CourseClass::factory()->create(["owner_user_id" => $owner->id]);
        $meeting = ClassMeeting::factory()->create([
            "class_id" => $courseClass->id, "user_Created" => $owner->id,
            "date" => now()->toDateString(), "start_time" => "00:00:00", "end_time" => "23:59:00", "status" => "active",
        ]);
        $session = ClassSession::factory()->create([
            "class_id" => $courseClass->id, "meeting_id" => $meeting->id, "created_by" => $owner->id,
            "date" => now()->toDateString(), "status" => "active",
            "gps_latitude" => 10.762622, "gps_longitude" => 106.660172, "gps_radius" => 500,
        ]);
        $student = User::factory()->create();
        $member = ClassMember::create([
            "class_id" => $courseClass->id, "user_id" => $student->id, "status" => ClassMember::STATUS_ACTIVE,
        ]);

        return [$session, $member];
    }
}