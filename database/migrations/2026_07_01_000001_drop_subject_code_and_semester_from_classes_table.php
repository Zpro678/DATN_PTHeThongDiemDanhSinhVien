<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [];

        if (Schema::hasColumn('classes', 'subject_code')) {
            $columns[] = 'subject_code';
        }

        if (Schema::hasColumn('classes', 'semester')) {
            $columns[] = 'semester';
        }

        if ($columns !== []) {
            Schema::table('classes', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        // The current database diagram does not include these columns.
    }
};
