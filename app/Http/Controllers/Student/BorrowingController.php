<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function __invoke(Request $request)
    {
        $student = $request->user();

        $activeLoans = $student->borrowings()
            ->with('book')
            ->where('status', 'borrowed')
            ->orderBy('due_at')
            ->get();

        $history = $student->borrowings()
            ->with('book')
            ->where('status', '!=', 'borrowed')
            ->latest('returned_at')
            ->paginate(10);

        return view('student.borrowings.index', [
            'activeLoans' => $activeLoans,
            'history' => $history,
        ]);
    }
}
