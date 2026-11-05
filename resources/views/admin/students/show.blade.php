@extends('layouts.admin')

@section('title', $student->name)

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.students.index') }}" class="small"><i class="bi bi-arrow-left me-1"></i> Back to students</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar-circle" style="width: 3.6rem; height: 3.6rem; font-size: 1.2rem;">{{ $student->initials }}</span>
                        <div>
                            <h1 class="h5 fw-bold mb-0">{{ $student->name }}</h1>
                            <div class="small text-muted-library">{{ $student->email }}</div>
                        </div>
                        <span class="badge ms-auto {{ $student->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                            {{ $student->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="row g-2 small">
                        <div class="col-12 d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted-library">Student number</span>
                            <span class="fw-semibold">{{ $student->student_number ?: '-' }}</span>
                        </div>
                        <div class="col-12 d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted-library">RFID card</span>
                            <code>{{ $student->rfid_uid }}</code>
                        </div>
                        <div class="col-12 d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted-library">Course</span>
                            <span class="fw-semibold">{{ $student->course ?: '-' }}</span>
                        </div>
                        <div class="col-12 d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted-library">Year level</span>
                            <span class="fw-semibold">{{ $student->year_level ?: '-' }}</span>
                        </div>
                        <div class="col-12 d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted-library">Phone</span>
                            <span class="fw-semibold">{{ $student->phone ?: '-' }}</span>
                        </div>
                        <div class="col-12 d-flex justify-content-between py-2">
                            <span class="text-muted-library">Unpaid fines</span>
                            <span class="fw-semibold {{ $unpaidFines > 0 ? 'text-danger' : '' }}">{{ config('library.currency') }} {{ number_format($unpaidFines, 2) }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i> Edit student</a>
                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Delete this student? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light border text-danger"><i class="bi bi-trash me-1"></i> Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-journal-check me-1"></i> Active loans</span>
                    <span class="badge badge-soft-info">{{ $activeLoans->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Book</th><th>Borrowed</th><th>Due</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($activeLoans as $loan)
                                <tr>
                                    <td class="small fw-semibold">{{ $loan->book?->title ?? 'Deleted book' }}</td>
                                    <td class="small">{{ $loan->borrowed_at->format('M d, Y') }}</td>
                                    <td class="small">{{ $loan->due_at->format('M d, Y') }}</td>
                                    <td><span class="badge {{ $loan->status_badge }}">{{ $loan->status_label }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted-library py-4">No active loans.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-clock-history me-1"></i> Borrowing history</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Book</th><th>Borrowed</th><th>Returned</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($student->borrowings as $loan)
                                <tr>
                                    <td class="small">{{ Str::limit($loan->book?->title ?? 'Deleted book', 35) }}</td>
                                    <td class="small">{{ $loan->borrowed_at->format('M d, Y') }}</td>
                                    <td class="small">{{ $loan->returned_at?->format('M d, Y') ?? '-' }}</td>
                                    <td><span class="badge {{ $loan->status_badge }}">{{ $loan->status_label }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted-library py-4">No borrowing history.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-receipt me-1"></i> Fines</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Type</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse ($student->fines as $fine)
                                <tr>
                                    <td class="small">{{ $fine->type_label }}</td>
                                    <td class="small fw-semibold">{{ config('library.currency') }} {{ number_format((float) $fine->amount, 2) }}</td>
                                    <td><span class="badge {{ $fine->status_badge }}">{{ ucfirst($fine->status) }}</span></td>
                                    <td class="small">{{ $fine->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted-library py-4">No fines on record.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-rss me-1"></i> Recent attendance</span>
                    <a href="{{ route('admin.attendance.index', ['q' => $student->student_number ?: $student->name]) }}" class="small">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Entry</th><th>Exit</th><th>Duration</th><th>Source</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($student->attendanceLogs as $log)
                                <tr>
                                    <td class="small">{{ $log->entry_at->format('M d, Y h:i A') }}</td>
                                    <td class="small">{{ $log->exit_at?->format('M d, Y h:i A') ?? '-' }}</td>
                                    <td class="small">{{ $log->duration_label }}</td>
                                    <td class="small"><span class="badge badge-soft-secondary">{{ $log->source }}</span></td>
                                    <td>
                                        @if ($log->is_open)
                                            <span class="attendance-live">Inside</span>
                                        @elseif ($log->closed_automatically)
                                            <span class="badge badge-soft-warning">Auto-closed</span>
                                        @else
                                            <span class="badge badge-soft-success">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted-library py-4">No attendance logs.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
