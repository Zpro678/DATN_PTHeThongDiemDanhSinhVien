<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\Attendance\QrAttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Bảng học viên trong phiên QR phải chỉ rõ dấu hiệu bất thường bằng CHỮ dưới tên
 * (không tô nền cả dòng), và nêu đích danh người dùng chung thiết bị ở cột Ghi chú.
 */
class QrSessionFraudFlagsDisplayTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private CourseClass $class;
    private ClassSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->class = CourseClass::factory()->create(['owner_user_id' => $this->owner->id]);

        $meeting = ClassMeeting::factory()->create([
            'class_id' => $this->class->id, 'user_Created' => $this->owner->id,
            'date' => '2026-07-01', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'status' => 'active',
        ]);

        $this->session = ClassSession::factory()->create([
            'class_id' => $this->class->id, 'meeting_id' => $meeting->id, 'created_by' => $this->owner->id,
            'date' => '2026-07-01', 'status' => 'active', 'qr_token' => 'TOKEN1',
            'gps_latitude' => 10.0, 'gps_longitude' => 106.0, 'gps_radius' => 30,
        ]);
    }

    private function addRecord(string $name, array $attributes = []): AttendanceRecord
    {
        $member = ClassMember::create([
            'class_id' => $this->class->id,
            'user_id' => User::factory()->create()->id,
            'status' => ClassMember::STATUS_ACTIVE,
        ]);
        $member->syncProfile(['full_name' => $name, 'email' => strtolower(str_replace(' ', '', $name)).'@example.com']);

        return AttendanceRecord::factory()->create(array_merge([
            'class_session_id' => $this->session->id,
            'class_member_id' => $member->id,
            'status' => 'present',
            'check_in_time' => now(),
        ], $attributes));
    }

    public function test_shared_device_shows_red_label_and_names_the_peer(): void
    {
        $this->addRecord('Nguyễn Văn A', ['device_id' => 'DEVICE-X']);
        $this->addRecord('Trần Thị B', ['device_id' => 'DEVICE-X']);

        Livewire::actingAs($this->owner)->test(QrAttendanceSession::class, ['session' => $this->session->id])
            ->assertOk()
            ->assertSee('Trùng thiết bị')
            // Nêu đích danh người còn lại, không chỉ đếm số.
            ->assertSee('Trùng thiết bị với Trần Thị B')
            ->assertSee('Trùng thiết bị với Nguyễn Văn A');
    }

    /**
     * Cờ trùng thiết bị phải ĐỐI XỨNG: ai dùng chung máy cũng bị gắn, và mỗi người
     * nhìn thấy đúng những người còn lại (không tự liệt kê chính mình).
     */
    public function test_every_member_on_a_shared_device_is_flagged(): void
    {
        $this->addRecord('Người Một', ['device_id' => 'DEVICE-SHARED']);
        $this->addRecord('Người Hai', ['device_id' => 'DEVICE-SHARED']);
        $this->addRecord('Người Ba', ['device_id' => 'DEVICE-SHARED']);

        $html = Livewire::actingAs($this->owner)
            ->test(QrAttendanceSession::class, ['session' => $this->session->id])
            ->assertOk()
            ->html();

        // Cả ba đều phải có dòng cảnh báo, mỗi người nêu đúng hai người kia.
        $this->assertStringContainsString(e('Trùng thiết bị với Người Hai, Người Ba'), $html);
        $this->assertStringContainsString(e('Trùng thiết bị với Người Một, Người Ba'), $html);
        $this->assertStringContainsString(e('Trùng thiết bị với Người Một, Người Hai'), $html);

        // Đúng 3 khối cảnh báo — không sót ai, không thừa.
        $this->assertSame(3, substr_count($html, e('Trùng thiết bị với')));
    }

    /** Nhóm đông dùng chung máy: cắt còn 2 tên để cột không bị giãn. */
    public function test_long_peer_list_is_truncated(): void
    {
        foreach (['An Nguyễn', 'Bình Trần', 'Cường Lê', 'Dũng Phạm', 'Em Võ'] as $name) {
            $this->addRecord($name, ['device_id' => 'DEVICE-CROWD']);
        }

        $html = Livewire::actingAs($this->owner)
            ->test(QrAttendanceSession::class, ['session' => $this->session->id])
            ->assertOk()
            ->html();

        // Mỗi người thấy 2 tên + phần dư gom lại (5 người -> 4 người khác -> 2 tên + 2 người nữa).
        $this->assertStringContainsString(e('và 2 người nữa'), $html);
        $this->assertSame(5, substr_count($html, e('và 2 người nữa')));
    }

    public function test_lone_device_is_not_flagged(): void
    {
        $this->addRecord('Một Mình', ['device_id' => 'DEVICE-SOLO']);

        Livewire::actingAs($this->owner)->test(QrAttendanceSession::class, ['session' => $this->session->id])
            ->assertOk()
            ->assertDontSee('Trùng thiết bị');
    }

    public function test_out_of_radius_shows_amber_text_without_row_background(): void
    {
        // Cách tâm 95m, bán kính 30m -> vượt 65m.
        $this->addRecord('Xa Điểm Danh', [
            'device_id' => 'DEVICE-Y',
            'gps_fraud_flag' => 'out_of_radius',
            'distance_meters' => 95,
        ]);

        Livewire::actingAs($this->owner)->test(QrAttendanceSession::class, ['session' => $this->session->id])
            ->assertOk()
            ->assertSee('Ngoài bán kính +65m')
            // Nền vàng CẢ DÒNG đã bị bỏ (bg-amber-50 vẫn còn ở icon thẻ thống kê phía trên,
            // nên phải kiểm đúng cặp class của <tr> cũ chứ không kiểm chung chung).
            ->assertDontSee('bg-amber-50 hover:bg-amber-100/70')
            // Và chip nền vàng dưới tên cũng đã thay bằng chữ thuần.
            ->assertDontSee('bg-amber-100 px-1.5');
    }

    public function test_both_flags_are_shown_together(): void
    {
        $this->addRecord('Vi Phạm Kép', [
            'device_id' => 'DEVICE-Z',
            'gps_fraud_flag' => 'out_of_radius',
            'distance_meters' => 80,
        ]);
        $this->addRecord('Bạn Cùng Máy', ['device_id' => 'DEVICE-Z']);

        Livewire::actingAs($this->owner)->test(QrAttendanceSession::class, ['session' => $this->session->id])
            ->assertOk()
            ->assertSee('Trùng thiết bị')
            ->assertSee('Ngoài bán kính +50m')
            ->assertSee('Trùng thiết bị với Bạn Cùng Máy');
    }
}
