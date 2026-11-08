<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookCopy>
 */
class BookCopyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'rfid_uid' => strtoupper(fake()->unique()->bothify('COPY-####??')),
            'status' => BookCopy::STATUS_AVAILABLE,
        ];
    }

    public function borrowed(): static
    {
        return $this->state(fn () => ['status' => BookCopy::STATUS_BORROWED]);
    }

    public function lost(): static
    {
        return $this->state(fn () => ['status' => BookCopy::STATUS_LOST]);
    }

    public function damaged(): static
    {
        return $this->state(fn () => ['status' => BookCopy::STATUS_DAMAGED]);
    }
}
