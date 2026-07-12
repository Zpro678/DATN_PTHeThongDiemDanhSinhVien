<?php

namespace Tests\Feature;

use App\Exports\StudentsSheet;
use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 2 cột cuối của báo cáo chuyên cần (StudentsSheet):
 *  - "Phần trăm vắng không phép trên tổng buổi dự kiến" = vắng KP / tổng buổi dự kiến × 100.
 *  - "Điểm chuyên cần (/10)" = 10 − (muộn×deduct_late + vắng×deduct_absent + có phép×deduct_excused).
 */
class StudentsSheetAttendanceColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_attendance_columns_follow_class_deduction_config(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        // Lớp: 10 buổi dự kiến; điểm trừ muộn 0.5, vắng 2.0, có phép 0.
        $class = CourseClass::factory()->create([
            'owner_user_id' => $owner->id,
            'total_sessions' => 10,
            'deduct_late' => 0.5, 'deduct_absent' => 2.0, 'deduct_excused' => 0.0,
        ]);

        $member = ClassMember::factory()->create(['class_id' => $class->id]);

        // 2 buổi đã chốt: 1 vắng + 1 đi muộn.
        foreach ([['absent'], ['late']] as $i => $st) {
            $meeting = ClassMeeting::factory()->create([
                'class_id' => $class->id, 'user_Created' => $owner->id, 'status' => 'closed',
            ]);
            $session = ClassSession::factory()->create([
                'class_id' => $class->id, 'meeting_id' => $meeting->id, 'created_by' => $owner->id, 'status' => 'closed',
            ]);
            AttendanceRecord::factory()->create([
                'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => $st[0],
            ]);
        }

        $rows = (new StudentsSheet($owner->id, (string) $class->id, 'active', '', ''))->array();

        // Dòng dữ liệu SV đầu tiên nằm ở index 6 (sau 5 dòng header + dòng tiêu đề cột).
        $dataRow = $rows[6];
        // Thứ tự cột cuối: [Tổng số buổi, Có mặt, Đi muộn, Vắng, Có phép, Tổng điểm trừ, %có mặt, Điểm chuyên cần]
        $tongDiemTru    = $dataRow[count($dataRow) - 3];
        $percentPresent = $dataRow[count($dataRow) - 2];
        $score          = $dataRow[count($dataRow) - 1];

        // Tổng điểm trừ = 1 muộn×0.5 + 1 vắng×2.0 = 2.5.
        $this->assertEquals(2.5, $tongDiemTru);
        // Điểm chuyên cần = 10 − 2.5 = 7.5.
        $this->assertEquals(7.5, $score);
        // % có mặt trong lớp = 100% − (1 vắng / 10 buổi) = 90%.
        $this->assertSame('90%', $percentPresent);
    }
}
