@extends('layouts.guest')

@inject('settings', 'App\Services\SettingService')
@php($libraryName = $settings->get('library_name', config('app.name')))

@section('title', 'Scan your card - '.$libraryName)

@section('content')
    <div class="py-4 py-lg-5">
        <div class="card scan-card">
            <div class="card-body p-4 p-md-5 text-center">
                <div class="scan-icon mb-4">
                    <i class="bi bi-upc-scan"></i>
                </div>
                <h1 class="h3 fw-bold mb-2">Scan your RFID card</h1>
                <p class="text-muted-library mb-4">
                    Tap your student card on the reader, or type the card UID below and press Enter.
                </p>

                <form method="POST" action="{{ route('scan.store') }}" class="mb-4">
                    @csrf
                    <label for="rfid_uid" class="form-label visually-hidden">RFID card UID</label>
                    <input
                        type="text"
                        id="rfid_uid"
                        name="rfid_uid"
                        value="{{ old('rfid_uid') }}"
                        class="form-control scan-input @error('rfid_uid') is-invalid @enderror"
                        placeholder="CARD-UID"
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
                        inputmode="text"
                        maxlength="100"
                        data-rfid-scan-input
                        required
                    >
                    @error('rfid_uid')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                        <i class="bi bi-arrow-right-circle me-2"></i> Continue
                    </button>
                </form>

                <div class="rule-pill text-start d-flex gap-2 mb-3">
                    <i class="bi bi-info-circle mt-1"></i>
                    <span>If you are using a USB card reader, the form can submit automatically when the reader sends Enter.</span>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-light border btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to home
                    </a>
                    <a href="{{ route('admin.login') }}" class="btn btn-light border btn-sm">
                        <i class="bi bi-shield-lock me-1"></i> Admin sign in
                    </a>
                </div>
            </div>
            <div class="card-footer bg-white border-top py-3 text-center small text-muted-library">
                <i class="bi bi-shield-check me-1"></i>
                Card not recognised? Please ask the librarian to register or reactivate it.
            </div>
        </div>

        <div class="text-center mt-4 small text-muted-library">
            <i class="bi bi-router me-1"></i>
            IoT readers can post scans to <code>/api/rfid/scan</code> using the configured API key.
        </div>
    </div>
@endsection
