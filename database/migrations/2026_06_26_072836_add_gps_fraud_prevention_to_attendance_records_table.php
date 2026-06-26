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
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->decimal('gps_accuracy_meters', 8, 2)->nullable()->after('distance_meters');
            $table->decimal('gps_latitude_recorded', 10, 8)->nullable()->after('gps_accuracy_meters');
            $table->decimal('gps_longitude_recorded', 11, 8)->nullable()->after('gps_latitude_recorded');
            $table->string('gps_fraud_flag')->nullable()->after('gps_longitude_recorded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn([
                'gps_accuracy_meters',
                'gps_latitude_recorded',
                'gps_longitude_recorded',
                'gps_fraud_flag'
            ]);
        });
    }
};
