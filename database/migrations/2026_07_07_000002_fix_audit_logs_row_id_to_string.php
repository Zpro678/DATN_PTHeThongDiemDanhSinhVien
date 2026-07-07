<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Đồng bộ kiểu cột audit_logs.row_id = VARCHAR(36) để chứa được CẢ id số lẫn UUID
        // (bảng classes dùng khóa UUID). Một số DB (vd production) đã migrate từ bản cũ khi
        // row_id còn là INT -> tạo lớp (UUID) ném "Incorrect integer value for column 'row_id'".
        // Migration này chỉnh lại cho khớp. Chỉ chạy trên MySQL; SQLite/bản fresh đã là string(36)
        // sẵn từ migration tạo bảng nên không cần đụng.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('row_id', 36)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Không hạ về INT (sẽ mất dữ liệu UUID). Giữ nguyên VARCHAR.
    }
};
