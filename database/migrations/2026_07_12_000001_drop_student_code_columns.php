<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gỡ bỏ hoàn toàn khái niệm MSSV/student_code khỏi hệ thống.
 *
 * Sinh viên nay được định danh bằng HỌ TÊN + EMAIL (và tài khoản liên kết nếu có).
 * Dùng guard hasColumn để chạy được trên cả DB đã tồn tại lẫn migrate:fresh
 * (bản create đã bỏ cột nên fresh sẽ bỏ qua nhánh drop).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('class_member_profiles', 'student_code')) {
            Schema::table('class_member_profiles', function (Blueprint $table) {
                $table->dropColumn('student_code');
            });
        }

        if (Schema::hasColumn('check_in_scans', 'student_code_attempt')) {
            Schema::table('check_in_scans', function (Blueprint $table) {
                $table->dropColumn('student_code_attempt');
            });
        }

        if (Schema::hasColumn('pending_import_notifications', 'student_code')) {
            Schema::table('pending_import_notifications', function (Blueprint $table) {
                $table->dropColumn('student_code');
            });
        }
    }

    public function down(): void
    {
        // Không phục hồi cột (đã gỡ MSSV theo yêu cầu nghiệp vụ).
    }
};
