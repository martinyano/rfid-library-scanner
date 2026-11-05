@foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $style)
    @if (session($key))
        <div class="alert alert-{{ $style }} alert-dismissible fade show alert-auto-dismiss d-flex align-items-start gap-2" role="alert">
            <i class="bi {{ $key === 'success' ? 'bi-check-circle-fill' : ($key === 'error' ? 'bi-exclamation-octagon-fill' : 'bi-info-circle-fill') }} mt-1"></i>
            <div class="flex-grow-1">{{ session($key) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach
