<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends BorrowingFactory
{
    public function definition(): array
    {
        return array_merge(parent::definition(), [
            'penalty' => fake()->randomFloat(2, 5, 100),
            'penalty_status' => 'unpaid',
            'penalty_notes' => null,
            'penalty_handled_by_id' => null,
            'penalty_paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'penalty_status' => 'paid',
            'penalty_paid_at' => now(),
        ]);
    }
}
