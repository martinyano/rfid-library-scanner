@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="admin-page-title h3 mb-1">Categories</h1>
            <p class="text-muted-library mb-0">Organize the catalog and power the student category filter.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add category
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="row g-2">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control search-input" placeholder="Search categories">
                    </div>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                    @if (request()->filled('q'))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light border">Clear</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Books</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td class="small text-muted-library">{{ Str::limit($category->description ?: 'No description', 70) }}</td>
                            <td><span class="badge badge-soft-primary">{{ $category->books_count }}</span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-light border" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete" @disabled($category->books_count > 0)>
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
                                    <i class="bi bi-tags"></i>
                                    <div class="fw-semibold">No categories yet</div>
                                    <div class="small">Add a category so books can be organized.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="card-footer bg-white">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
