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

    public function test_static_samples_are_suspicious(): void
    {
        $samples = [['lat' => 10.0, 'lng' => 106.0], ['lat' => 10.0, 'lng' => 106.0], ['lat' => 10.0, 'lng' => 106.0]];
        $r = $this->service()->computeFakeGpsScore(10.0, 106.0, 20.0, ['samples' => $samples, 'altitude' => 5.0], '127.0.0.1');
        $this->assertGreaterThanOrEqual(2, $r['score']);
    }

    public function test_ip_far_from_gps_scores_high(): void
    {
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
}
