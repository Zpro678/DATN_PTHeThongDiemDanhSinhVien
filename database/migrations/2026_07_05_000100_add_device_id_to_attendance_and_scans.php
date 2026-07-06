<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm `device_id` — mã định danh TRÌNH DUYỆT bền (client sinh, lưu localStorage + cookie).
 *
 * Khác với `device_fingerprint` (md5 IP+User-Agent, dễ trùng trên cùng NAT/cùng dòng máy),
 * device_id là ngẫu nhiên theo từng trình duyệt nên 2 máy khác nhau không đụng nhau — dùng
 * làm khóa CHÍNH để phát hiện "một máy điểm danh cho nhiều sinh viên".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('device_id', 191)->nullable()->after('device_fingerprint');
            $table->index(['class_session_id', 'device_id']);
        });

        Schema::table('check_in_scans', function (Blueprint $table) {
            $table->string('device_id', 191)->nullable()->after('device_fingerprint');
            $table->index(['device_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropIndex(['class_session_id', 'device_id']);
            $table->dropColumn('device_id');
        });

        Schema::table('check_in_scans', function (Blueprint $table) {
            $table->dropIndex(['device_id', 'scanned_at']);
            $table->dropColumn('device_id');
        });
    }
};
