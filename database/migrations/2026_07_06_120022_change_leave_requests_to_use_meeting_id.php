<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Thêm cột class_meeting_id
        if (!Schema::hasColumn('leave_requests', 'class_meeting_id')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->foreignId('class_meeting_id')->nullable()->constrained('class_meetings')->cascadeOnDelete();
            });

            // 2. Chuyển đổi dữ liệu cũ: lấy meeting_id từ bảng class_sessions
            DB::table('leave_requests')->update([
                'class_meeting_id' => DB::raw('(SELECT meeting_id FROM class_sessions WHERE class_sessions.id = leave_requests.class_session_id LIMIT 1)')
            ]);
        }

        // Đảm bảo không có record nào null (nếu data chuẩn)
        // Sau đó xóa cột cũ
        // MySQL dùng SHOW KEYS/information_schema để kiểm tra index/khoá trước khi drop (an toàn
        // khi chạy lại trên DB thật). Các driver khác (vd SQLite lúc chạy test) không hiểu SHOW,
        // và migrate luôn chạy từ đầu trên schema sạch nên chỉ cần thao tác schema trực tiếp.
        if (DB::getDriverName() === 'mysql') {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (collect(DB::select("SHOW KEYS FROM leave_requests WHERE Key_name = 'leave_requests_class_member_id_class_session_id_unique'"))->isNotEmpty()) {
                    $table->index('class_member_id', 'temp_class_member_index');
                    $table->dropUnique(['class_member_id', 'class_session_id']);
                }
                if (collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'leave_requests' AND CONSTRAINT_NAME = 'leave_requests_class_session_id_foreign'"))->isNotEmpty()) {
                    $table->dropForeign(['class_session_id']);
                }
                if (collect(DB::select("SHOW KEYS FROM leave_requests WHERE Key_name = 'leave_requests_class_session_id_status_index'"))->isNotEmpty()) {
                    $table->dropIndex(['class_session_id', 'status']);
                }

                if (Schema::hasColumn('leave_requests', 'class_session_id')) {
                    $table->dropColumn('class_session_id');
                }

                // Thêm các constraints mới
                if (collect(DB::select("SHOW KEYS FROM leave_requests WHERE Key_name = 'leave_requests_class_member_id_class_meeting_id_unique'"))->isEmpty()) {
                    $table->unique(['class_member_id', 'class_meeting_id']);
                }
                if (collect(DB::select("SHOW KEYS FROM leave_requests WHERE Key_name = 'leave_requests_class_meeting_id_status_index'"))->isEmpty()) {
                    $table->index(['class_meeting_id', 'status']);
                }
                if (collect(DB::select("SHOW KEYS FROM leave_requests WHERE Key_name = 'temp_class_member_index'"))->isNotEmpty()) {
                    $table->dropIndex('temp_class_member_index');
                }
            });
        } elseif (Schema::hasColumn('leave_requests', 'class_session_id')) {
            // SQLite (test) không drop được cột còn bị FK/unique/index tham chiếu — gỡ ràng buộc
            // trước, mỗi bước một lần rebuild bảng, rồi mới bỏ cột và thêm ràng buộc mới.
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropUnique(['class_member_id', 'class_session_id']);
                $table->dropIndex(['class_session_id', 'status']);
            });
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropForeign(['class_session_id']);
            });
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropColumn('class_session_id');
            });
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->unique(['class_member_id', 'class_meeting_id']);
                $table->index(['class_meeting_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('class_session_id')->nullable()->constrained('class_sessions')->cascadeOnDelete();
        });

        // Khôi phục bằng cách lấy session đầu tiên của meeting
        DB::table('leave_requests')->update([
            'class_session_id' => DB::raw('(SELECT id FROM class_sessions WHERE class_sessions.meeting_id = leave_requests.class_meeting_id LIMIT 1)')
        ]);

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['class_meeting_id']);
            $table->dropUnique(['class_member_id', 'class_meeting_id']);
            $table->dropIndex(['class_meeting_id', 'status']);
            $table->dropColumn('class_meeting_id');

            $table->unique(['class_member_id', 'class_session_id']);
            $table->index(['class_session_id', 'status']);
        });
    }
};
