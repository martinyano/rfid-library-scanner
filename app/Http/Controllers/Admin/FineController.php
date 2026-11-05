<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use Illuminate\Http\Request;

class FineController extends Controller
{
    public function index(Request $request)
    {
        $fines = Fine::query()
            ->with(['user', 'borrowing.book'])
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->input('type'), fn ($query, $type) => $query->where('type', $type))
            ->when($request->string('q')->trim()->toString(), function ($query, $q) {
                $query->whereHas('user', function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('student_number', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.fines.index', [
            'fines' => $fines,
            'unpaidTotal' => (float) Fine::query()->unpaid()->sum('amount'),
            'paidTotal' => (float) Fine::query()->where('status', 'paid')->sum('amount'),
            'waivedTotal' => (float) Fine::query()->where('status', 'waived')->sum('amount'),
        ]);
    }

    public function pay(Request $request, Fine $fine)
    {
        if ($fine->status !== 'unpaid') {
            return back()->with('error', 'This fine is not pending payment.');
        }

        $fine->update([
            'status' => 'paid',
            'paid_at' => now(),
            'handled_by_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Fine marked as paid.');
    }

    public function waive(Request $request, Fine $fine)
    {
        if ($fine->status === 'waived') {
            return back()->with('error', 'This fine is already waived.');
        }

        $fine->update([
            'status' => 'waived',
            'paid_at' => null,
            'handled_by_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Fine waived.');
    }
}
