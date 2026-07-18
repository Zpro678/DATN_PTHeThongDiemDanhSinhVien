<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ngưỡng đi muộn không còn được dùng ở bất kỳ logic tính điểm danh nào
        // (trạng thái 'late' do giảng viên đánh dấu / quy đổi qua deduct_late).
        if (Schema::hasColumn('classes', 'late_threshold')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('late_threshold');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('classes', 'late_threshold')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->integer('late_threshold')->default(15);
            });
        }
    }
};
