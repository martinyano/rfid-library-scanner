<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Borrowing>
 */
class BorrowingFactory extends Factory
{
    public function definition(): array
    {
        $borrowedAt = today()->subDays(3);

        return [
            'user_id' => User::factory()->student(),
            'book_id' => Book::factory(),
            'borrowed_at' => $borrowedAt,
            'due_at' => $borrowedAt->copy()->addDays(7),
            'days_to_return' => 7,
            'returned_at' => null,
            'penalty' => null,
            'penalty_status' => 'unpaid',
            'penalty_notes' => null,
            'penalty_handled_by_id' => null,
            'penalty_paid_at' => null,
            'status' => 'borrowed',
        ];
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'returned_at' => today(),
            'status' => 'returned',
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'borrowed_at' => today()->subDays(12),
            'due_at' => today()->subDays(5),
            'days_to_return' => 7,
        ]);
    }
}
