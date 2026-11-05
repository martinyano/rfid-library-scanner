<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\FineService;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function __invoke(Request $request, FineService $fines)
    {
        $student = $request->user();

        $activeLoans = $student->borrowings()
            ->with('book')
            ->where('status', 'borrowed')
            ->orderBy('due_at')
            ->get();

        $history = $student->borrowings()
            ->with(['book', 'fine'])
            ->where('status', '!=', 'borrowed')
            ->latest('returned_at')
            ->paginate(10);

        return view('student.borrowings.index', [
            'activeLoans' => $activeLoans,
            'history' => $history,
            'unpaidFines' => $fines->unpaidTotalFor($student),
            'fines' => $student->fines()->with('borrowing.book')->latest()->get(),
        ]);
    }
}
