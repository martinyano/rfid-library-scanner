@extends('layouts.admin')

@section('title', 'Inventory')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="admin-page-title h3 mb-1">Book inventory</h1>
            <p class="text-muted-library mb-0">All titles, copies, and availability in the library.</p>
        </div>
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add book
        </a>
    </div>

    <div class="filter-bar mb-3">
        <form method="GET" action="{{ route('admin.books.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-xl-4">
                <label class="form-label small fw-semibold">Search</label>
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control search-input" placeholder="Title, author, ISBN">
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold">Author</label>
                <select name="author_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($authors as $author)
                        <option value="{{ $author->id }}" @selected((string) request('author_id') === (string) $author->id)>{{ $author->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold">Availability</label>
                <select name="availability" class="form-select">
                    <option value="">All</option>
                    <option value="available" @selected(request('availability') === 'available')>Available</option>
                    <option value="unavailable" @selected(request('availability') === 'unavailable')>All borrowed</option>
                </select>
            </div>
            <div class="col-6 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if (request()->hasAny(['q', 'author_id', 'category_id', 'availability']))
                    <a href="{{ route('admin.books.index') }}" class="btn btn-light border" title="Clear"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th class="text-center">Copies</th>
                        <th class="text-center">Available</th>
                        <th class="text-center">On loan</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="book-cover-wrap" style="width: 2.6rem; flex: 0 0 2.6rem; aspect-ratio: 3 / 4; border-radius: 0.35rem;">
                                        @if ($book->cover_url)
                                            <img src="{{ $book->cover_url }}" alt="" class="book-cover" style="border-radius: 0.35rem;">
                                        @else
                                            <i class="bi bi-book" style="font-size: 1rem;"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate" style="max-width: 260px;">{{ $book->title }}</div>
                                        <div class="small text-muted-library">{{ $book->isbn ?: 'No ISBN' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="small">{{ $book->author?->name }}</td>
                            <td class="small"><span class="badge badge-soft-primary">{{ $book->category?->name }}</span></td>
                            <td class="text-center">{{ $book->total_copies }}</td>
                            <td class="text-center">
                                <span class="badge {{ $book->availability_badge }}">{{ $book->available_copies }}</span>
                            </td>
                            <td class="text-center">{{ $book->active_borrowings_count }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-light border" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('admin.books.destroy', $book) }}" onsubmit="return confirm('Remove this book from inventory?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete" @disabled($book->active_borrowings_count > 0)>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state py-4">
                                    <i class="bi bi-book"></i>
                                    <div class="fw-semibold">No books found</div>
                                    <div class="small mb-3">Add a book or adjust your filters.</div>
                                    <a href="{{ route('admin.books.create') }}" class="btn btn-sm btn-primary">Add book</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($books->hasPages())
            <div class="card-footer bg-white">{{ $books->links() }}</div>
        @endif
    </div>
@endsection
