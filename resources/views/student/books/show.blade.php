@extends('layouts.app')

@section('title', $book->title)

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Catalog</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($book->title, 45) }}</li>
        </ol>
    </nav>

    <div class="row g-4 g-lg-5">
        <div class="col-md-4 col-lg-3">
            <div class="detail-cover">
                @if ($book->cover_url)
                    <img src="{{ $book->cover_url }}" alt="Cover of {{ $book->title }}">
                @else
                    <i class="bi bi-book-half"></i>
                @endif
            </div>
        </div>
        <div class="col-md-8 col-lg-9">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge {{ $book->availability_badge }}">{{ $book->availability_label }}</span>
                <span class="badge badge-soft-primary">{{ $book->category?->name ?? 'Uncategorized' }}</span>
            </div>
            <h1 class="h2 fw-bold mb-2">{{ $book->title }}</h1>
            <p class="text-muted-library mb-3">
                <i class="bi bi-person me-1"></i>{{ $book->author?->name ?? 'Unknown author' }}
            </p>

            <div class="d-flex flex-wrap gap-2 mb-4">
                <span class="rule-pill"><strong>ISBN:</strong> {{ $book->isbn ?: 'Not set' }}</span>
                <span class="rule-pill"><strong>Year:</strong> {{ $book->published_year ?: 'Not set' }}</span>
                <span class="rule-pill"><strong>Shelf:</strong> {{ $book->shelf_location ?: 'Not set' }}</span>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-2"><i class="bi bi-text-paragraph me-1"></i> Description</h2>
                    <p class="mb-0 text-secondary" style="white-space: pre-line;">{{ $book->description ?: 'No description has been added for this book yet.' }}</p>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="rule-pill h-100">
                        <div class="small text-muted-library">Loan period</div>
                        <strong>{{ $borrowDays }} days</strong>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="rule-pill h-100">
                        <div class="small text-muted-library">Your active loans</div>
                        <strong>{{ $activeCount }} of {{ $maxBooks }}</strong>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="rule-pill h-100">
                        <div class="small text-muted-library">Unpaid fines</div>
                        <strong>{{ config('library.currency') }} {{ number_format($unpaidFines, 2) }}</strong>
                    </div>
                </div>
            </div>

            @if ($activeLoan)
                <div class="alert alert-info d-flex align-items-start gap-2">
                    <i class="bi bi-check-circle mt-1"></i>
                    <div>
                        <strong>You already have this book.</strong>
                        <div class="small">It is due on {{ $activeLoan->due_at->format('M d, Y') }}. Return it at the library desk when finished.</div>
                    </div>
                </div>
            @elseif ($canBorrowMessage)
                <div class="alert alert-warning d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle mt-1"></i>
                    <div>
                        <strong>Borrowing unavailable.</strong>
                        <div class="small">{{ $canBorrowMessage }}</div>
                    </div>
                </div>
            @else
                <a href="{{ route('student.books.borrow.form', $book) }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-journal-plus me-2"></i> Borrow this book
                </a>
            @endif
        </div>
    </div>
@endsection
