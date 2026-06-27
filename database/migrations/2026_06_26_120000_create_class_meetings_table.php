<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('name'); // Tên buổi học.
            $table->date('date'); // Ngày diễn ra buổi học.
            $table->time('start_time')->nullable(); // Giờ bắt đầu buổi.
            $table->time('end_time')->nullable(); // Giờ kết thúc buổi.
            $table->string('status', 50)->default('active'); // active/closed.
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_meetings');
    }
};
