<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Services\BorrowingService;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        $borrowings = Borrowing::query()
            ->with(['user', 'book'])
            ->when($request->input('status') === 'overdue', fn ($query) => $query->overdue())
            ->when(in_array($request->input('status'), ['borrowed', 'returned', 'lost', 'damaged'], true), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->string('q')->trim()->toString(), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->whereHas('user', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%")
                            ->orWhere('student_number', 'like', "%{$q}%");
                    })->orWhereHas('book', fn ($query) => $query->where('title', 'like', "%{$q}%"));
                });
            })
            ->latest('borrowed_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.borrowings.index', compact('borrowings'));
    }

    public function show(Borrowing $borrowing)
    {
        $borrowing->load(['user', 'book', 'handledBy']);

        return view('admin.borrowings.show', compact('borrowing'));
    }

    public function markReturned(Request $request, Borrowing $borrowing, BorrowingService $borrowings)
    {
        $borrowings->markReturned($borrowing, $request->user());

        return back()->with('success', 'Book marked as returned.');
    }

    public function markLost(Request $request, Borrowing $borrowing, BorrowingService $borrowings)
    {
        $borrowings->markLost($borrowing, $request->user());

        return back()->with('success', 'Book marked as lost.');
    }

    public function markDamaged(Request $request, Borrowing $borrowing, BorrowingService $borrowings)
    {
        $borrowings->markDamaged($borrowing, $request->user());

        return back()->with('success', 'Book marked as damaged.');
    }

    public function extend(Request $request, Borrowing $borrowing, BorrowingService $borrowings)
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $borrowings->extend($borrowing, (int) $data['days']);

        return back()->with('success', 'Due date extended by '.$data['days'].' day(s).');
    }
}
