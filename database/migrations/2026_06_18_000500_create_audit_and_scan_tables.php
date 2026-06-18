<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('action');
            $table->string('table_name')->nullable();
            $table->unsignedBigInteger('row_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['class_id', 'created_at']);
            $table->index(['table_name', 'row_id']);
        });

        Schema::create('check_in_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('student_code_attempt', 50)->nullable();
            $table->string('scan_type', 50);
            $table->string('payload_signature');
            $table->boolean('is_valid')->default(true);
            $table->string('fail_reason', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->timestamp('scanned_at');

            $table->index(['class_session_id', 'scanned_at']);
            $table->index(['user_id', 'scanned_at']);
            $table->index(['device_fingerprint', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_in_scans');
        Schema::dropIfExists('audit_logs');
    }
};
