<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_configs', function (Blueprint $table) {
            $table->dropColumn('max_gps_radius');
        });
    }

    public function down(): void
    {
        Schema::table('plan_configs', function (Blueprint $table) {
            $table->integer('max_gps_radius')->default(100);
        });
    }
};
