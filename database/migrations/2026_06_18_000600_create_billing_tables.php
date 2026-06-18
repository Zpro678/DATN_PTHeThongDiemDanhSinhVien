<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('max_classes');
            $table->boolean('can_export_excel')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->string('status', 50)->default('active')->index();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50);
            $table->string('transaction_code', 100)->unique();
            $table->string('partner_reference_id')->nullable();
            $table->string('status', 50)->default('pending')->index();
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
