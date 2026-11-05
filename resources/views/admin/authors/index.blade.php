@extends('layouts.admin')

@section('title', 'Authors')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="admin-page-title h3 mb-1">Authors</h1>
            <p class="text-muted-library mb-0">Manage the authors available in the book form dropdown.</p>
        </div>
        <a href="{{ route('admin.authors.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add author
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.authors.index') }}" class="row g-2">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control search-input" placeholder="Search authors">
                    </div>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                    @if (request()->filled('q'))
                        <a href="{{ route('admin.authors.index') }}" class="btn btn-light border">Clear</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Author</th>
                        <th>Bio</th>
                        <th>Books</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($authors as $author)
                        <tr>
                            <td class="fw-semibold">{{ $author->name }}</td>
                            <td class="small text-muted-library">{{ Str::limit($author->bio ?: 'No bio', 70) }}</td>
                            <td><span class="badge badge-soft-primary">{{ $author->books_count }}</span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.authors.edit', $author) }}" class="btn btn-sm btn-light border" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.authors.destroy', $author) }}" onsubmit="return confirm('Delete this author?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete" @disabled($author->books_count > 0)>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state py-4">
                                    <i class="bi bi-person-lines-fill"></i>
                                    <div class="fw-semibold">No authors yet</div>
                                    <div class="small">Add an author to start building the catalog.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($authors->hasPages())
            <div class="card-footer bg-white">{{ $authors->links() }}</div>
        @endif
    </div>
@endsection
