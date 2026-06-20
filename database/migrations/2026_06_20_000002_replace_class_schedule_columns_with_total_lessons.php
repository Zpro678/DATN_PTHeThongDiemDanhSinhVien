<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('classes', 'total_lessons')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->unsignedInteger('total_lessons')->default(45)->after('status');
            });
        }

        if (
            Schema::hasColumn('classes', 'total_sessions')
            && Schema::hasColumn('classes', 'lessons_per_session')
        ) {
            DB::table('classes')->update([
                'total_lessons' => DB::raw('COALESCE(total_sessions, 15) * COALESCE(lessons_per_session, 3)'),
            ]);
        }

        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn('classes', 'total_sessions') ? 'total_sessions' : null,
            Schema::hasColumn('classes', 'lessons_per_session') ? 'lessons_per_session' : null,
        ]));

        if ($columnsToDrop !== []) {
            Schema::table('classes', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('classes', 'total_sessions')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->unsignedInteger('total_sessions')->default(15)->after('status');
            });
        }

        if (! Schema::hasColumn('classes', 'lessons_per_session')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->unsignedInteger('lessons_per_session')->default(3)->after('total_sessions');
            });
        }

        if (Schema::hasColumn('classes', 'total_lessons')) {
            DB::table('classes')->update([
                'total_sessions' => DB::raw('GREATEST(total_lessons, 1)'),
                'lessons_per_session' => 1,
            ]);

            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('total_lessons');
            });
        }
    }
};
