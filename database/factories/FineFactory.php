<?php

namespace Database\Factories;

use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fine>
 */
class FineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'borrowing_id' => Borrowing::factory(),
            'user_id' => User::factory()->student(),
            'type' => 'overdue',
            'amount' => fake()->randomFloat(2, 10, 200),
            'days_overdue' => fake()->numberBetween(1, 20),
            'status' => 'unpaid',
        ];
    }
}
