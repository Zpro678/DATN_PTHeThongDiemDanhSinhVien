<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phân biệt email outbox theo việc SV đã có tài khoản hay chưa:
     *  - has_account = false: mail hướng dẫn ĐĂNG KÝ (chưa có tài khoản).
     *  - has_account = true : mail thông báo đã được thêm vào lớp + hướng dẫn ĐĂNG NHẬP.
     */
    public function up(): void
    {
        Schema::table('pending_import_notifications', function (Blueprint $table) {
            $table->boolean('has_account')->default(false)->after('join_key');
        });
    }

    public function down(): void
    {
        Schema::table('pending_import_notifications', function (Blueprint $table) {
            $table->dropColumn('has_account');
        });
    }
};
