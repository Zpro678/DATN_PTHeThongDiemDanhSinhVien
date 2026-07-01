<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // Mã nhập vào. VD: SUMMER2026.
            // null = áp dụng mọi gói; có ID = chỉ định gói cụ thể.
            $table->foreignId('applicable_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('type', 50)->default('PERCENT'); // PERCENT | FIXED.
            $table->decimal('value', 10, 2); // Giá trị giảm (VD: 20 hoặc 50000).
            $table->integer('usage_limit')->nullable(); // null = vô hạn.
            $table->integer('used_count')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
