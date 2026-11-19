<?php

namespace Database\Factories;

use App\Models\Borrowing;
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
            'borrowing_id' => Borrowing::factory(),
            'user_id' => User::factory()->student(),
            'amount' => fake()->randomFloat(2, 5, 100),
            'status' => 'unpaid',
            'notes' => null,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
