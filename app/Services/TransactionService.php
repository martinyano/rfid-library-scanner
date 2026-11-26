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
        $borrowing->refresh();

        if (! $borrowing->due_at || ! $borrowing->due_at->lt(today())) {
            throw ValidationException::withMessages(['amount' => 'A fine can only be recorded for an overdue loan.']);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'The fine amount must be greater than zero.']);
        }

        if ($borrowing->penalty !== null) {
            throw ValidationException::withMessages(['amount' => 'A fine has already been recorded for this loan.']);
        }

        return DB::transaction(function () use ($borrowing, $amount, $handledBy, $notes) {
            $loan = Borrowing::query()->whereKey($borrowing->id)->lockForUpdate()->firstOrFail();

            if ($loan->penalty !== null) {
                throw ValidationException::withMessages(['amount' => 'A fine has already been recorded for this loan.']);
            }

            $loan->update([
                'penalty' => $amount,
                'penalty_status' => 'unpaid',
                'penalty_notes' => $notes,
                'penalty_handled_by_id' => $handledBy?->id,
                'penalty_paid_at' => null,
            ]);

            return Transaction::query()->findOrFail($loan->id);
        });
    }

    public function markPaid(Transaction $transaction, ?User $handledBy): void
    {
        if ($transaction->penalty === null) {
            throw ValidationException::withMessages(['transaction' => 'No fine has been recorded for this transaction.']);
        }

        if ($transaction->penalty_status === 'paid') {
            throw ValidationException::withMessages(['transaction' => 'This transaction is already marked as paid.']);
        }

        $transaction->update([
            'penalty_status' => 'paid',
            'penalty_paid_at' => now(),
            'penalty_handled_by_id' => $handledBy?->id,
        ]);
    }
}
