<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persist cờ `device_check` (kiểm tra thiết bị chống điểm danh hộ) ở mức phiên.
 *
 * Trước đây toggle này chỉ nằm trong cache cấu hình form tạo QR, không lưu vào phiên và
 * không được tôn trọng khi phát hiện. Thêm cột để giảng viên bật/tắt kiểm tra thiết bị
 * cho từng phiên. Mặc định true (giữ hành vi hiện tại).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->boolean('device_check')->default(true)->after('gps_radius');
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn('device_check');
        });
    }
};
