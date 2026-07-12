<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lưu BẢNG ĐIỂM TRỪ chuyên cần theo TỪNG LỚP để giảng viên tự cấu hình.
 *
 * Trước đây điểm trừ là giá trị CỐ ĐỊNH trong CourseClass::getAttendanceRules()
 * (đi muộn 0.5, vắng 1.0, có phép tuỳ cờ) — các ô nhập trong cấu hình lớp không
 * được lưu. Nay thêm 3 cột để các ô đó thực sự điều khiển công thức % chuyên cần.
 * (Điểm trừ "có mặt" luôn = 0 nên không lưu.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (! Schema::hasColumn('classes', 'deduct_late')) {
                $table->decimal('deduct_late', 4, 2)->default(0.5)->after('deduct_excused_absence');
            }
            if (! Schema::hasColumn('classes', 'deduct_absent')) {
                $table->decimal('deduct_absent', 4, 2)->default(1.0)->after('deduct_late');
            }
            if (! Schema::hasColumn('classes', 'deduct_excused')) {
                $table->decimal('deduct_excused', 4, 2)->default(0.0)->after('deduct_absent');
            }
        });

        // Backfill: lớp đang bật "trừ vắng có phép" -> điểm trừ có phép = 1.0 (giữ nguyên hành vi cũ).
        DB::table('classes')->where('deduct_excused_absence', true)->update(['deduct_excused' => 1.0]);
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            foreach (['deduct_late', 'deduct_absent', 'deduct_excused'] as $col) {
                if (Schema::hasColumn('classes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
