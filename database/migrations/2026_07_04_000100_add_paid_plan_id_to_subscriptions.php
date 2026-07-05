<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Gói người dùng ĐÃ TRẢ TIỀN (khác plan_id = gói đang dùng). Cho phép đổi qua
            // lại miễn phí giữa FREE và đúng gói này; gói khác chưa mua thì phải thanh toán.
            $table->foreignId('paid_plan_id')->nullable()->after('plan_id')->constrained('plans')->nullOnDelete();
        });

        // Backfill: các thuê bao sẵn có coi gói đang dùng là gói đã mua.
        DB::table('subscriptions')->whereNull('paid_plan_id')->update([
            'paid_plan_id' => DB::raw('plan_id'),
        ]);
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paid_plan_id');
        });
    }
};
