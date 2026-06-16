<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->string('status', 50)->default('active')->index();
            $table->timestamps();

            $table->unique('owner_id');
        });

        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->integer('default_gps_radius')->default(50);
            $table->decimal('default_absence_warning', 5, 2)->default(20.00);
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('code', 50);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subject_code', 50)->nullable();
            $table->string('semester', 50)->nullable();
            $table->boolean('require_approval')->default(false);
            $table->string('status', 50)->default('active')->index();
            $table->integer('total_sessions')->default(15);
            $table->integer('lessons_per_session')->default(3);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('class_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('student_code', 50);
            $table->string('full_name');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['class_id', 'student_code']);
            $table->index('user_id');
        });

        Schema::create('class_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('student_code', 50);
            $table->string('full_name');
            $table->string('status', 50)->default('pending')->index();
            $table->timestamps();
        });

        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('qr_token')->nullable()->unique();
            $table->timestamp('token_expires_at')->nullable();
            $table->decimal('gps_latitude', 10, 8)->nullable();
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->integer('gps_radius')->nullable();
            $table->string('status', 50)->default('pending')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'created_at']);
            $table->index(['qr_token', 'token_expires_at']);
        });

        Schema::create('attendance_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('class_member_id')->constrained('class_members')->cascadeOnDelete();
            $table->string('status', 50)->default('pending')->index();
            $table->foreignId('method_id')->constrained('attendance_methods')->restrictOnDelete();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('check_in_time')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('device_info')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['class_session_id', 'class_member_id']);
            $table->index(['tenant_id', 'class_session_id', 'status']);
        });

        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('class_member_id')->constrained('class_members')->cascadeOnDelete();
            $table->integer('total_present')->default(0);
            $table->integer('total_late')->default(0);
            $table->integer('total_absent')->default(0);
            $table->integer('total_excused')->default(0);
            $table->boolean('is_banned_from_exam')->default(false);
            $table->timestamp('updated_at')->nullable();

            $table->unique(['class_id', 'class_member_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('table_name')->nullable();
            $table->unsignedBigInteger('row_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
        });

        Schema::create('check_in_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('student_code_attempt', 50)->nullable();
            $table->string('scan_type', 50);
            $table->string('payload_signature')->unique();
            $table->boolean('is_valid')->default(true);
            $table->string('fail_reason', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('device_info')->nullable();
            $table->timestamp('scanned_at');
        });

        Schema::create('attendance_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('rule_type', 50);
            $table->decimal('threshold_value', 5, 2);
            $table->string('condition_operator', 10)->default('>=');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['class_id', 'is_active']);
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('max_classes');
            $table->boolean('can_export_excel')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->string('status', 50)->default('active')->index();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50);
            $table->string('transaction_code', 100)->unique();
            $table->string('partner_reference_id')->nullable();
            $table->string('status', 50)->default('pending')->index();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('fcm_token')->unique();
            $table->string('device_name')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('class_member_id')->constrained('class_members')->cascadeOnDelete();
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->text('reason');
            $table->string('proof_image')->nullable();
            $table->string('status', 50)->default('pending')->index();
            $table->text('rejected_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('attendance_rules');
        Schema::dropIfExists('check_in_scans');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('attendance_summaries');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_methods');
        Schema::dropIfExists('class_sessions');
        Schema::dropIfExists('class_join_requests');
        Schema::dropIfExists('class_members');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('tenants');
    }
};
