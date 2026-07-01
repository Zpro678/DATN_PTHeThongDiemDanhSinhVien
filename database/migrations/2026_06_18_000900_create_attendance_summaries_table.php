<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tổng hợp theo lớp (MATERIALIZED).
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('class_member_id')->constrained('class_members')->cascadeOnDelete();
            $table->integer('total_present')->default(0);
            $table->integer('total_late')->default(0);
            $table->integer('total_absent')->default(0);
            $table->integer('total_excused')->default(0);
            $table->boolean('is_banned_from_exam')->default(false);
            $table->timestamp('updated_at')->nullable();

            $table->unique(['class_id', 'class_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
    }
};
