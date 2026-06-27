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
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('qr_token')->nullable()->unique();
            $table->timestamp('token_expires_at')->nullable();
            $table->decimal('gps_latitude', 10, 8)->nullable();
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->unsignedInteger('gps_radius')->nullable();
            $table->string('status', 50)->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'date']);
            $table->index(['class_id', 'created_at']);
            $table->index(['qr_token', 'token_expires_at']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('class_member_id')->constrained('class_members')->cascadeOnDelete();
            $table->string('status', 50)->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('check_in_time')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->decimal('distance_meters', 8, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['class_session_id', 'class_member_id']);
            $table->index(['class_session_id', 'status']);
        });

        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('class_member_id')->constrained('class_members')->cascadeOnDelete();
            $table->unsignedInteger('total_present')->default(0);
            $table->unsignedInteger('total_late')->default(0);
            $table->unsignedInteger('total_absent')->default(0);
            $table->unsignedInteger('total_excused')->default(0);
            $table->boolean('is_banned_from_exam')->default(false);
            $table->timestamp('updated_at')->nullable();

            $table->unique(['class_id', 'class_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('class_sessions');
    }
};
