<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\BorrowingService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function show(Request $request, Book $book, BorrowingService $borrowings, SettingService $settings)
    {
        $book->load(['author', 'category']);

        return view('student.books.show', [
            'book' => $book,
            'canBorrowMessage' => $borrowings->canBorrow($request->user(), $book),
            'activeLoan' => $request->user()->borrowings()
                ->where('book_id', $book->id)
                ->where('status', 'borrowed')
                ->first(),
            'activeCount' => $borrowings->activeCount($request->user()),
            'maxBooks' => $settings->int('max_books', 3),
            'borrowDays' => $settings->int('borrow_days', 7),
        ]);
    }

    public function borrowForm(Request $request, Book $book, BorrowingService $borrowings, SettingService $settings)
    {
        $book->load(['author', 'category']);

        if ($message = $borrowings->canBorrow($request->user(), $book)) {
            return redirect()->route('student.books.show', $book)->with('error', $message);
        }

        return view('student.books.borrow', [
            'book' => $book,
            'borrowDays' => $settings->int('borrow_days', 7),
            'dueDate' => today()->addDays($settings->int('borrow_days', 7)),
        ]);
    }

    public function borrow(Request $request, Book $book, BorrowingService $borrowings)
    {
        $borrowing = $borrowings->borrow($request->user(), $book);

        return redirect()->route('student.borrowings.index')
            ->with('success', 'Book borrowed successfully. Please return it by '.$borrowing->due_at->format('M d, Y').'.');
    }
}
