<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tenant_id' => Tenant::factory(),
            'amount' => fake()->randomElement([0, 99000, 199000]),
            'payment_method' => fake()->randomElement(['payos', 'vietqr', 'momo']),
            'transaction_code' => fake()->unique()->bothify('DD T### PRO'),
            'partner_reference_id' => fake()->optional()->uuid(),
            'status' => fake()->randomElement(['pending', 'success', 'failed', 'canceled']),
        ];
    }
}
