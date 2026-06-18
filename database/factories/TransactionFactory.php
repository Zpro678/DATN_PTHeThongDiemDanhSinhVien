<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomElement([99000, 199000, 299000]),
            'payment_method' => fake()->randomElement(['payos', 'bank_transfer']),
            'transaction_code' => 'TXN-'.Str::upper(Str::random(12)),
            'partner_reference_id' => null,
            'status' => 'pending',
            'created_at' => now(),
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'partner_reference_id' => (string) Str::uuid(),
            'status' => 'success',
        ]);
    }
}
