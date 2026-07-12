<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng "outbox" cho email thông báo import.
     *
     * Import KHÔNG gửi mail trực tiếp (tránh bắn hàng nghìn mail một lúc). Mỗi SV
     * chưa có tài khoản được ghi 1 dòng chờ ở đây; lệnh import:flush-notifications
     * chạy theo scheduler sẽ rút từng lô và gửi có tiết chế tốc độ.
     */
    public function up(): void
    {
        Schema::create('pending_import_notifications', function (Blueprint $table) {
            $table->id();
            // Không ràng buộc FK cứng: nếu lớp bị xoá, dòng chờ vẫn có thể gửi/được dọn.
            $table->uuid('class_id')->nullable();
            $table->string('email');
            $table->string('full_name');
            $table->string('student_code')->nullable();
            $table->string('class_name');
            $table->string('join_key');
            $table->string('status', 20)->default('pending'); // pending | sent | failed
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Truy vấn rút hàng chờ: lọc theo status, gửi theo thứ tự FIFO (created_at).
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_import_notifications');
    }
};
