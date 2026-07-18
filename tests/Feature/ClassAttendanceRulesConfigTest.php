<?php

namespace Tests\Feature;

use App\Livewire\Lecturer\ClassSettings;
use App\Models\CourseClass;
use App\Models\User;
use App\Services\AttendanceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Điểm trừ chuyên cần nay cấu hình theo LỚP (deduct_late/absent/excused) và công thức %
 * phải dựa vào đó — không còn dùng giá trị cố định.
 */
class ClassAttendanceRulesConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_attendance_rules_reads_stored_values(): void
    {
        $class = CourseClass::factory()->create([
            'deduct_late' => 0.25, 'deduct_absent' => 2.0, 'deduct_excused' => 0.5,
        ]);

        $rules = $class->getAttendanceRules();
        $this->assertSame(0.0, $rules['present']);
        $this->assertSame(0.25, $rules['late']);
        $this->assertSame(2.0, $rules['absent']);
        $this->assertSame(0.5, $rules['excused']);
    }

    public function test_percent_reflects_custom_absent_deduction(): void
    {
        // 10 buổi dự kiến, 1 buổi vắng, có phép không trừ.
        $counts = ['present' => 0, 'late' => 0, 'absent' => 1, 'excused' => 0, 'total' => 1];

        $default = CourseClass::factory()->create(['deduct_absent' => 1.0, 'deduct_excused' => 0.0]);
        $harsh   = CourseClass::factory()->create(['deduct_absent' => 2.0, 'deduct_excused' => 0.0]);

        // Mặc định: mất 1 buổi/10 = 90%. Trừ nặng gấp đôi: mất 2/10 = 80%.
        $this->assertSame(90, AttendanceCalculator::percentOfPlanned(10, $counts, $default->getAttendanceRules()));
        $this->assertSame(80, AttendanceCalculator::percentOfPlanned(10, $counts, $harsh->getAttendanceRules()));
    }

    public function test_excused_deduction_shrinks_denominator(): void
    {
        // 10 dự kiến, 2 buổi vắng có phép. excused>0 -> mẫu số = 10-2 = 8.
        $counts = ['present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 2, 'total' => 2];

        $noExcuse  = CourseClass::factory()->create(['deduct_excused' => 0.0]); // có phép không ảnh hưởng
        $withExcuse = CourseClass::factory()->create(['deduct_excused' => 1.0]); // có phép làm giảm mẫu số

        $this->assertSame(100, AttendanceCalculator::percentOfPlanned(10, $counts, $noExcuse->getAttendanceRules()));
        // mẫu số 8, không mất buổi nào khác -> vẫn 100% (chỉ mẫu số nhỏ lại, không trừ điểm có phép ở tử số).
        $this->assertSame(100, AttendanceCalculator::percentOfPlanned(10, $counts, $withExcuse->getAttendanceRules()));
    }

    public function test_class_settings_persists_deduction_config(): void
    {
        $owner = User::factory()->create();
        $class = CourseClass::factory()->create(['owner_user_id' => $owner->id]);

        Livewire::actingAs($owner)->test(ClassSettings::class, ['courseClass' => $class])
            ->set('attendanceRules.late', 0.5)
            ->set('attendanceRules.absent', 1.5)
            ->set('attendanceRules.excused', 1.0)
            ->call('save')
            ->assertHasNoErrors();

        $class->refresh();
        $this->assertSame(0.5, (float) $class->deduct_late);
        $this->assertSame(1.5, (float) $class->deduct_absent);
        $this->assertSame(1.0, (float) $class->deduct_excused);
        // Mirror giữ cho các truy vấn cũ: excused>0 -> deduct_excused_absence = true.
        $this->assertTrue((bool) $class->deduct_excused_absence);
    }

    public function test_absence_thresholds_default_to_twenty_percent(): void
    {
        $class = CourseClass::factory()->create();
        $thresholds = $class->getAttendanceThresholds();

        $this->assertSame(20.0, $thresholds['absence_limit_percent']);
        // Ngưỡng cấm thi luôn suy ra = 100 - quỹ vắng, không có cột riêng.
        $this->assertSame(80.0, $thresholds['min_attendance_percent']);
        $this->assertSame(85.0, $thresholds['warning_percent']);
        $this->assertSame(2, $thresholds['near_absence_sessions']);
    }

    public function test_allowed_absent_sessions_follows_class_config(): void
    {
        // Mặc định 20% của 15 buổi = 3 buổi.
        $this->assertSame(3, AttendanceCalculator::allowedAbsentSessions(15));

        // Lớp cấu hình 40% -> 6 buổi; ngưỡng cấm thi kéo theo còn 60%.
        $loose = CourseClass::factory()->create(['absence_limit_percent' => 40]);
        $this->assertSame(6, AttendanceCalculator::allowedAbsentSessions(15, $loose->getAttendanceThresholds()['absence_limit_percent']));
        $this->assertSame(60.0, $loose->getMinAttendancePercent());

        // Lớp siết chặt 10% -> chỉ 1 buổi (floor 1.5).
        $strict = CourseClass::factory()->create(['absence_limit_percent' => 10]);
        $this->assertSame(1, AttendanceCalculator::allowedAbsentSessions(15, $strict->getAttendanceThresholds()['absence_limit_percent']));
        $this->assertSame(90.0, $strict->getMinAttendancePercent());
    }

    public function test_class_settings_persists_threshold_config(): void
    {
        $owner = User::factory()->create();
        $class = CourseClass::factory()->create(['owner_user_id' => $owner->id]);

        Livewire::actingAs($owner)->test(ClassSettings::class, ['courseClass' => $class])
            ->set('absenceLimitPercent', 30)
            ->set('warningMarginPercent', 10)
            ->set('nearAbsenceSessions', 4)
            ->call('save')
            ->assertHasNoErrors();

        $class->refresh();
        $thresholds = $class->getAttendanceThresholds();

        $this->assertSame(30.0, $thresholds['absence_limit_percent']);
        $this->assertSame(70.0, $thresholds['min_attendance_percent']);
        $this->assertSame(80.0, $thresholds['warning_percent']);
        $this->assertSame(4, $thresholds['near_absence_sessions']);
    }

    public function test_settings_page_previews_allowed_sessions_live(): void
    {
        $owner = User::factory()->create();
        $class = CourseClass::factory()->create(['owner_user_id' => $owner->id, 'total_sessions' => 20]);

        Livewire::actingAs($owner)->test(ClassSettings::class, ['courseClass' => $class])
            // Mặc định 20% của 20 buổi = 4 buổi, chuyên cần tối thiểu 80%.
            ->assertSee('4 buổi')
            ->assertSee('80%')
            // Đổi sang 35% -> 7 buổi, tối thiểu 65% (tính lại ngay, không cần lưu).
            ->set('absenceLimitPercent', 35)
            ->assertSee('7 buổi')
            ->assertSee('65%');
    }

    /**
     * Bẫy đã dính một lần: các eager-load dạng 'courseClass:id,name,...' chỉ nạp cột liệt kê,
     * thiếu cột ngưỡng thì getAttendanceThresholds() đọc null và âm thầm trả về mặc định 20%/80%
     * — cảnh báo sẽ sai mà không có lỗi nào được ném ra.
     */
    public function test_threshold_survives_column_limited_eager_load(): void
    {
        $class = CourseClass::factory()->create(['absence_limit_percent' => 35]);

        $reloaded = CourseClass::query()
            ->select(['id', 'name', 'total_sessions', 'absence_limit_percent', 'warning_margin_percent', 'near_absence_sessions'])
            ->find($class->id);

        $this->assertSame(35.0, $reloaded->getAttendanceThresholds()['absence_limit_percent']);
        $this->assertSame(65.0, $reloaded->getMinAttendancePercent());
    }

    public function test_student_warnings_use_class_threshold_not_default(): void
    {
        $owner = User::factory()->create();
        // Quỹ vắng 50% -> chuyên cần tối thiểu 50%; SV đạt 60% thì KHÔNG được coi là nguy cơ cấm thi
        // (với mặc định 80% cũ thì 60% sẽ bị cảnh báo sai).
        $class = CourseClass::factory()->create([
            'owner_user_id' => $owner->id,
            'absence_limit_percent' => 50,
            'total_sessions' => 10,
        ]);

        $student = User::factory()->create();
        $member = \App\Models\ClassMember::create([
            'class_id' => $class->id,
            'user_id' => $student->id,
            'status' => \App\Models\ClassMember::STATUS_ACTIVE,
        ]);
        $member->syncProfile(['full_name' => $student->name, 'email' => $student->email]);

        $meeting = \App\Models\ClassMeeting::factory()->create([
            'class_id' => $class->id, 'user_Created' => $owner->id,
            'date' => '2026-07-01', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'status' => 'closed',
        ]);
        $session = \App\Models\ClassSession::factory()->create([
            'class_id' => $class->id, 'meeting_id' => $meeting->id, 'created_by' => $owner->id,
            'date' => '2026-07-01', 'status' => 'closed',
        ]);
        \App\Models\AttendanceRecord::factory()->create([
            'class_session_id' => $session->id, 'class_member_id' => $member->id, 'status' => 'absent',
        ]);

        $warnings = app(\App\Services\StudentsService::class)->getWarningsForStudent($student->id);
        $titles = collect($warnings)->pluck('title')->all();

        // 1 buổi vắng / 10 buổi = 90% chuyên cần, trên ngưỡng 50% -> không cảnh báo cấm thi.
        $this->assertNotContains('Nguy cơ cấm thi', $titles);
    }

    public function test_threshold_percent_is_validated(): void
    {
        $owner = User::factory()->create();
        $class = CourseClass::factory()->create(['owner_user_id' => $owner->id]);

        Livewire::actingAs($owner)->test(ClassSettings::class, ['courseClass' => $class])
            ->set('absenceLimitPercent', 150)
            ->call('save')
            ->assertHasErrors(['absenceLimitPercent']);
    }
}
