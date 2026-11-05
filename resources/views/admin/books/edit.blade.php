@extends('layouts.admin')

@section('title', 'Edit book')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.books.index') }}" class="small"><i class="bi bi-arrow-left me-1"></i> Back to inventory</a>
        <h1 class="admin-page-title h3 mt-2 mb-1">Edit book</h1>
        <p class="text-muted-library mb-0">{{ $book->title }}</p>
    </div>

    <div class="alert alert-info d-flex align-items-start gap-2">
        <i class="bi bi-info-circle mt-1"></i>
        <div class="small">
            This book has <strong>{{ $book->active_borrowings_count }}</strong> active loan(s).
            Total copies cannot be set below that number.
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.books.update', $book) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.books._fields', ['book' => $book])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Update book</button>
                    <a href="{{ route('admin.books.index') }}" class="btn btn-light border">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
