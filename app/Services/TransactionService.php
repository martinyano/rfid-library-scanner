<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function recordFine(Borrowing $borrowing, float $amount, ?User $handledBy, ?string $notes): Transaction
    {
        if (! $borrowing->due_at || ! $borrowing->due_at->lt(today())) {
            throw ValidationException::withMessages(['amount' => 'A fine can only be recorded for an overdue loan.']);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'The fine amount must be greater than zero.']);
        }

        return DB::transaction(fn () => Transaction::query()->create([
            'borrowing_id' => $borrowing->id,
            'user_id' => $borrowing->user_id,
            'amount' => $amount,
            'status' => 'unpaid',
            'notes' => $notes,
            'handled_by_id' => $handledBy?->id,
        ]));
    }

    public function markPaid(Transaction $transaction, ?User $handledBy): void
    {
        if ($transaction->status === 'paid') {
            throw ValidationException::withMessages(['transaction' => 'This transaction is already marked as paid.']);
        }

        $transaction->update([
            'status' => 'paid',
            'paid_at' => now(),
            'handled_by_id' => $handledBy?->id,
        ]);
    }
}
