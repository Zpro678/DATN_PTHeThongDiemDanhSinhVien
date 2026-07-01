<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            // Phiên thuộc buổi nào; cascadeOnDelete khi buổi bị xóa.
            $table->foreignId('meeting_id')->nullable()->constrained('class_meetings')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('qr_token')->nullable()->unique();
            $table->timestamp('token_expires_at')->nullable();
            $table->integer('qr_refresh_rate')->default(10); // Số giây làm mới QR.
            $table->decimal('gps_latitude', 10, 8)->nullable();
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->integer('gps_radius')->nullable();
            $table->string('status', 50)->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'date']);
            $table->index(['class_id', 'created_at']);
            $table->index(['qr_token', 'token_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
