<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lưu điểm nghi vấn fake GPS (chấm tại bước /gps/verify) để bước check-in đọc lại và gắn cờ.
 *
 * Tổng hợp nhiều tín hiệu độc lập: độ chính xác bất thường, toạ độ không đổi qua nhiều mẫu,
 * thiếu độ cao, và lệch IP↔GPS. Điểm càng cao càng nghi giả lập vị trí.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gps_verifications', function (Blueprint $table) {
            $table->unsignedTinyInteger('fraud_score')->default(0)->after('accuracy');
            $table->string('fraud_reasons', 500)->nullable()->after('fraud_score');
        });
    }

    public function down(): void
    {
        Schema::table('gps_verifications', function (Blueprint $table) {
            $table->dropColumn(['fraud_score', 'fraud_reasons']);
        });
    }
};
