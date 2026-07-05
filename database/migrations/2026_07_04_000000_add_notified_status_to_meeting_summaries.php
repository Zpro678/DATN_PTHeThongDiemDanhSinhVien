<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_summaries', function (Blueprint $table) {
            // Trạng thái tổng kết đã gửi thông báo cho học viên — dùng để chỉ gửi lại khi trạng thái đổi.
            $table->string('notified_status', 50)->nullable()->after('is_overridden');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_summaries', function (Blueprint $table) {
            $table->dropColumn('notified_status');
        });
    }
};
