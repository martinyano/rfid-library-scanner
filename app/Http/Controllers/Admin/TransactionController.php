<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Transaction;
use App\Services\AdminNotificationService;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::query()
            ->with(['book', 'user', 'penaltyHandledBy'])
            ->whereNotNull('penalty')
            ->when(in_array($request->input('status'), ['unpaid', 'paid'], true), fn ($query) => $query->where('penalty_status', $request->input('status')))
            ->when($request->string('q')->trim()->toString(), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->whereHas('user', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%")
                            ->orWhere('student_number', 'like', "%{$q}%")
                            ->orWhere('rfid_uid', 'like', "%{$q}%");
                    })->orWhere('id', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'unpaidCount' => Transaction::query()->finesUnpaid()->count(),
            'unpaidTotal' => (float) Transaction::query()->finesUnpaid()->sum('penalty'),
            'paidTotal' => (float) Transaction::query()->finesPaid()->sum('penalty'),
        ]);
    }

    public function store(
        Request $request,
        Borrowing $borrowing,
        TransactionService $transactions,
        AdminNotificationService $notifications,
    ) {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $transaction = $transactions->recordFine(
            $borrowing,
            (float) $data['amount'],
            $request->user(),
            $data['notes'] ?? null
        );

        $notifications->fineRecorded($transaction);

        return back()->with('success', 'Fine recorded successfully.');
    }

    public function markPaid(Request $request, Transaction $transaction, TransactionService $transactions)
    {
        $transactions->markPaid($transaction, $request->user());

        return back()->with('success', 'Transaction marked as paid.');
    }
}
