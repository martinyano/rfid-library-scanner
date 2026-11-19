@extends('layouts.admin')

@section('title', 'New borrowing')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.borrowings.index') }}" class="small"><i class="bi bi-arrow-left me-1"></i> Back to borrowings</a>
        <h1 class="admin-page-title h3 mt-2 mb-1">Record a borrowing</h1>
        <p class="text-muted-library mb-0">Use this form when a card or book tag cannot be scanned.</p>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.borrowings.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="student_id" class="form-label small fw-semibold">Student</label>
                        <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">Select student</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                    {{ $student->name }} @if ($student->student_number)({{ $student->student_number }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="rfid_uid" class="form-label small fw-semibold">Card UID used <span class="text-muted-library">(optional)</span></label>
                        <input type="text" name="rfid_uid" id="rfid_uid" value="{{ old('rfid_uid') }}" class="form-control @error('rfid_uid') is-invalid @enderror" maxlength="100" placeholder="Defaults to the student's registered card">
                        @error('rfid_uid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="book_id" class="form-label small fw-semibold">Book</label>
                        <select name="book_id" id="book_id" class="form-select @error('book_id') is-invalid @enderror" required>
                            <option value="">Select available book</option>
                            @foreach ($books as $book)
                                <option value="{{ $book->id }}" data-title="{{ $book->title }}" @selected(old('book_id') == $book->id)>{{ $book->title }}</option>
                            @endforeach
                        </select>
                        @error('book_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="book_copy_id" class="form-label small fw-semibold">Physical copy <span class="text-muted-library">(optional)</span></label>
                        <select name="book_copy_id" id="book_copy_id" class="form-select @error('book_copy_id') is-invalid @enderror">
                            <option value="">Let the system choose</option>
                            @foreach ($copies as $copy)
                                <option value="{{ $copy->id }}" data-book="{{ $copy->book_id }}" @selected(old('book_copy_id') == $copy->id) hidden>
                                    {{ $copy->book?->title }} &middot; {{ $copy->rfid_uid ?: 'No tag' }}
                                </option>
                            @endforeach
                        </select>
                        @error('book_copy_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-journal-plus me-1"></i> Record borrowing</button>
                    <a href="{{ route('admin.borrowings.index') }}" class="btn btn-light border">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const book = document.getElementById('book_id');
                const copy = document.getElementById('book_copy_id');
                if (!book || !copy) return;

                const filter = () => {
                    Array.from(copy.options).forEach((option) => {
                        if (!option.value) return;
                        option.hidden = option.dataset.book !== book.value;
                    });
                    if (copy.selectedOptions[0]?.hidden) copy.value = '';
                };

                book.addEventListener('change', filter);
                filter();
            });
        </script>
    @endpush
@endsection
