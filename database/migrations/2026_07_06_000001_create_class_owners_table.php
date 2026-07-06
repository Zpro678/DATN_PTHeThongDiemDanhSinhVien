<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Đồng chủ lớp: một lớp (classes) có thể có nhiều người CÙNG QUẢN LÝ ngoài chủ chính
        // (classes.owner_user_id). Bảng này KHÔNG chứa học viên — học viên vẫn ở class_members.
        Schema::create('class_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 30)->default('co_owner'); // co_owner (ngang chủ về nghiệp vụ dạy).
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable(); // Mốc kích hoạt (MVP: set ngay khi thêm).
            $table->timestamps();

            $table->unique(['class_id', 'user_id']); // Mỗi người chỉ là đồng chủ một lần trong lớp.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_owners');
    }
};
