<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowingService
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly FineService $fines,
    ) {}

    public function activeCount(User $student): int
    {
        return Borrowing::query()
            ->where('user_id', $student->id)
            ->where('status', 'borrowed')
            ->count();
    }

    public function canBorrow(User $student, Book $book): ?string
    {
        if (! $student->is_active) {
            return 'Your account is inactive. Please contact the librarian.';
        }

        if ($book->available_copies < 1) {
            return 'All copies of this book are currently borrowed.';
        }

        if ($this->activeCount($student) >= $this->settings->int('max_books', 3)) {
            return 'You have reached the maximum number of borrowed books.';
        }

        $alreadyBorrowed = Borrowing::query()
            ->where('user_id', $student->id)
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->exists();

        if ($alreadyBorrowed) {
            return 'You already have an active loan for this book.';
        }

        if ($this->settings->bool('block_on_unpaid_fine') && $this->fines->hasUnpaidFines($student)) {
            return 'You have unpaid fines. Please settle them at the library desk.';
        }

        return null;
    }

    /**
     * @throws ValidationException
     */
    public function borrow(User $student, Book $book): Borrowing
    {
        return DB::transaction(function () use ($student, $book) {
            $lockedBook = Book::query()->whereKey($book->id)->lockForUpdate()->firstOrFail();

            if ($message = $this->canBorrow($student, $lockedBook)) {
                throw ValidationException::withMessages(['borrow' => $message]);
            }

            $borrowedAt = today();
            $dueAt = today()->addDays($this->settings->int('borrow_days', 7));

            $borrowing = Borrowing::query()->create([
                'user_id' => $student->id,
                'book_id' => $lockedBook->id,
                'borrowed_at' => $borrowedAt,
                'due_at' => $dueAt,
                'status' => 'borrowed',
            ]);

            $lockedBook->decrement('available_copies');

            return $borrowing;
        });
    }

    public function markReturned(Borrowing $borrowing, ?User $handledBy = null): ?Fine
    {
        return DB::transaction(function () use ($borrowing, $handledBy) {
            $borrowing = Borrowing::query()->whereKey($borrowing->id)->lockForUpdate()->firstOrFail();

            if ($borrowing->status !== 'borrowed') {
                throw ValidationException::withMessages(['borrowing' => 'This loan is no longer active.']);
            }

            $returnedAt = today();
            $daysLate = $this->daysLate($borrowing, $returnedAt);

            $borrowing->update([
                'status' => 'returned',
                'returned_at' => $returnedAt,
                'handled_by_id' => $handledBy?->id,
            ]);

            $book = $borrowing->book()->withTrashed()->first();

            if ($book) {
                $book->update([
                    'available_copies' => min($book->total_copies, $book->available_copies + 1),
                ]);
            }

            return $this->fines->overdueFine($borrowing, $daysLate, $handledBy);
        });
    }

    public function markLost(Borrowing $borrowing, ?float $amount = null, ?User $handledBy = null): Fine
    {
        return DB::transaction(function () use ($borrowing, $amount, $handledBy) {
            $borrowing = Borrowing::query()->whereKey($borrowing->id)->lockForUpdate()->firstOrFail();

            if ($borrowing->status !== 'borrowed') {
                throw ValidationException::withMessages(['borrowing' => 'This loan is no longer active.']);
            }

            $borrowing->update([
                'status' => 'lost',
                'returned_at' => today(),
                'handled_by_id' => $handledBy?->id,
            ]);

            $this->removeCopyFromCirculation($borrowing);

            return $this->fines->lostFine($borrowing, $amount, $handledBy);
        });
    }

    public function markDamaged(Borrowing $borrowing, ?float $amount = null, ?User $handledBy = null): Fine
    {
        return DB::transaction(function () use ($borrowing, $amount, $handledBy) {
            $borrowing = Borrowing::query()->whereKey($borrowing->id)->lockForUpdate()->firstOrFail();

            if ($borrowing->status !== 'borrowed') {
                throw ValidationException::withMessages(['borrowing' => 'This loan is no longer active.']);
            }

            $borrowing->update([
                'status' => 'damaged',
                'returned_at' => today(),
                'handled_by_id' => $handledBy?->id,
            ]);

            $this->removeCopyFromCirculation($borrowing);

            return $this->fines->damagedFine($borrowing, $amount, $handledBy);
        });
    }

    public function extend(Borrowing $borrowing, int $days): void
    {
        if ($borrowing->status !== 'borrowed') {
            throw ValidationException::withMessages(['borrowing' => 'Only active loans can be extended.']);
        }

        $borrowing->update([
            'due_at' => $borrowing->due_at->copy()->addDays(max(1, $days)),
            'due_soon_notified_at' => null,
            'overdue_notified_at' => null,
        ]);
    }

    public function daysLate(Borrowing $borrowing, $returnedAt): int
    {
        $due = $borrowing->due_at->copy()->startOfDay();
        $returned = $returnedAt->copy()->startOfDay();

        return max(0, (int) floor(($returned->timestamp - $due->timestamp) / 86400));
    }

    private function removeCopyFromCirculation(Borrowing $borrowing): void
    {
        $book = $borrowing->book()->withTrashed()->first();

        if (! $book) {
            return;
        }

        $book->update([
            'total_copies' => max(0, $book->total_copies - 1),
            'available_copies' => min($book->available_copies, max(0, $book->total_copies - 1)),
        ]);
    }
}
