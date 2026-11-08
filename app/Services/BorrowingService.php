<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowingService
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly BookCopyService $copies,
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

            $copy = $lockedBook->copies()
                ->where('status', BookCopy::STATUS_AVAILABLE)
                ->lockForUpdate()
                ->first();

            if (! $copy) {
                throw ValidationException::withMessages([
                    'borrow' => 'All copies of this book are currently borrowed.',
                ]);
            }

            return $this->createBorrowing($student, $lockedBook, $copy);
        });
    }

    /**
     * Borrow a specific physical copy, typically from an RFID book tag scan.
     *
     * @throws ValidationException
     */
    public function borrowCopy(User $student, BookCopy $copy): Borrowing
    {
        return DB::transaction(function () use ($student, $copy) {
            $lockedCopy = BookCopy::query()->whereKey($copy->id)->lockForUpdate()->firstOrFail();
            $lockedBook = Book::query()->whereKey($lockedCopy->book_id)->lockForUpdate()->firstOrFail();

            if ($message = $this->canBorrow($student, $lockedBook)) {
                throw ValidationException::withMessages(['borrow' => $message]);
            }

            if ($lockedCopy->status !== BookCopy::STATUS_AVAILABLE) {
                throw ValidationException::withMessages([
                    'borrow' => 'This copy is not available for borrowing.',
                ]);
            }

            return $this->createBorrowing($student, $lockedBook, $lockedCopy);
        });
    }

    private function createBorrowing(User $student, Book $book, BookCopy $copy): Borrowing
    {
        $borrowedAt = today();
        $dueAt = today()->addDays($this->settings->int('borrow_days', 7));

        $borrowing = Borrowing::query()->create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'book_copy_id' => $copy->id,
            'borrowed_at' => $borrowedAt,
            'due_at' => $dueAt,
            'status' => 'borrowed',
        ]);

        $copy->update(['status' => BookCopy::STATUS_BORROWED]);

        $this->copies->syncCounters($book);

        return $borrowing;
    }

    public function markReturned(Borrowing $borrowing, ?User $handledBy = null): void
    {
        DB::transaction(function () use ($borrowing, $handledBy) {
            $borrowing = Borrowing::query()->whereKey($borrowing->id)->lockForUpdate()->firstOrFail();

            if ($borrowing->status !== 'borrowed') {
                throw ValidationException::withMessages(['borrowing' => 'This loan is no longer active.']);
            }

            $copy = $this->lockedCopyFor($borrowing);

            $borrowing->update([
                'status' => 'returned',
                'returned_at' => today(),
                'handled_by_id' => $handledBy?->id,
            ]);

            if ($copy) {
                $copy->update(['status' => BookCopy::STATUS_AVAILABLE]);

                if ($copy->book) {
                    $this->copies->syncCounters($copy->book);
                }

                return;
            }

            $this->restoreLegacyCopy($borrowing);
        });
    }

    public function markLost(Borrowing $borrowing, ?User $handledBy = null): void
    {
        DB::transaction(function () use ($borrowing, $handledBy) {
            $borrowing = Borrowing::query()->whereKey($borrowing->id)->lockForUpdate()->firstOrFail();

            if ($borrowing->status !== 'borrowed') {
                throw ValidationException::withMessages(['borrowing' => 'This loan is no longer active.']);
            }

            $copy = $this->lockedCopyFor($borrowing);

            $borrowing->update([
                'status' => 'lost',
                'returned_at' => today(),
                'handled_by_id' => $handledBy?->id,
            ]);

            if ($copy) {
                $copy->update(['status' => BookCopy::STATUS_LOST]);

                if ($copy->book) {
                    $this->copies->syncCounters($copy->book);
                }

                return;
            }

            $this->removeLegacyCopyFromCirculation($borrowing);
        });
    }

    public function markDamaged(Borrowing $borrowing, ?User $handledBy = null): void
    {
        DB::transaction(function () use ($borrowing, $handledBy) {
            $borrowing = Borrowing::query()->whereKey($borrowing->id)->lockForUpdate()->firstOrFail();

            if ($borrowing->status !== 'borrowed') {
                throw ValidationException::withMessages(['borrowing' => 'This loan is no longer active.']);
            }

            $copy = $this->lockedCopyFor($borrowing);

            $borrowing->update([
                'status' => 'damaged',
                'returned_at' => today(),
                'handled_by_id' => $handledBy?->id,
            ]);

            if ($copy) {
                $copy->update(['status' => BookCopy::STATUS_DAMAGED]);

                if ($copy->book) {
                    $this->copies->syncCounters($copy->book);
                }

                return;
            }

            $this->removeLegacyCopyFromCirculation($borrowing);
        });
    }

    private function lockedCopyFor(Borrowing $borrowing): ?BookCopy
    {
        if ($borrowing->book_copy_id) {
            return BookCopy::query()
                ->whereKey($borrowing->book_copy_id)
                ->lockForUpdate()
                ->first();
        }

        return BookCopy::query()
            ->where('book_id', $borrowing->book_id)
            ->where('status', BookCopy::STATUS_BORROWED)
            ->lockForUpdate()
            ->first();
    }

    private function restoreLegacyCopy(Borrowing $borrowing): void
    {
        $book = $borrowing->book()->withTrashed()->first();

        if (! $book) {
            return;
        }

        $book->update([
            'available_copies' => min($book->total_copies, $book->available_copies + 1),
        ]);
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

    private function removeLegacyCopyFromCirculation(Borrowing $borrowing): void
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
