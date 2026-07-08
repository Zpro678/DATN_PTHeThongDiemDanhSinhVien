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
        Schema::table('system_feedbacks', function (Blueprint $table) {
            $table->text('attachment_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_feedbacks', function (Blueprint $table) {
            $table->string('attachment_path', 255)->nullable()->change();
        });
    }
};
