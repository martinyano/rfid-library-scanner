@extends('layouts.app')

@section('title', 'Library catalog')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <div class="text-muted-library small fw-semibold text-uppercase mb-1">Student dashboard</div>
            <h1 class="h3 fw-bold mb-1">Welcome, {{ auth()->user()->name }}</h1>
            <p class="text-muted-library mb-0">
                {{ auth()->user()->course ? auth()->user()->course.' • ' : '' }}{{ auth()->user()->student_number ?: 'No student number on file' }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student.borrowings.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-journal-text me-1"></i> My borrowings
            </a>
            <form method="POST" action="{{ route('student.logout') }}">
                @csrf
                <button type="submit" class="btn btn-light border text-danger">
                    <i class="bi bi-box-arrow-right me-1"></i> Log out
                </button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Active loans</div>
                        <div class="stat-value">{{ $activeLoanCount }}</div>
                    </div>
                    <div class="stat-icon bg-soft-primary"><i class="bi bi-journal-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Borrowing slots</div>
                        <div class="stat-value">{{ max(0, $maxBooks - $activeCount) }}</div>
                    </div>
                    <div class="stat-icon bg-soft-teal"><i class="bi bi-stack"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Loan period</div>
                        <div class="stat-value">{{ $borrowDays }} days</div>
                    </div>
                    <div class="stat-icon bg-soft-amber"><i class="bi bi-calendar-range"></i></div>
                </div>
            </div>
        </div>
    </div>

    @if ($activeLoans->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bookmark-check me-1"></i> Books you currently have</span>
                <a href="{{ route('student.borrowings.index') }}" class="small">View all</a>
            </div>
            <div class="list-group list-group-flush">
                @foreach ($activeLoans as $loan)
                    <div class="list-group-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-soft-primary" style="width: 2.6rem; height: 2.6rem; font-size: 1.1rem;">
                                <i class="bi bi-book"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $loan->book?->title ?? 'Deleted book' }}</div>
                                <div class="small text-muted-library">
                                    Due {{ $loan->due_at->format('M d, Y') }}
                                    @if ($loan->is_overdue)
                                        <span class="text-danger fw-semibold">• {{ $loan->overdue_days }} day(s) overdue</span>
                                    @else
                                        <span>• {{ $loan->days_left }} day(s) left</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span class="badge {{ $loan->status_badge }}">{{ $loan->status_label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="filter-bar mb-4">
        <form method="GET" action="{{ route('student.dashboard') }}" class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label small fw-semibold">Search</label>
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control search-input" placeholder="Title, author, ISBN, or category">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small fw-semibold">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small fw-semibold">Author</label>
                <select name="author_id" class="form-select">
                    <option value="">All authors</option>
                    @foreach ($authors as $author)
                        <option value="{{ $author->id }}" @selected((string) request('author_id') === (string) $author->id)>{{ $author->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if (request()->filled('q') || request()->filled('category_id') || request()->filled('author_id'))
                    <a href="{{ route('student.dashboard') }}" class="btn btn-light border" title="Clear filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 fw-bold mb-0">Library catalog</h2>
            <div class="small text-muted-library">{{ $books->total() }} book{{ $books->total() === 1 ? '' : 's' }} found</div>
        </div>
    </div>

    @if ($books->isEmpty())
        <div class="card">
            <div class="empty-state">
                <i class="bi bi-book"></i>
                <h3 class="h6 fw-bold">No books found</h3>
                <p class="small mb-3">Try a different search term or clear your filters.</p>
                <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-primary">Clear filters</a>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach ($books as $book)
                <div class="col-6 col-md-4 col-xl-3">
                    @include('partials.book-card', ['book' => $book])
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $books->links() }}
        </div>
    @endif
@endsection
