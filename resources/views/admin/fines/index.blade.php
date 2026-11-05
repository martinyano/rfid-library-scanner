@extends('layouts.admin')

@section('title', 'Fines')

@section('content')
    <div class="mb-4">
        <h1 class="admin-page-title h3 mb-1">Fines</h1>
        <p class="text-muted-library mb-0">Track and settle overdue, lost, and damaged book charges.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-label">Unpaid</div>
                    <div class="stat-value text-danger">{{ config('library.currency') }} {{ number_format($unpaidTotal, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-label">Collected</div>
                    <div class="stat-value">{{ config('library.currency') }} {{ number_format($paidTotal, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-label">Waived</div>
                    <div class="stat-value text-muted-library">{{ config('library.currency') }} {{ number_format($waivedTotal, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-bar mb-3">
        <form method="GET" action="{{ route('admin.fines.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-xl-5">
                <label class="form-label small fw-semibold">Search</label>
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control search-input" placeholder="Student name or student number">
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                    <option value="waived" @selected(request('status') === 'waived')>Waived</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold">Type</label>
                <select name="type" class="form-select">
                    <option value="">All</option>
                    <option value="overdue" @selected(request('type') === 'overdue')>Overdue</option>
                    <option value="lost" @selected(request('type') === 'lost')>Lost</option>
                    <option value="damaged" @selected(request('type') === 'damaged')>Damaged</option>
                </select>
            </div>
            <div class="col-12 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if (request()->hasAny(['q', 'status', 'type']))
                    <a href="{{ route('admin.fines.index') }}" class="btn btn-light border" title="Clear"><i class="bi bi-x-lg"></i></a>
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
                        <th>Book</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Recorded</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fines as $fine)
                        <tr>
                            <td class="small">
                                <a href="{{ route('admin.students.show', $fine->user) }}" class="fw-semibold text-dark">{{ $fine->user?->name }}</a>
                                <div class="text-muted-library">{{ $fine->user?->student_number }}</div>
                            </td>
                            <td class="small">
                                <a href="{{ route('admin.borrowings.show', $fine->borrowing) }}" class="text-dark">{{ Str::limit($fine->borrowing?->book?->title ?? 'Deleted book', 35) }}</a>
                            </td>
                            <td><span class="badge badge-soft-primary">{{ $fine->type_label }}</span></td>
                            <td class="small fw-semibold">{{ config('library.currency') }} {{ number_format((float) $fine->amount, 2) }}</td>
                            <td><span class="badge {{ $fine->status_badge }}">{{ ucfirst($fine->status) }}</span></td>
                            <td class="small">{{ $fine->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                @if ($fine->status === 'unpaid')
                                    <div class="d-inline-flex gap-1">
                                        <form method="POST" action="{{ route('admin.fines.pay', $fine) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Mark paid"><i class="bi bi-cash-coin"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.fines.waive', $fine) }}" onsubmit="return confirm('Waive this fine?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border" title="Waive"><i class="bi bi-slash-circle"></i></button>
                                        </form>
                                    </div>
                                @else
                                    <span class="small text-muted-library">{{ $fine->paid_at?->format('M d, Y') ?? '-' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted-library py-5">
                                <i class="bi bi-receipt d-block fs-3 mb-2"></i>
                                No fines found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($fines->hasPages())
            <div class="card-footer bg-white">{{ $fines->links() }}</div>
        @endif
    </div>
@endsection
