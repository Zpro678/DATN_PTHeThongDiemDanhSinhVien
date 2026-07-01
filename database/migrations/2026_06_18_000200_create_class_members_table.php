<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_members', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            // Late binding: gắn tài khoản khi SV đăng nhập; nullOnDelete.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('ACTIVE'); // ACTIVE | LEFT | REMOVED.
            $table->timestamp('status_changed_at')->nullable(); // Thời điểm bị đá/tự out.
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['class_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_members');
    }
};
