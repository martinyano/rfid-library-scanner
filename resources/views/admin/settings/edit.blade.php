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
                        <p class="small text-muted-library">Card taps record attendance; book tag taps record borrowing and returns.</p>

                        <div class="mb-3">
                            <div class="small text-muted-library">Circulation endpoint (card + book)</div>
                            <code class="small d-block text-break">POST {{ url('/api/rfid/circulation') }}</code>
                            <pre class="small bg-light border rounded p-2 mt-2 mb-0"><code>{
  "rfid_uid": "CARD-OR-BOOK-UID",
  "device_id": "reader-1"
}</code></pre>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted-library">Door endpoint (entry / exit)</div>
                            <code class="small d-block text-break">POST {{ url('/api/rfid/scan') }}</code>
                            <pre class="small bg-light border rounded p-2 mt-2 mb-0"><code>{
  "rfid_uid": "CARD-UID"
}</code></pre>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted-library">Header</div>
                            <code class="small d-block text-break">X-RFID-Key: ••••••••••</code>
                        </div>

                        <ol class="small text-muted-library ps-3 mb-3">
                            <li>Student taps their card: entry is logged and the reader waits for a book tag.</li>
                            <li>Student taps a book copy tag: an active loan is returned, otherwise the copy is borrowed.</li>
                            <li>Both tapped card and book UIDs are stored on the borrowing record.</li>
                            <li>Use the door endpoint for exits; the pending session expires after {{ config('library.kiosk_session_seconds') }} seconds.</li>
                        </ol>

                        <div class="mb-0">
                            <div class="small text-muted-library">Response actions</div>
                            <code class="small d-block">identify &middot; borrow &middot; return</code>
                        </div>

                        <p class="small text-muted-library mt-3 mb-0">Set <code>RFID_API_KEY</code> and <code>RFID_MODE</code> for the Python bridge. Register student cards and copy tags in the Students and Inventory pages.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
