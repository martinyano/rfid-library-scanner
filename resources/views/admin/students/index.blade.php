@extends('layouts.admin')

@section('title', 'Students')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="admin-page-title h3 mb-1">Students</h1>
            <p class="text-muted-library mb-0">Register RFID cards and manage student accounts.</p>
        </div>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Register student
        </a>
    </div>

    <div class="filter-bar mb-3">
        <form method="GET" action="{{ route('admin.students.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-lg-6">
                <label class="form-label small fw-semibold">Search</label>
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control search-input" placeholder="Name, email, student number, or RFID">
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">All students</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-6 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if (request()->hasAny(['q', 'status']))
                    <a href="{{ route('admin.students.index') }}" class="btn btn-light border" title="Clear"><i class="bi bi-x-lg"></i></a>
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
                        <th>Student no.</th>
                        <th>RFID card</th>
                        <th>Course</th>
                        <th class="text-center">Active loans</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-circle">{{ $student->initials }}</span>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.students.show', $student) }}" class="fw-semibold text-dark">{{ $student->name }}</a>
                                        <div class="small text-muted-library">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="small">{{ $student->student_number ?: '-' }}</td>
                            <td><code class="small">{{ $student->rfid_uid }}</code></td>
                            <td class="small">{{ $student->course ?: '-' }}</td>
                            <td class="text-center"><span class="badge badge-soft-info">{{ $student->active_loans_count }}</span></td>
                            <td>
                                <span class="badge {{ $student->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                    {{ $student->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-light border" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state py-4">
                                    <i class="bi bi-people"></i>
                                    <div class="fw-semibold">No students found</div>
                                    <div class="small mb-3">Register a student to issue an RFID login.</div>
                                    <a href="{{ route('admin.students.create') }}" class="btn btn-sm btn-primary">Register student</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($students->hasPages())
            <div class="card-footer bg-white">{{ $students->links() }}</div>
        @endif
    </div>
@endsection
