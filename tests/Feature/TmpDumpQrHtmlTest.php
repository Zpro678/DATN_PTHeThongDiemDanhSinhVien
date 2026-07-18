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
 * Tạm: xuất HTML THẬT của bảng học viên (do component render) ra file tĩnh để soi bằng mắt.
 * Không phải markup viết tay — đúng thứ giảng viên sẽ thấy.
 */
class TmpDumpQrHtmlTest extends TestCase
{
    use RefreshDatabase;

    public function test_dump(): void
    {
        $owner = User::factory()->create();
        $class = CourseClass::factory()->create(['owner_user_id' => $owner->id, 'name' => 'Lập trình Web']);
        $meeting = ClassMeeting::factory()->create([
            'class_id' => $class->id, 'user_Created' => $owner->id,
            'date' => '2026-07-01', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'status' => 'active',
        ]);
        $session = ClassSession::factory()->create([
            'class_id' => $class->id, 'meeting_id' => $meeting->id, 'created_by' => $owner->id,
            'date' => '2026-07-01', 'status' => 'active', 'qr_token' => 'T1',
            'gps_latitude' => 10.0, 'gps_longitude' => 106.0, 'gps_radius' => 30,
        ]);

        $add = function (string $name, array $attrs) use ($class, $session) {
            $m = ClassMember::create([
                'class_id' => $class->id,
                'user_id' => User::factory()->create()->id,
                'status' => ClassMember::STATUS_ACTIVE,
            ]);
            $m->syncProfile(['full_name' => $name, 'email' => uniqid().'@example.com']);
            AttendanceRecord::factory()->create(array_merge([
                'class_session_id' => $session->id, 'class_member_id' => $m->id,
                'status' => 'present', 'check_in_time' => now(),
            ], $attrs));
        };

        // 1. Bình thường
        $add('Nguyễn Tuấn Khanh', ['device_id' => 'D-1']);
        // 2. Chỉ lệch GPS
        $add('Đặng Thu Hà', ['device_id' => 'D-2', 'gps_fraud_flag' => 'out_of_radius', 'distance_meters' => 41]);
        // 3+4. Trùng thiết bị (một em kèm lệch GPS)
        $add('Lê Hoàng Nam', ['device_id' => 'D-CHUNG']);
        $add('Phạm Thị Mai', ['device_id' => 'D-CHUNG', 'gps_fraud_flag' => 'out_of_radius', 'distance_meters' => 95]);
        // 5. Chưa điểm danh
        $add('Trần Quốc Bảo', ['device_id' => null, 'status' => 'pending', 'check_in_time' => null]);
        // 6-10. Nhóm 5 em chung một máy -> rút gọn danh sách
        foreach (['An Nguyễn', 'Bình Trần', 'Cường Lê', 'Dũng Phạm', 'Giang Vũ'] as $n) {
            $add($n, ['device_id' => 'D-DONG']);
        }

        $html = Livewire::actingAs($owner)->test(QrAttendanceSession::class, ['session' => $session->id])->html();

        preg_match('/<table.*?<\/table>/s', $html, $m2);
        $table = $m2[0] ?? '<p>không tìm thấy bảng</p>';

        // Lấy tên file CSS đã build từ manifest để không bị lệch hash.
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $css = '/build/'.($manifest['resources/css/app.css']['file'] ?? '');

        file_put_contents(public_path('__mockup.html'),
            '<!doctype html><html lang="vi"><head><meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width, initial-scale=1">'
            .'<title>Bảng điểm danh — hiện trạng</title>'
            .'<link rel="stylesheet" href="'.$css.'">'
            .'<style>body{background:#f1f5f9;padding:24px}</style></head><body>'
            .'<div class="mx-auto max-w-[1280px]">'
            .'<h1 class="mb-1 text-xl font-black text-slate-800">Bảng học viên trong phiên QR — hiện trạng</h1>'
            .'<p class="mb-4 text-[13px] text-slate-500">HTML thật do component render. Bán kính lớp = 30m.</p>'
            .'<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">'.$table.'</div>'
            .'</div></body></html>'
        );

        $this->assertTrue(true);
    }
}
