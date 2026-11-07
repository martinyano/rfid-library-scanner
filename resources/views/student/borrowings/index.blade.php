@extends('layouts.app')

@section('title', 'My borrowings')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <div class="text-muted-library small fw-semibold text-uppercase mb-1">My account</div>
            <h1 class="h3 fw-bold mb-1">My borrowings</h1>
            <p class="text-muted-library mb-0">Track due dates and overdue books.</p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-search me-1"></i> Browse catalog
        </a>
    </div>

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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
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
                <i class="bi bi-info-circle me-1"></i> Return books at the library desk so the librarian can process the return.
            </div>
        @endif
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
