<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Services\BorrowingService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        $borrowings = Borrowing::query()
            ->with(['user', 'book', 'fine'])
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

    public function show(Borrowing $borrowing, SettingService $settings)
    {
        $borrowing->load(['user', 'book', 'fine.handledBy', 'handledBy']);

        return view('admin.borrowings.show', [
            'borrowing' => $borrowing,
            'lostFee' => $settings->float('lost_fee', 500),
            'damagedFee' => $settings->float('damaged_fee', 200),
        ]);
    }

    public function markReturned(Request $request, Borrowing $borrowing, BorrowingService $borrowings)
    {
        $fine = $borrowings->markReturned($borrowing, $request->user());

        $message = 'Book marked as returned.';
        if ($fine) {
            $message .= ' An overdue fine of '.number_format((float) $fine->amount, 2).' was recorded.';
        }

        return back()->with('success', $message);
    }

    public function markLost(Request $request, Borrowing $borrowing, BorrowingService $borrowings)
    {
        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        $fine = $borrowings->markLost($borrowing, isset($data['amount']) ? (float) $data['amount'] : null, $request->user());

        return back()->with('success', 'Book marked as lost. A fine of '.number_format((float) $fine->amount, 2).' was recorded.');
    }

    public function markDamaged(Request $request, Borrowing $borrowing, BorrowingService $borrowings)
    {
        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        $fine = $borrowings->markDamaged($borrowing, isset($data['amount']) ? (float) $data['amount'] : null, $request->user());

        return back()->with('success', 'Book marked as damaged. A fine of '.number_format((float) $fine->amount, 2).' was recorded.');
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
