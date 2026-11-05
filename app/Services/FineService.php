<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\User;

class FineService
{
    public function __construct(private readonly SettingService $settings) {}

    public function create(Borrowing $borrowing, string $type, float $amount, int $daysOverdue = 0, ?string $notes = null, ?User $handledBy = null): Fine
    {
        return Fine::query()->create([
            'borrowing_id' => $borrowing->id,
            'user_id' => $borrowing->user_id,
            'type' => $type,
            'amount' => round($amount, 2),
            'days_overdue' => $daysOverdue,
            'status' => 'unpaid',
            'notes' => $notes,
            'handled_by_id' => $handledBy?->id,
        ]);
    }

    public function overdueFine(Borrowing $borrowing, int $daysOverdue, ?User $handledBy = null): ?Fine
    {
        if ($daysOverdue < 1) {
            return null;
        }

        if ($borrowing->fine()->exists()) {
            return $borrowing->fine;
        }

        $amount = $daysOverdue * $this->settings->float('fine_per_day', 10);

        return $this->create(
            $borrowing,
            'overdue',
            $amount,
            $daysOverdue,
            "Returned {$daysOverdue} day(s) after the due date.",
            $handledBy
        );
    }

    public function lostFine(Borrowing $borrowing, ?float $amount = null, ?User $handledBy = null): Fine
    {
        $amount ??= $this->settings->float('lost_fee', 500);

        return $this->create(
            $borrowing,
            'lost',
            $amount,
            0,
            'Book reported lost.',
            $handledBy
        );
    }

    public function damagedFine(Borrowing $borrowing, ?float $amount = null, ?User $handledBy = null): Fine
    {
        $amount ??= $this->settings->float('damaged_fee', 200);

        return $this->create(
            $borrowing,
            'damaged',
            $amount,
            0,
            'Book reported damaged.',
            $handledBy
        );
    }

    public function unpaidTotalFor(User $student): float
    {
        return (float) Fine::query()
            ->where('user_id', $student->id)
            ->where('status', 'unpaid')
            ->sum('amount');
    }

    public function hasUnpaidFines(User $student): bool
    {
        return Fine::query()
            ->where('user_id', $student->id)
            ->where('status', 'unpaid')
            ->exists();
    }
}
