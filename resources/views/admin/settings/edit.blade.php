@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <div class="mb-4">
        <h1 class="admin-page-title h3 mb-1">Library settings</h1>
        <p class="text-muted-library mb-0">Borrowing rules, fines, reminders, and attendance behavior.</p>
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
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="block_on_unpaid_fine" id="block_on_unpaid_fine" value="1" @checked(old('block_on_unpaid_fine', $settings['block_on_unpaid_fine']) == '1')>
                                    <label class="form-check-label small fw-semibold" for="block_on_unpaid_fine">Block borrowing when a student has unpaid fines</label>
                                </div>
                                <div class="form-text">When off, students may still borrow and settle fines later.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><i class="bi bi-cash-coin me-1"></i> Fines</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="fine_per_day" class="form-label small fw-semibold">Overdue fine per day ({{ config('library.currency') }})</label>
                                <input type="number" step="0.01" name="fine_per_day" id="fine_per_day" value="{{ old('fine_per_day', $settings['fine_per_day']) }}" min="0" max="10000" class="form-control @error('fine_per_day') is-invalid @enderror" required>
                                @error('fine_per_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="lost_fee" class="form-label small fw-semibold">Lost book fee ({{ config('library.currency') }})</label>
                                <input type="number" step="0.01" name="lost_fee" id="lost_fee" value="{{ old('lost_fee', $settings['lost_fee']) }}" min="0" max="100000" class="form-control @error('lost_fee') is-invalid @enderror" required>
                                @error('lost_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="damaged_fee" class="form-label small fw-semibold">Damaged book fee ({{ config('library.currency') }})</label>
                                <input type="number" step="0.01" name="damaged_fee" id="damaged_fee" value="{{ old('damaged_fee', $settings['damaged_fee']) }}" min="0" max="100000" class="form-control @error('damaged_fee') is-invalid @enderror" required>
                                @error('damaged_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
