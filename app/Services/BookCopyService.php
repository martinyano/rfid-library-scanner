<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Validation\ValidationException;

class BookCopyService
{
    public function createForBook(Book $book, int $count, string $status = BookCopy::STATUS_AVAILABLE): void
    {
        for ($i = 0; $i < $count; $i++) {
            BookCopy::query()->create([
                'book_id' => $book->id,
                'status' => $status,
            ]);
        }

        $this->syncCounters($book);
    }

    public function add(Book $book, ?string $rfidUid = null, string $status = BookCopy::STATUS_AVAILABLE): BookCopy
    {
        $copy = BookCopy::query()->create([
            'book_id' => $book->id,
            'rfid_uid' => $rfidUid,
            'status' => $status,
        ]);

        $this->syncCounters($book);

        return $copy;
    }

    public function remove(BookCopy $copy): void
    {
        if ($copy->status === BookCopy::STATUS_BORROWED) {
            throw ValidationException::withMessages([
                'copy' => 'A borrowed copy cannot be removed. Mark the loan as returned first.',
            ]);
        }

        $book = $copy->book;

        $copy->delete();

        if ($book) {
            $this->syncCounters($book);
        }
    }

    public function adjustCount(Book $book, int $target): void
    {
        $target = max(0, $target);
        $current = $book->copies()
            ->whereIn('status', [BookCopy::STATUS_AVAILABLE, BookCopy::STATUS_BORROWED])
            ->count();

        if ($target > $current) {
            $this->createForBook($book, $target - $current);

            return;
        }

        if ($target < $current) {
            $remove = $current - $target;
            $available = $book->copies()
                ->where('status', BookCopy::STATUS_AVAILABLE)
                ->count();

            if ($available < $remove) {
                throw ValidationException::withMessages([
                    'total_copies' => 'Total copies cannot be reduced below the number of active loans.',
                ]);
            }

            $ids = $book->copies()
                ->where('status', BookCopy::STATUS_AVAILABLE)
                ->orderByDesc('id')
                ->limit($remove)
                ->pluck('id');

            BookCopy::query()->whereKey($ids)->delete();
        }

        $this->syncCounters($book);
    }

    public function syncCounters(Book $book): void
    {
        $available = $book->copies()
            ->where('status', BookCopy::STATUS_AVAILABLE)
            ->count();

        $total = $book->copies()
            ->whereIn('status', [BookCopy::STATUS_AVAILABLE, BookCopy::STATUS_BORROWED])
            ->count();

        $book->forceFill([
            'total_copies' => $total,
            'available_copies' => $available,
        ])->save();
    }
}
