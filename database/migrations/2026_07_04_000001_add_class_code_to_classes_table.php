<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('classes', 'class_code')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->string('class_code', 50)->nullable()->after('join_key');
                $table->index('class_code');
            });

            // Khởi tạo dữ liệu cũ: class_code = join_key
            DB::table('classes')->update([
                'class_code' => DB::raw('join_key'),
            ]);
        } else {
            // Cột đã tồn tại, chỉ điền dữ liệu null
            DB::table('classes')->whereNull('class_code')->update([
                'class_code' => DB::raw('join_key'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'class_code')) {
                $table->dropIndex(['class_code']);
                $table->dropColumn('class_code');
            }
        });
    }
};
