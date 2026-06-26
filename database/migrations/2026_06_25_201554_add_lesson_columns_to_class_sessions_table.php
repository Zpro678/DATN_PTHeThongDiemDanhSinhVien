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
        Schema::table('class_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('class_sessions', 'start_lesson')) {
                $table->unsignedInteger('start_lesson')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('class_sessions', 'end_lesson')) {
                $table->unsignedInteger('end_lesson')->nullable()->after('start_lesson');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('class_sessions', 'start_lesson')) {
                $table->dropColumn('start_lesson');
            }
            if (Schema::hasColumn('class_sessions', 'end_lesson')) {
                $table->dropColumn('end_lesson');
            }
        });
    }
};
