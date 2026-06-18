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
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('student_code', 50);
            $table->string('full_name');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['class_id', 'student_code']);
            $table->unique(['class_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('class_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('student_code', 50);
            $table->string('full_name');
            $table->string('status', 50)->default('pending')->index();
            $table->timestamps();

            $table->index(['class_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_join_requests');
        Schema::dropIfExists('class_members');
    }
};
