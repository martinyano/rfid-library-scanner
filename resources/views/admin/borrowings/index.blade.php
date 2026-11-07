@extends('layouts.admin')

@section('title', 'Borrowings')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="admin-page-title h3 mb-1">Borrowings</h1>
            <p class="text-muted-library mb-0">Loans, returns, and overdue records.</p>
        </div>
    </div>

    <div class="filter-bar mb-3">
        <form method="GET" action="{{ route('admin.borrowings.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-lg-6">
                <label class="form-label small fw-semibold">Search</label>
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control search-input" placeholder="Student name, student number, or book title">
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">All records</option>
                    <option value="borrowed" @selected(request('status') === 'borrowed')>On loan</option>
                    <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                    <option value="returned" @selected(request('status') === 'returned')>Returned</option>
                    <option value="lost" @selected(request('status') === 'lost')>Lost</option>
                    <option value="damaged" @selected(request('status') === 'damaged')>Damaged</option>
                </select>
            </div>
            <div class="col-6 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if (request()->hasAny(['q', 'status']))
                    <a href="{{ route('admin.borrowings.index') }}" class="btn btn-light border" title="Clear"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Student</th>
                        <th>Borrowed</th>
                        <th>Due</th>
                        <th>Returned</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($borrowings as $borrowing)
                        <tr>
                            <td>
                                <div class="fw-semibold small text-truncate" style="max-width: 240px;">{{ $borrowing->book?->title ?? 'Deleted book' }}</div>
                            </td>
                            <td class="small">
                                <a href="{{ route('admin.students.show', $borrowing->user) }}" class="text-dark fw-semibold">{{ $borrowing->user?->name ?? 'Deleted student' }}</a>
                                <div class="text-muted-library">{{ $borrowing->user?->student_number }}</div>
                            </td>
                            <td class="small">{{ $borrowing->borrowed_at->format('M d, Y') }}</td>
                            <td class="small {{ $borrowing->is_overdue ? 'text-danger fw-semibold' : '' }}">
                                {{ $borrowing->due_at->format('M d, Y') }}
                                @if ($borrowing->is_overdue)
                                    <div class="small">{{ $borrowing->overdue_days }} day(s) late</div>
                                @endif
                            </td>
                            <td class="small">{{ $borrowing->returned_at?->format('M d, Y') ?? '-' }}</td>
                            <td><span class="badge {{ $borrowing->status_badge }}">{{ $borrowing->status_label }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.borrowings.show', $borrowing) }}" class="btn btn-sm btn-light border" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if ($borrowing->status === 'borrowed')
                                    <form method="POST" action="{{ route('admin.borrowings.return', $borrowing) }}" class="d-inline" onsubmit="return confirm('Mark this book as returned?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Mark returned"><i class="bi bi-check2-circle"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted-library py-5">
                                <i class="bi bi-journal-x d-block fs-3 mb-2"></i>
                                No borrowing records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($borrowings->hasPages())
            <div class="card-footer bg-white">{{ $borrowings->links() }}</div>
        @endif
    </div>
@endsection
