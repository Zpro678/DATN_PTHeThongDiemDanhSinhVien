<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subject_code', 50)->nullable();
            $table->string('semester', 50)->nullable();
            $table->boolean('require_approval')->default(false);
            $table->string('status', 50)->default('active')->index();
            $table->unsignedInteger('total_sessions')->default(15);
            $table->unsignedInteger('lessons_per_session')->default(3);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
