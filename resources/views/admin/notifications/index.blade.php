@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="admin-page-title h3 mb-1">Notifications</h1>
            <p class="text-muted-library mb-0">Due dates, overdue loans, fines, and RFID reader alerts.</p>
        </div>
        @if (auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-check2-all me-1"></i> Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $tone = match ($data['tone'] ?? 'primary') {
                        'warning' => 'amber',
                        default => $data['tone'] ?? 'primary',
                    };
                @endphp
                <div class="list-group-item py-3 d-flex align-items-start gap-3 {{ $notification->read_at ? '' : 'bg-soft-primary border-start border-3 border-primary' }}">
                    <span class="stat-icon bg-soft-{{ $tone }}" style="width: 2.2rem; height: 2.2rem; font-size: 0.85rem;">
                        <i class="bi {{ $data['icon'] ?? 'bi-bell' }}"></i>
                    </span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="fw-semibold">{{ $data['title'] ?? 'Notification' }}</span>
                            @unless ($notification->read_at)
                                <span class="badge badge-soft-primary">New</span>
                            @endunless
                        </div>
                        <div class="small text-muted-library mt-1">{{ $data['message'] ?? '' }}</div>
                        <div class="small text-muted-library mt-1">{{ $notification->created_at->format('M d, Y h:i A') }}</div>
                        @if (! empty($data['url']))
                            <a href="{{ $data['url'] }}" class="small mt-2 d-inline-block">Open record <i class="bi bi-arrow-right ms-1"></i></a>
                        @endif
                    </div>
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light border" title="Mark as read"><i class="bi bi-check2"></i></button>
                        </form>
                    @endunless
                </div>
            @empty
                <div class="empty-state py-5">
                    <i class="bi bi-bell"></i>
                    <div class="small">No notifications yet.</div>
                </div>
            @endforelse
        </div>
        @if ($notifications->hasPages())
            <div class="card-footer bg-white">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
