@extends('layouts.admin')

@section('title', 'Attendance')

@section('content')
    <div class="mb-4">
        <h1 class="admin-page-title h3 mb-1">Attendance</h1>
        <p class="text-muted-library mb-0">Library entry and exit logs from RFID scans.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-label">Inside now</div>
                    <div class="stat-value">{{ $insideNow }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-label">Today's entries</div>
                    <div class="stat-value">{{ $todayEntries }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-label">Today's exits</div>
                    <div class="stat-value">{{ $todayExits }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-bar mb-3">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label small fw-semibold">Search</label>
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control search-input" placeholder="Name, student number, or RFID">
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label small fw-semibold">Date</label>
                <input type="date" name="date" value="{{ $date === 'all' ? '' : $date }}" class="form-control">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold">Show</label>
                <select name="filter" class="form-select">
                    <option value="">All sessions</option>
                    <option value="open" @selected(request('filter') === 'open')>Still inside</option>
                </select>
            </div>
            <div class="col-12 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if (request()->hasAny(['q', 'date', 'filter']))
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-light border" title="Clear"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
        <div class="small mt-2">
            <a href="{{ route('admin.attendance.index', ['date' => 'all']) }}">Show all dates</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>RFID</th>
                        <th>Entry</th>
                        <th>Exit</th>
                        <th>Duration</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="small">
                                <a href="{{ route('admin.students.show', $log->user) }}" class="fw-semibold text-dark">{{ $log->user?->name ?? 'Deleted student' }}</a>
                                <div class="text-muted-library">{{ $log->user?->student_number }}</div>
                            </td>
                            <td><code class="small">{{ $log->rfid_uid }}</code></td>
                            <td class="small">{{ $log->entry_at->format('M d, Y h:i A') }}</td>
                            <td class="small">{{ $log->exit_at?->format('M d, Y h:i A') ?? '-' }}</td>
                            <td class="small">{{ $log->duration_label }}</td>
                            <td><span class="badge badge-soft-secondary">{{ $log->source }}</span></td>
                            <td>
                                @if ($log->is_open)
                                    <span class="attendance-live">Inside</span>
                                @elseif ($log->closed_automatically)
                                    <span class="badge badge-soft-warning">Auto-closed</span>
                                @else
                                    <span class="badge badge-soft-success">Closed</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($log->is_open)
                                    <form method="POST" action="{{ route('admin.attendance.close', $log) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light border" title="Close session"><i class="bi bi-box-arrow-right"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted-library py-5">
                                <i class="bi bi-rss d-block fs-3 mb-2"></i>
                                No attendance logs for this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer bg-white">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
