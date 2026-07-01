<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'amount' => fake()->randomElement([99000, 199000, 299000]),
            'currency' => 'VND',
            'payment_method' => fake()->randomElement(['MOMO', 'PAYOS', 'VNPAY', 'STRIPE']),
            'transaction_code' => 'TXN-'.Str::upper(Str::random(12)),
            'reference_code' => null,
            'gateway_transaction_id' => null,
            'status' => 'PENDING',
            'payment_url' => null,
            'payment_response' => null,
            'failure_reason' => null,
            'paid_at' => null,
            'expired_at' => now()->addMinutes(30),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_code' => (string) Str::uuid(),
            'gateway_transaction_id' => (string) Str::uuid(),
            'status' => 'PAID',
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'FAILED',
            'failure_reason' => fake()->sentence(),
        ]);
    }
}
