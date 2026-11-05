@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="admin-page-title h3 mb-1">Library overview</h1>
            <p class="text-muted-library mb-0">{{ now()->format('l, F d, Y') }} • Real-time circulation snapshot</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add book
            </a>
            <a href="{{ route('admin.students.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-person-plus me-1"></i> Register student
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Books in system</div>
                        <div class="stat-value">{{ number_format($totalCopies) }}</div>
                        <div class="small text-muted-library">{{ number_format($totalTitles) }} title(s)</div>
                    </div>
                    <div class="stat-icon bg-soft-primary"><i class="bi bi-bookshelf"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Currently borrowed</div>
                        <div class="stat-value">{{ number_format($activeBorrowings) }}</div>
                        <div class="small text-muted-library">{{ number_format($availableCopies) }} available</div>
                    </div>
                    <div class="stat-icon bg-soft-teal"><i class="bi bi-arrow-left-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Overdue books</div>
                        <div class="stat-value text-danger">{{ number_format($overdueBorrowings) }}</div>
                        <div class="small text-muted-library">Needs follow-up</div>
                    </div>
                    <div class="stat-icon bg-soft-danger"><i class="bi bi-exclamation-octagon"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Unpaid fines</div>
                        <div class="stat-value">{{ number_format($unpaidFines, 2) }}</div>
                        <div class="small text-muted-library">{{ config('library.currency') }} total</div>
                    </div>
                    <div class="stat-icon bg-soft-amber"><i class="bi bi-receipt"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-soft-slate"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-value" style="font-size: 1.4rem;">{{ number_format($studentCount) }}</div>
                    <div class="stat-label">Students</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-soft-teal"><i class="bi bi-person-check"></i></div>
                <div>
                    <div class="stat-value" style="font-size: 1.4rem;">{{ number_format($activeSessions) }}</div>
                    <div class="stat-label">Inside now</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-soft-primary"><i class="bi bi-box-arrow-in-right"></i></div>
                <div>
                    <div class="stat-value" style="font-size: 1.4rem;">{{ number_format($todayEntries) }}</div>
                    <div class="stat-label">Entries today</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-soft-amber"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-value" style="font-size: 1.4rem;">{{ number_format($dueSoon->count()) }}</div>
                    <div class="stat-label">Due soon</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history me-1"></i> Recent borrowings</span>
                    <a href="{{ route('admin.borrowings.index') }}" class="small">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Book</th>
                                <th>Borrowed</th>
                                <th>Due</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentBorrowings as $borrowing)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.students.show', $borrowing->user_id) }}" class="fw-semibold text-dark">{{ $borrowing->user?->name ?? 'Unknown' }}</a>
                                        <div class="small text-muted-library">{{ $borrowing->user?->student_number }}</div>
                                    </td>
                                    <td class="small">{{ Str::limit($borrowing->book?->title ?? 'Deleted book', 30) }}</td>
                                    <td class="small">{{ $borrowing->borrowed_at->format('M d') }}</td>
                                    <td class="small">{{ $borrowing->due_at->format('M d') }}</td>
                                    <td><span class="badge {{ $borrowing->status_badge }}">{{ $borrowing->status_label }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted-library py-4">No borrowings yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-alarm me-1"></i> Due within {{ $dueSoonDays }} day(s)</span>
                    <span class="badge badge-soft-warning">{{ $dueSoon->count() }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($dueSoon as $borrowing)
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between gap-2">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $borrowing->book?->title ?? 'Deleted book' }}</div>
                                    <div class="small text-muted-library">{{ $borrowing->user?->name }}</div>
                                </div>
                                <span class="badge badge-soft-warning align-self-start">{{ $borrowing->days_left }} day(s)</span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-4">
                            <i class="bi bi-check2-circle"></i>
                            <div class="small">No books due soon.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-exclamation-octagon me-1"></i> Overdue loans</span>
                    <a href="{{ route('admin.borrowings.index', ['status' => 'overdue']) }}" class="small">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Book</th>
                                <th>Due date</th>
                                <th>Overdue</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($overdueLoans as $borrowing)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $borrowing->user?->name }}</div>
                                        <div class="small text-muted-library">{{ $borrowing->user?->student_number }}</div>
                                    </td>
                                    <td class="small">{{ Str::limit($borrowing->book?->title ?? 'Deleted book', 30) }}</td>
                                    <td class="small">{{ $borrowing->due_at->format('M d, Y') }}</td>
                                    <td><span class="badge badge-soft-danger">{{ $borrowing->overdue_days }} day(s)</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.borrowings.show', $borrowing) }}" class="btn btn-sm btn-light border">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted-library py-4">No overdue loans.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-rss me-1"></i> Recent RFID entries</span>
                    <a href="{{ route('admin.attendance.index') }}" class="small">View all</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($recentAttendance as $log)
                        <div class="list-group-item py-3 d-flex justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="avatar-circle" style="width: 2rem; height: 2rem; font-size: 0.68rem;">{{ $log->user?->initials ?? '?' }}</span>
                                <div class="min-w-0">
                                    <div class="small fw-semibold text-truncate">{{ $log->user?->name ?? 'Unknown' }}</div>
                                    <div class="small text-muted-library">{{ $log->entry_at->format('M d, h:i A') }}</div>
                                </div>
                            </div>
                            @if ($log->is_open)
                                <span class="attendance-live">Inside</span>
                            @else
                                <span class="badge badge-soft-secondary">Exited</span>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state py-4">
                            <i class="bi bi-rss"></i>
                            <div class="small">No scans logged yet.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
