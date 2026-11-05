<div class="card book-card h-100">
    <div class="p-3 pb-0">
        <div class="book-cover-wrap">
            @if ($book->cover_url)
                <img src="{{ $book->cover_url }}" alt="Cover of {{ $book->title }}" class="book-cover">
            @else
                <i class="bi bi-book-half"></i>
            @endif
        </div>
    </div>
    <div class="card-body d-flex flex-column p-3">
        <div class="d-flex flex-wrap gap-1 mb-2">
            <span class="badge {{ $book->availability_badge }}">{{ $book->availability_label }}</span>
            @if ($book->category)
                <span class="badge badge-soft-primary">{{ $book->category->name }}</span>
            @endif
        </div>
        <h6 class="book-title mb-1">{{ $book->title }}</h6>
        <p class="text-muted-library small mb-2">
            <i class="bi bi-person me-1"></i>{{ $book->author?->name ?? 'Unknown author' }}
        </p>
        <p class="small text-muted-library book-description-preview mb-3">
            {{ $book->description ?: 'No description available for this title yet.' }}
        </p>
        <div class="mt-auto d-grid">
            <a href="{{ route('student.books.show', $book) }}" class="btn btn-sm btn-outline-primary">
                View details <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
