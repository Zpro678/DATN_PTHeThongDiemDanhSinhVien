<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tổng hợp theo buổi (MATERIALIZED): present/late/absent/excused + điểm trừ.
        Schema::create('meeting_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('class_meetings')->cascadeOnDelete();
            $table->foreignId('class_member_id')->constrained('class_members')->cascadeOnDelete();
            $table->string('status', 50)->default('absent'); // present | late | absent | excused.
            $table->decimal('deduction', 3, 1)->default(0); // Điểm trừ chuyên cần: 0 / 0.5 / 1.
            $table->string('auto_status', 50)->nullable(); // Trạng thái hệ thống tự tính để đối chiếu.
            $table->boolean('is_overridden')->default(false); // GV đã chỉnh tay hay chưa.
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'class_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_summaries');
    }
};
