<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('class_member_id')->constrained('class_members')->cascadeOnDelete();
            // pending | present | late | absent | excused | invalid.
            $table->string('status', 50)->default('pending');
            $table->boolean('is_account')->default(false); // True: SV có tài khoản; False: điền form.
            $table->timestamp('check_in_time')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->decimal('distance_meters', 8, 2)->nullable();
            $table->decimal('gps_accuracy_meters', 8, 2)->nullable();
            $table->decimal('gps_latitude_recorded', 10, 8)->nullable();
            $table->decimal('gps_longitude_recorded', 11, 8)->nullable();
            $table->string('gps_fraud_flag')->nullable(); // Cờ nghi ngờ gian lận GPS.
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['class_session_id', 'class_member_id']);
            $table->index(['class_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
