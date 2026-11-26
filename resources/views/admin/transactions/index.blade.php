@extends('layouts.admin')

@section('title', 'Transactions')

@section('content')
    <div class="mb-4">
        <h1 class="admin-page-title h3 mb-1">Transactions</h1>
        <p class="text-muted-library mb-0">Overdue fines recorded by administrators and their payment status.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-label">Unpaid fines</div>
                    <div class="stat-value text-danger">{{ number_format($unpaidTotal, 2) }}</div>
                    <div class="small text-muted-library">{{ $unpaidCount }} transaction(s)</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-label">Paid total</div>
                    <div class="stat-value">{{ number_format($paidTotal, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-bar mb-3">
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-lg-6">
                <label class="form-label small fw-semibold">Search</label>
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control search-input" placeholder="Student, RFID, or transaction ID">
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">All transactions</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                </select>
            </div>
            <div class="col-6 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if (request()->hasAny(['q', 'status']))
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-light border" title="Clear"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Book / borrowing</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Recorded</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="small">
                                <a href="{{ route('admin.students.show', $transaction->user) }}" class="fw-semibold text-dark">{{ $transaction->user?->name ?? 'Deleted student' }}</a>
                                <div class="text-muted-library">{{ $transaction->user?->student_number }}</div>
                            </td>
                            <td class="small">
                                <a href="{{ route('admin.borrowings.show', $transaction) }}" class="text-dark">{{ Str::limit($transaction->book?->title ?? 'Deleted book', 40) }}</a>
                                <div class="text-muted-library">
                                    #{{ $transaction->id }}
                                    @if ($transaction->student_rfid_uid)<code>{{ $transaction->student_rfid_uid }}</code>@endif
                                </div>
                                @if ($transaction->penalty_notes)
                                    <div class="text-muted-library">{{ Str::limit($transaction->penalty_notes, 50) }}</div>
                                @endif
                            </td>
                            <td class="fw-semibold small">{{ number_format($transaction->penalty, 2) }}</td>
                            <td><span class="badge {{ $transaction->penalty_status_badge }}">{{ $transaction->penalty_status_label }}</span></td>
                            <td class="small">
                                {{ $transaction->created_at->format('M d, Y') }}
                                <div class="text-muted-library">{{ $transaction->penaltyHandledBy?->name ?? 'System' }}</div>
                            </td>
                            <td class="text-end">
                                @if ($transaction->penalty_status === 'unpaid')
                                    <form method="POST" action="{{ route('admin.transactions.pay', $transaction) }}" onsubmit="return confirm('Mark this fine as paid?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-cash me-1"></i> Mark paid</button>
                                    </form>
                                @else
                                    <span class="small text-muted-library">{{ $transaction->penalty_paid_at?->format('M d, Y') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted-library py-5">
                                <i class="bi bi-cash-coin d-block fs-3 mb-2"></i>
                                No transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transactions->hasPages())
            <div class="card-footer bg-white">{{ $transactions->links() }}</div>
        @endif
    </div>
@endsection
