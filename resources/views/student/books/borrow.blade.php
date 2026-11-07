@extends('layouts.app')

@section('title', 'Borrow '.$book->title)

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Catalog</a></li>
            <li class="breadcrumb-item"><a href="{{ route('student.books.show', $book) }}">{{ Str::limit($book->title, 40) }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Borrow</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-journal-plus me-1"></i> Confirm borrowing
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-3 mb-4">
                        <div class="detail-cover" style="width: 5.5rem; flex: 0 0 5.5rem; aspect-ratio: 3 / 4;">
                            @if ($book->cover_url)
                                <img src="{{ $book->cover_url }}" alt="Cover of {{ $book->title }}">
                            @else
                                <i class="bi bi-book-half" style="font-size: 2rem;"></i>
                            @endif
                        </div>
                        <div>
                            <h1 class="h5 fw-bold mb-1">{{ $book->title }}</h1>
                            <div class="small text-muted-library mb-1"><i class="bi bi-person me-1"></i>{{ $book->author?->name ?? 'Unknown author' }}</div>
                            <span class="badge badge-soft-primary">{{ $book->category?->name ?? 'Uncategorized' }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('student.books.borrow', $book) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Student name</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Student number</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->student_number ?: 'Not set' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">RFID card</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->rfid_uid ?: 'Not set' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Book</label>
                                <input type="text" class="form-control" value="{{ $book->title }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Date borrowed</label>
                                <input type="text" class="form-control" value="{{ today()->format('M d, Y') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Expected return date</label>
                                <input type="text" class="form-control" value="{{ $dueDate->format('M d, Y') }}" readonly>
                            </div>
                        </div>

                        <div class="rule-pill mt-4">
                            <i class="bi bi-info-circle me-1"></i>
                            These details are filled from your account. The final due date and availability are validated by the system when you confirm.
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i> Confirm borrow
                            </button>
                            <a href="{{ route('student.books.show', $book) }}" class="btn btn-light border">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><i class="bi bi-clipboard-check me-1"></i> Borrowing rules</div>
                <div class="card-body">
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-0 small">
                        <li class="d-flex gap-2"><i class="bi bi-calendar-range text-primary mt-1"></i><span>Loan period: <strong>{{ $borrowDays }} days</strong> from the date borrowed.</span></li>
                        <li class="d-flex gap-2"><i class="bi bi-stack text-primary mt-1"></i><span>Return the book at the library desk so the librarian can process the return.</span></li>
                        <li class="d-flex gap-2"><i class="bi bi-envelope text-primary mt-1"></i><span>Email reminders are sent before the due date and when a book is overdue.</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
