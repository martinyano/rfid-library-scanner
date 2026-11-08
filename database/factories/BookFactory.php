<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        $copies = fake()->numberBetween(1, 5);

        return [
            'title' => fake()->unique()->sentence(3),
            'author_id' => Author::factory(),
            'category_id' => Category::factory(),
            'isbn' => fake()->unique()->isbn13(),
            'description' => fake()->paragraph(),
            'published_year' => fake()->numberBetween(1980, (int) date('Y')),
            'shelf_location' => strtoupper(fake()->bothify('?#-##')),
            'total_copies' => $copies,
            'available_copies' => $copies,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Book $book) {
            $available = max(0, (int) $book->available_copies);
            $borrowed = max(0, (int) $book->total_copies - $available);

            for ($i = 0; $i < $available; $i++) {
                BookCopy::factory()->create([
                    'book_id' => $book->id,
                    'status' => BookCopy::STATUS_AVAILABLE,
                ]);
            }

            for ($i = 0; $i < $borrowed; $i++) {
                BookCopy::factory()->create([
                    'book_id' => $book->id,
                    'status' => BookCopy::STATUS_BORROWED,
                ]);
            }
        });
    }
}
