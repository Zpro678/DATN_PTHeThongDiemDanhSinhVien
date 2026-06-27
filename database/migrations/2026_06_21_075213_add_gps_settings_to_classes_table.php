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
            $table->decimal('gps_latitude', 10, 8)->nullable()->after('total_sessions');
            $table->decimal('gps_longitude', 11, 8)->nullable()->after('gps_latitude');
            $table->unsignedInteger('gps_radius')->nullable()->default(100)->after('gps_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['gps_latitude', 'gps_longitude', 'gps_radius']);
        });
    }
};
