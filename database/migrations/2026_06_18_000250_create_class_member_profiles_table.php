<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Danh tính SV (tên/email) do GV import hoặc SV nhập form khai báo.
        // Tách khỏi class_members để giữ class_members đúng DBML; quan hệ 1-1.
        Schema::create('class_member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_member_id')->unique()->constrained('class_members')->cascadeOnDelete();
            $table->string('full_name')->nullable(); // Họ tên (đặc biệt khi SV chưa có tài khoản).
            $table->string('email')->nullable(); // Email SV (tùy chọn).
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_member_profiles');
    }
};
