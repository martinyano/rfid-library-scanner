@inject('settings', 'App\Services\SettingService')
@php($libraryName = $settings->get('library_name', config('app.name')))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $libraryName)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body-tertiary">
    <nav class="navbar navbar-expand-lg navbar-library py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <span class="brand-mark"><i class="bi bi-book-half"></i></span>
                <span class="brand-text">{{ $libraryName }}</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-sm btn-light border">Home</a>
                <a href="{{ route('scan') }}" class="btn btn-sm btn-outline-primary">Scan</a>
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('student.dashboard') }}" class="btn btn-sm btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('admin.login') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-shield-lock me-1"></i> Admin
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container pt-3">
        @include('partials.flash')
        @include('partials.errors')
    </div>

    <main class="container pb-5">
        @yield('content')
    </main>

    <footer class="footer-library py-4 mt-auto">
        <div class="container text-center small">
            &copy; {{ date('Y') }} {{ $libraryName }}
        </div>
    </footer>
</body>
</html>
