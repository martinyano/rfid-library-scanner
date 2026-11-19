<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Borrowing;
use App\Models\User;
use App\Services\BorrowingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BorrowingController extends Controller
{
    public function create()
    {
        return view('admin.borrowings.create', [
            'students' => User::query()->students()->where('is_active', true)->orderBy('name')->get(),
            'books' => Book::query()->where('available_copies', '>', 0)->orderBy('title')->get(),
            'copies' => BookCopy::query()
                ->with('book')
                ->where('status', BookCopy::STATUS_AVAILABLE)
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request, BorrowingService $borrowings)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'book_id' => ['required', 'exists:books,id'],
            'book_copy_id' => ['nullable', 'exists:book_copies,id'],
            'rfid_uid' => ['nullable', 'string', 'max:100'],
        ]);

        $student = User::query()->students()->whereKey($data['student_id'])->firstOrFail();
        $book = Book::query()->whereKey($data['book_id'])->firstOrFail();
        $cardUid = filled($data['rfid_uid'] ?? null)
            ? strtoupper(preg_replace('/\s+/', '', trim($data['rfid_uid'])))
            : $student->rfid_uid;

        if (filled($data['book_copy_id'] ?? null)) {
            $copy = BookCopy::query()
                ->whereKey($data['book_copy_id'])
                ->where('book_id', $book->id)
                ->firstOrFail();

            if ($copy->status !== BookCopy::STATUS_AVAILABLE) {
                throw ValidationException::withMessages(['book_copy_id' => 'This copy is not available for borrowing.']);
            }

            $borrowing = $borrowings->borrowCopy($student, $copy, $cardUid, $copy->rfid_uid);
        } else {
            $borrowing = $borrowings->borrow($student, $book, $cardUid);
        }

        return redirect()->route('admin.borrowings.show', $borrowing)
            ->with('success', 'Borrowing recorded successfully.');
    }

    public function live(Request $request): JsonResponse
    {
        $after = max(0, (int) $request->input('after', 0));

        $borrowings = Borrowing::query()
            ->with(['user', 'book'])
            ->where('id', '>', $after)
            ->latest('id')
            ->limit(10)
            ->get();

        return response()->json([
            'html' => view('admin.borrowings.partials.live-items', ['borrowings' => $borrowings])->render(),
            'latestId' => (int) (Borrowing::query()->max('id') ?? 0),
            'counters' => [
                'activeBorrowings' => Borrowing::query()->active()->count(),
                'overdueBorrowings' => Borrowing::query()->overdue()->count(),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $borrowings = Borrowing::query()
            ->with(['user', 'book', 'copy'])
            ->when($request->input('status') === 'overdue', fn ($query) => $query->overdue())
            ->when(in_array($request->input('status'), ['borrowed', 'returned', 'lost', 'damaged'], true), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->string('q')->trim()->toString(), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->whereHas('user', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%")
                            ->orWhere('student_number', 'like', "%{$q}%");
                    })->orWhereHas('book', fn ($query) => $query->where('title', 'like', "%{$q}%"))
                        ->orWhere('student_rfid_uid', 'like', "%{$q}%")
                        ->orWhere('book_rfid_uid', 'like', "%{$q}%");
                });
            })
            ->latest('borrowed_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.borrowings.index', [
            'borrowings' => $borrowings,
            'recentLoans' => Borrowing::query()->with(['user', 'book'])->latest('id')->limit(8)->get(),
            'activeCount' => Borrowing::query()->active()->count(),
            'overdueCount' => Borrowing::query()->overdue()->count(),
        ]);
    }

    public function show(Borrowing $borrowing)
    {
        $borrowing->load(['user', 'book', 'copy', 'handledBy', 'transactions.handledBy']);

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
