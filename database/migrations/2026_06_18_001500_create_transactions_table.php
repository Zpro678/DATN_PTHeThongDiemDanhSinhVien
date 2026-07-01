<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('VND');
            $table->string('payment_method', 50); // MOMO | PAYOS | VNPAY | STRIPE.
            $table->string('transaction_code', 100)->unique(); // Mã giao dịch nội bộ.
            $table->string('reference_code', 100)->nullable()->unique(); // Mã tham chiếu gửi cổng.
            $table->string('gateway_transaction_id', 100)->nullable(); // Mã giao dịch cổng trả về.
            $table->string('status', 50)->default('PENDING');
            $table->text('payment_url')->nullable();
            $table->json('payment_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('transaction_code');
            $table->index('reference_code');
            $table->index('gateway_transaction_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
