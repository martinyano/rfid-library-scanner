@extends('layouts.guest')

@inject('settings', 'App\Services\SettingService')
@php($libraryName = $settings->get('library_name', config('app.name')))

@section('title', 'Admin sign in - '.$libraryName)

@section('content')
    <div class="auth-page py-4">
        <div class="card auth-card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="stat-icon bg-soft-primary mx-auto mb-3" style="width: 4rem; height: 4rem; font-size: 1.8rem;">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h1 class="h4 fw-bold mb-1">Administrator sign in</h1>
                    <p class="text-muted-library small mb-0">Manage the catalog, students, and circulation.</p>
                </div>

                <form method="POST" action="{{ route('admin.login.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold">Password</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                        <label class="form-check-label small" for="remember">Keep me signed in</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
                    </button>
                </form>

                @if (app()->isLocal())
                    <div class="rule-pill mt-4 text-center">
                        <div class="small"><strong>Demo admin:</strong> admin@library.test / password</div>
                    </div>
                @endif

                <div class="text-center mt-4 small">
                    <a href="{{ route('scan') }}" class="me-3"><i class="bi bi-upc-scan me-1"></i> Student scan</a>
                    <a href="{{ route('home') }}"><i class="bi bi-house me-1"></i> Home</a>
                </div>
            </div>
        </div>
    </div>
@endsection
