<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Services\BorrowingService;
use App\Services\FineService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, BorrowingService $borrowings, FineService $fines, SettingService $settings)
    {
        $student = $request->user();

        $books = Book::query()
            ->with(['author', 'category'])
            ->search($request->string('q')->trim()->toString())
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->integer('author_id'), fn ($query, $authorId) => $query->where('author_id', $authorId))
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $activeLoans = $student->borrowings()
            ->with('book')
            ->where('status', 'borrowed')
            ->orderBy('due_at')
            ->get();

        return view('student.dashboard', [
            'books' => $books,
            'categories' => Category::query()->orderBy('name')->get(),
            'authors' => Author::query()->orderBy('name')->get(),
            'activeLoans' => $activeLoans,
            'activeLoanCount' => $activeLoans->count(),
            'maxBooks' => $settings->int('max_books', 3),
            'borrowDays' => $settings->int('borrow_days', 7),
            'unpaidFines' => $fines->unpaidTotalFor($student),
            'borrowingBlocked' => $settings->bool('block_on_unpaid_fine') && $fines->hasUnpaidFines($student),
            'activeCount' => $borrowings->activeCount($student),
        ]);
    }
}
