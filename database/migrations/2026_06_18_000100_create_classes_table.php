<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID theo DBML.
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->string('join_key', 50)->unique(); // Mã lớp SV nhập để vào lớp.
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('deduct_excused_absence')->default(false); // Có trừ chuyên cần khi vắng có phép.
            $table->boolean('require_approval')->default(false);
            $table->string('status', 50)->default('active');
            $table->integer('total_sessions')->default(15); // Tổng số buổi dự kiến.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
