<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_tier', 50)->unique(); // FREE | PRO | ENTERPRISE.
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_days')->default(30); // 0 = vĩnh viễn.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Cấu hình giới hạn theo gói (1-1 với plans).
        Schema::create('plan_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->unique()->constrained('plans')->cascadeOnDelete();
            $table->integer('max_classes');
            $table->integer('max_students_per_class')->default(50);
            $table->boolean('can_export_excel')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_configs');
        Schema::dropIfExists('plans');
    }
};
