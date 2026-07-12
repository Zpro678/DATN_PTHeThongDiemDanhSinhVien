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
}
