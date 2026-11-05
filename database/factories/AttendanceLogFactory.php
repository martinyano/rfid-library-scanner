<?php

namespace Database\Factories;

use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AttendanceLog>
 */
class AttendanceLogFactory extends Factory
{
    public function definition(): array
    {
        $entry = now()->subHours(fake()->numberBetween(1, 6));

        return [
            'user_id' => User::factory()->student(),
            'rfid_uid' => strtoupper(Str::random(10)),
            'entry_at' => $entry,
            'exit_at' => $entry->copy()->addMinutes(fake()->numberBetween(20, 180)),
            'source' => 'scan_page',
            'closed_automatically' => false,
        ];
    }
}
