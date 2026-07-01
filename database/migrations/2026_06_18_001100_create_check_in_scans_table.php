<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_in_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('student_code_attempt', 50)->nullable(); // Mã SV nhập tay nếu là guest.
            $table->string('scan_type', 50); // qr | gps | link.
            $table->string('payload_signature'); // HMAC chống Replay.
            $table->boolean('is_valid')->default(true);
            // timeout | invalid_signature | out_of_range | not_enrolled.
            $table->string('fail_reason', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->timestamp('scanned_at')->useCurrent(); // IMMUTABLE.

            $table->index(['class_session_id', 'scanned_at']);
            $table->index(['user_id', 'scanned_at']);
            $table->index(['device_fingerprint', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_in_scans');
    }
};
