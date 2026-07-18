<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Đưa các ngưỡng chuyên cần vốn bị gán cứng trong code lên cấu hình theo lớp:
     *  - absence_limit_percent: quỹ vắng cho phép (cũ: hằng số 20%).
     *  - warning_margin_percent: biên cảnh báo trước khi chạm ngưỡng cấm thi (cũ: 85% - 80%).
     *  - near_absence_sessions: còn bao nhiêu buổi trong quỹ vắng thì bắn cảnh báo (cũ: 2).
     *
     * Ngưỡng CẤM THI không có cột riêng — luôn suy ra = 100 - absence_limit_percent
     * để hai con số không thể mâu thuẫn nhau.
     */
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (! Schema::hasColumn('classes', 'absence_limit_percent')) {
                $table->decimal('absence_limit_percent', 5, 2)->default(20);
            }

            if (! Schema::hasColumn('classes', 'warning_margin_percent')) {
                $table->decimal('warning_margin_percent', 5, 2)->default(5);
            }

            if (! Schema::hasColumn('classes', 'near_absence_sessions')) {
                $table->unsignedSmallInteger('near_absence_sessions')->default(2);
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['absence_limit_percent', 'warning_margin_percent', 'near_absence_sessions'],
            fn (string $column) => Schema::hasColumn('classes', $column)
        ));

        if ($columns !== []) {
            Schema::table('classes', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
