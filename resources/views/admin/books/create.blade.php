@extends('layouts.admin')

@section('title', 'Add book')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.books.index') }}" class="small"><i class="bi bi-arrow-left me-1"></i> Back to inventory</a>
        <h1 class="admin-page-title h3 mt-2 mb-1">Add book</h1>
        <p class="text-muted-library mb-0">Add a title to the catalog and set the number of copies.</p>
    </div>

    @if ($authors->isEmpty() || $categories->isEmpty())
        <div class="alert alert-warning d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle mt-1"></i>
            <div>
                You need at least one author and one category before adding a book.
                <a href="{{ route('admin.authors.create') }}" class="alert-link">Add author</a> or
                <a href="{{ route('admin.categories.create') }}" class="alert-link">add category</a>.
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.books.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.books._fields', ['book' => null])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" @disabled($authors->isEmpty() || $categories->isEmpty())>
                        <i class="bi bi-check2 me-1"></i> Save book
                    </button>
                    <a href="{{ route('admin.books.index') }}" class="btn btn-light border">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
