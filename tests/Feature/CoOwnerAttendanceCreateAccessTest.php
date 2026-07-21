<?php

namespace Tests\Feature;

use App\Models\ClassMeeting;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Đồng chủ lớp được quyền quản lý điểm danh, nên phải mở được màn hình sửa/nhân bản
 * phiên do CHỦ CHÍNH tạo. Quyền phải xét theo "ai quản lý lớp" (scope managedBy),
 * không phải theo "ai bấm nút tạo phiên" (cột created_by).
 */
class CoOwnerAttendanceCreateAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: User, 2: ClassSession} chủ chính, đồng chủ, phiên do chủ chính tạo */
    private function makeClassWithCoOwner(): array
    {
        $owner = User::factory()->create();
        $coOwner = User::factory()->create();

        $class = CourseClass::factory()->create(['owner_user_id' => $owner->id]);
        $class->coOwners()->attach($coOwner->id, ['role' => 'co_owner', 'accepted_at' => now()]);

        $meeting = ClassMeeting::factory()->create([
            'class_id' => $class->id, 'user_Created' => $owner->id,
            'date' => '2026-07-01', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'status' => 'active',
        ]);

        // Phiên do CHỦ CHÍNH tạo -> created_by = owner, khác id của đồng chủ.
        $session = ClassSession::factory()->create([
            'class_id' => $class->id,
            'meeting_id' => $meeting->id,
            'created_by' => $owner->id,
            'date' => '2026-07-01',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => 'active',
        ]);

        return [$owner, $coOwner, $session];
    }

    public function test_primary_owner_can_open_session_for_edit(): void
    {
        [$owner, , $session] = $this->makeClassWithCoOwner();

        $this->actingAs($owner)
            ->get(route('lecturer.attendance.create', ['session_id' => $session->id]))
            ->assertOk();
    }

    public function test_co_owner_can_open_session_created_by_primary_owner(): void
    {
        [, $coOwner, $session] = $this->makeClassWithCoOwner();

        $this->actingAs($coOwner)
            ->get(route('lecturer.attendance.create', ['session_id' => $session->id]))
            ->assertOk();
    }

    public function test_co_owner_can_clone_session_created_by_primary_owner(): void
    {
        [, $coOwner, $session] = $this->makeClassWithCoOwner();

        $this->actingAs($coOwner)
            ->get(route('lecturer.attendance.create', ['clone_session' => $session->id]))
            ->assertOk();
    }

    public function test_co_owner_can_edit_qr_session_created_by_primary_owner(): void
    {
        [, $coOwner, $session] = $this->makeClassWithCoOwner();

        $this->actingAs($coOwner)
            ->get(route('lecturer.attendance.qr.create', ['edit_session' => $session->id]))
            ->assertOk();
    }

    public function test_co_owner_can_clone_qr_session_created_by_primary_owner(): void
    {
        [, $coOwner, $session] = $this->makeClassWithCoOwner();

        $this->actingAs($coOwner)
            ->get(route('lecturer.attendance.qr.create', ['clone_session' => $session->id]))
            ->assertOk();
    }

    public function test_outsider_is_still_blocked_on_qr_create(): void
    {
        [, , $session] = $this->makeClassWithCoOwner();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('lecturer.attendance.qr.create', ['edit_session' => $session->id]))
            ->assertNotFound();

        $this->actingAs($outsider)
            ->get(route('lecturer.attendance.qr.create', ['clone_session' => $session->id]))
            ->assertNotFound();
    }

    /**
     * Người ngoài lớp vẫn phải bị chặn — nới quyền cho đồng chủ không được hở chỗ này.
     *
     * Mã trả về là 404 chứ không phải 403: ownedSession() lọc theo scope managedBy rồi mới
     * findOrFail, nên phiên của lớp khác coi như không tồn tại. Đây là hành vi ĐỒNG NHẤT với
     * mọi màn điểm danh khác (QrAttendanceSession, ManualAttendanceSession...) và còn kín hơn
     * 403 vì không tiết lộ phiên đó có thật hay không.
     */
    public function test_outsider_is_still_blocked(): void
    {
        [, , $session] = $this->makeClassWithCoOwner();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('lecturer.attendance.create', ['session_id' => $session->id]))
            ->assertNotFound();

        $this->actingAs($outsider)
            ->get(route('lecturer.attendance.create', ['clone_session' => $session->id]))
            ->assertNotFound();
    }
}
