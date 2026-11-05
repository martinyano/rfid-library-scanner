@extends('layouts.app')

@section('title', 'My borrowings')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <div class="text-muted-library small fw-semibold text-uppercase mb-1">My account</div>
            <h1 class="h3 fw-bold mb-1">Borrowings and fines</h1>
            <p class="text-muted-library mb-0">Track due dates, overdue books, and outstanding fines.</p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-search me-1"></i> Browse catalog
        </a>
    </div>

    @if ($unpaidFines > 0)
        <div class="alert alert-warning d-flex align-items-start gap-2">
            <i class="bi bi-cash-coin mt-1"></i>
            <div>
                <strong>Unpaid fines: {{ config('library.currency') }} {{ number_format($unpaidFines, 2) }}</strong>
                <div class="small">Please settle this amount at the library desk.</div>
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-journal-check me-1"></i> Active loans</span>
            <span class="badge badge-soft-info">{{ $activeLoans->count() }} active</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Borrowed</th>
                        <th>Due date</th>
                        <th>Time left</th>
                        <th>Status</th>
                        <th class="text-end">Estimated fine</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activeLoans as $loan)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $loan->book?->title ?? 'Deleted book' }}</div>
                                <div class="small text-muted-library">{{ $loan->book?->author?->name }}</div>
                            </td>
                            <td class="small">{{ $loan->borrowed_at->format('M d, Y') }}</td>
                            <td class="small">{{ $loan->due_at->format('M d, Y') }}</td>
                            <td class="small">
                                @if ($loan->is_overdue)
                                    <span class="text-danger fw-semibold">{{ $loan->overdue_days }} day(s) overdue</span>
                                @else
                                    {{ $loan->days_left }} day(s) left
                                @endif
                            </td>
                            <td><span class="badge {{ $loan->status_badge }}">{{ $loan->status_label }}</span></td>
                            <td class="text-end small">{{ config('library.currency') }} {{ number_format($loan->estimated_fine, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state py-4">
                                    <i class="bi bi-journal"></i>
                                    <div class="fw-semibold">No active loans</div>
                                    <div class="small">Books you borrow will appear here.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($activeLoans->isNotEmpty())
            <div class="card-footer bg-white small text-muted-library">
                <i class="bi bi-info-circle me-1"></i> Return books at the library desk. The librarian will mark them as returned and compute any fines.
            </div>
        @endif
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-receipt me-1"></i> Fines</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Book</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fines as $fine)
                        <tr>
                            <td class="small">{{ $fine->created_at->format('M d, Y') }}</td>
                            <td class="small">{{ $fine->borrowing?->book?->title ?? 'Deleted book' }}</td>
                            <td class="small">{{ $fine->type_label }}</td>
                            <td class="small fw-semibold">{{ config('library.currency') }} {{ number_format((float) $fine->amount, 2) }}</td>
                            <td><span class="badge {{ $fine->status_badge }}">{{ ucfirst($fine->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state py-4">
                                    <i class="bi bi-emoji-smile"></i>
                                    <div class="fw-semibold">No fines on record</div>
                                    <div class="small">Keep returning books on time to keep it that way.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-clock-history me-1"></i> Borrowing history</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Borrowed</th>
                        <th>Returned</th>
                        <th>Due date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $loan)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $loan->book?->title ?? 'Deleted book' }}</div>
                                <div class="small text-muted-library">{{ $loan->book?->author?->name }}</div>
                            </td>
                            <td class="small">{{ $loan->borrowed_at->format('M d, Y') }}</td>
                            <td class="small">{{ $loan->returned_at?->format('M d, Y') ?? '-' }}</td>
                            <td class="small">{{ $loan->due_at->format('M d, Y') }}</td>
                            <td><span class="badge {{ $loan->status_badge }}">{{ $loan->status_label }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted-library py-4">No completed loans yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($history->hasPages())
            <div class="card-footer bg-white">{{ $history->links() }}</div>
        @endif
    </div>
@endsection
