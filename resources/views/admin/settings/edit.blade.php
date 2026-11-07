@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <div class="mb-4">
        <h1 class="admin-page-title h3 mb-1">Library settings</h1>
        <p class="text-muted-library mb-0">Borrowing rules, reminders, and attendance behavior.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header"><i class="bi bi-building me-1"></i> General</div>
                    <div class="card-body">
                        <label for="library_name" class="form-label small fw-semibold">Library name</label>
                        <input type="text" name="library_name" id="library_name" value="{{ old('library_name', $settings['library_name']) }}" class="form-control @error('library_name') is-invalid @enderror" required maxlength="120">
                        @error('library_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><i class="bi bi-journal-check me-1"></i> Borrowing</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="borrow_days" class="form-label small fw-semibold">Loan period (days)</label>
                                <input type="number" name="borrow_days" id="borrow_days" value="{{ old('borrow_days', $settings['borrow_days']) }}" min="1" max="90" class="form-control @error('borrow_days') is-invalid @enderror" required>
                                @error('borrow_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="max_books" class="form-label small fw-semibold">Maximum active loans per student</label>
                                <input type="number" name="max_books" id="max_books" value="{{ old('max_books', $settings['max_books']) }}" min="1" max="20" class="form-control @error('max_books') is-invalid @enderror" required>
                                @error('max_books')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><i class="bi bi-bell me-1"></i> Reminders &amp; attendance</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="due_soon_days" class="form-label small fw-semibold">Send due-soon reminder (days before due)</label>
                                <input type="number" name="due_soon_days" id="due_soon_days" value="{{ old('due_soon_days', $settings['due_soon_days']) }}" min="0" max="30" class="form-control @error('due_soon_days') is-invalid @enderror" required>
                                @error('due_soon_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Set to 0 to send on the due date.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="auto_close_hours" class="form-label small fw-semibold">Auto-close attendance after (hours)</label>
                                <input type="number" name="auto_close_hours" id="auto_close_hours" value="{{ old('auto_close_hours', $settings['auto_close_hours']) }}" min="1" max="72" class="form-control @error('auto_close_hours') is-invalid @enderror" required>
                                @error('auto_close_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Open sessions older than this are closed by the scheduled task.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Save settings</button>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><i class="bi bi-broadcast me-1"></i> RFID integration</div>
                    <div class="card-body">
                        <p class="small text-muted-library">Send card scans from an ESP32 or similar device to this endpoint.</p>
                        <div class="mb-3">
                            <div class="small text-muted-library">Endpoint</div>
                            <code class="small d-block text-break">POST {{ url('/api/rfid/scan') }}</code>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted-library">Header</div>
                            <code class="small d-block text-break">X-RFID-Key: {{ config('library.rfid_api_key') }}</code>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted-library">JSON body</div>
                            <pre class="small bg-light border rounded p-2 mb-0"><code>{
  "rfid_uid": "CARD-UID",
  "source": "iot"
}</code></pre>
                        </div>
                        <p class="small text-muted-library mb-0">The API key is set with the <code>RFID_API_KEY</code> environment variable.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
