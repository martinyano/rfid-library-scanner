@foreach ($borrowings as $borrowing)
    <div class="list-group-item px-0 py-2 d-flex align-items-center gap-2" data-live-item="{{ $borrowing->id }}">
        <span class="stat-icon bg-soft-primary" style="width: 1.9rem; height: 1.9rem; font-size: 0.72rem;"><i class="bi bi-journal-arrow-up"></i></span>
        <div class="min-w-0 flex-grow-1">
            <div class="small fw-semibold text-truncate">
                {{ $borrowing->user?->name ?? 'Unknown student' }}
                <span class="text-muted-library fw-normal">&middot; {{ $borrowing->status_label }}</span>
            </div>
            <div class="small text-muted-library text-truncate">
                {{ Str::limit($borrowing->book?->title ?? 'Deleted book', 45) }}
                @if ($borrowing->student_rfid_uid || $borrowing->book_rfid_uid)
                    &middot; <code>{{ $borrowing->student_rfid_uid ?: 'card?' }}</code> &rarr; <code>{{ $borrowing->book_rfid_uid ?: 'book?' }}</code>
                @endif
            </div>
        </div>
        <div class="text-end">
            <span class="badge {{ $borrowing->status_badge }}">{{ $borrowing->status_label }}</span>
            <div class="small text-muted-library">Due {{ $borrowing->due_at->format('M d') }}</div>
        </div>
        <a href="{{ route('admin.borrowings.show', $borrowing) }}" class="btn btn-sm btn-light border" title="Open"><i class="bi bi-eye"></i></a>
    </div>
@endforeach
