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
        Schema::table('classes', function (Blueprint $table) {
            $table->json('attendance_rules')->nullable()->after('status')->comment('Cấu hình bảng điểm trừ chuyên cần');
            if (Schema::hasColumn('classes', 'deduct_excused_absence')) {
                $table->dropColumn('deduct_excused_absence');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->boolean('deduct_excused_absence')->default(false)->after('late_threshold');
            $table->dropColumn('attendance_rules');
        });
    }
};
