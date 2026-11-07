@extends('layouts.admin')

@section('title', 'Borrowing #'.$borrowing->id)

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.borrowings.index') }}" class="small"><i class="bi bi-arrow-left me-1"></i> Back to borrowings</a>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
            <h1 class="admin-page-title h3 mb-0">Borrowing #{{ $borrowing->id }}</h1>
            <span class="badge {{ $borrowing->status_badge }}">{{ $borrowing->status_label }}</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-journal-text me-1"></i> Loan details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small text-muted-library">Book</div>
                            <div class="fw-semibold">{{ $borrowing->book?->title ?? 'Deleted book' }}</div>
                            @if ($borrowing->book)
                                <div class="small text-muted-library">{{ $borrowing->book->author?->name }}</div>
                                <a href="{{ route('admin.books.edit', $borrowing->book) }}" class="small">View in inventory</a>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted-library">Student</div>
                            <div class="fw-semibold">
                                <a href="{{ route('admin.students.show', $borrowing->user) }}" class="text-dark">{{ $borrowing->user?->name ?? 'Deleted student' }}</a>
                            </div>
                            <div class="small text-muted-library">{{ $borrowing->user?->student_number }} &middot; {{ $borrowing->user?->email }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted-library">Borrowed</div>
                            <div class="fw-semibold small">{{ $borrowing->borrowed_at->format('M d, Y') }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted-library">Due</div>
                            <div class="fw-semibold small {{ $borrowing->is_overdue ? 'text-danger' : '' }}">{{ $borrowing->due_at->format('M d, Y') }}</div>
                            @if ($borrowing->status === 'borrowed')
                                <div class="small text-muted-library">
                                    @if ($borrowing->is_overdue)
                                        {{ $borrowing->overdue_days }} day(s) overdue
                                    @else
                                        {{ $borrowing->days_left }} day(s) left
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted-library">Returned</div>
                            <div class="fw-semibold small">{{ $borrowing->returned_at?->format('M d, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted-library">Handled by</div>
                            <div class="fw-semibold small">{{ $borrowing->handledBy?->name ?? 'System' }}</div>
                        </div>
                        @if ($borrowing->notes)
                            <div class="col-12">
                                <div class="small text-muted-library">Notes</div>
                                <div class="small">{{ $borrowing->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-gear me-1"></i> Actions</div>
                <div class="card-body">
                    @if ($borrowing->status === 'borrowed')
                        <div class="d-grid gap-2">
                            <form method="POST" action="{{ route('admin.borrowings.return', $borrowing) }}" onsubmit="return confirm('Mark this book as returned?');">
                                @csrf
                                <button type="submit" class="btn btn-success w-100"><i class="bi bi-check2-circle me-1"></i> Mark as returned</button>
                            </form>
                        </div>

                        <hr>

                        <form method="POST" action="{{ route('admin.borrowings.extend', $borrowing) }}">
                            @csrf
                            <label for="days" class="form-label small fw-semibold">Extend due date</label>
                            <div class="input-group">
                                <input type="number" name="days" id="days" value="7" min="1" max="30" class="form-control" required>
                                <span class="input-group-text">days</span>
                                <button type="submit" class="btn btn-outline-primary">Extend</button>
                            </div>
                        </form>

                        <hr>

                        <form method="POST" action="{{ route('admin.borrowings.lost', $borrowing) }}" onsubmit="return confirm('Mark this book as lost?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-question-octagon me-1"></i> Report lost</button>
                        </form>

                        <form method="POST" action="{{ route('admin.borrowings.damaged', $borrowing) }}" class="mt-3" onsubmit="return confirm('Mark this book as damaged?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100"><i class="bi bi-exclamation-triangle me-1"></i> Report damaged</button>
                        </form>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-check2-circle fs-2 text-success"></i>
                            <div class="fw-semibold mt-2">No pending actions</div>
                            <div class="small text-muted-library">This loan is {{ $borrowing->status_label }}.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
