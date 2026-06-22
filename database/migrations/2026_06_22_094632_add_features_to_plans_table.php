<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->integer('duration_days')->default(30)->after('price'); // 0 = permanent
            $table->integer('max_students_per_class')->default(50)->after('max_classes');
            $table->integer('max_gps_radius')->default(100)->after('max_students_per_class');
            $table->boolean('api_access')->default(false)->after('max_gps_radius');
            $table->string('support_level')->default('Cơ bản')->after('api_access');
            $table->boolean('is_active')->default(true)->after('support_level');
            $table->json('features')->nullable()->after('is_active');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'duration_days',
                'max_students_per_class',
                'max_gps_radius',
                'api_access',
                'support_level',
                'is_active',
                'features',
                'updated_at'
            ]);
        });
    }
};
