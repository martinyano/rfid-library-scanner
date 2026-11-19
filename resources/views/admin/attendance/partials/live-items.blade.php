@foreach ($logs as $log)
    <div class="list-group-item px-0 py-2 d-flex align-items-center gap-2" data-live-item="{{ $log->id }}">
        <span class="avatar-circle" style="width: 1.9rem; height: 1.9rem; font-size: 0.68rem;">{{ $log->user?->initials ?? '?' }}</span>
        <div class="min-w-0 flex-grow-1">
            <div class="small fw-semibold text-truncate">{{ $log->user?->name ?? 'Unknown card' }}</div>
            <div class="small text-muted-library">
                <code>{{ $log->rfid_uid }}</code> &middot; {{ $log->entry_at->format('M d, h:i A') }}
            </div>
        </div>
        <div class="text-end">
            @if ($log->exit_at)
                <span class="badge badge-soft-secondary">Exit</span>
            @else
                <span class="attendance-live">Entry</span>
            @endif
            <div class="small text-muted-library">{{ $log->source }}</div>
        </div>
    </div>
@endforeach
