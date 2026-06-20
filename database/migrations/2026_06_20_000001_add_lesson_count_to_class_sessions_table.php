<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('class_sessions', 'lesson_count')) {
                $table->unsignedInteger('lesson_count')->default(3)->after('end_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('class_sessions', 'lesson_count')) {
                $table->dropColumn('lesson_count');
            }
        });
    }
};
