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
<body>
    <nav class="navbar navbar-expand-lg navbar-library sticky-top py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <span class="brand-mark"><i class="bi bi-book-half"></i></span>
                <span class="brand-text">{{ $libraryName }}</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-3"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto ms-lg-4 mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('scan*') ? 'active' : '' }}" href="{{ route('scan') }}">Scan card</a>
                    </li>
                    @auth
                        @if (auth()->user()->isStudent())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">Catalog</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('student.borrowings.*') ? 'active' : '' }}" href="{{ route('student.borrowings.index') }}">My borrowings</a>
                            </li>
                        @endif
                    @endauth
                </ul>
                <div class="d-flex align-items-center gap-2">
                    @auth
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-speedometer2 me-1"></i> Admin panel
                            </a>
                        @endif
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="avatar-circle" style="width: 1.9rem; height: 1.9rem; font-size: 0.72rem;">{{ auth()->user()->initials }}</span>
                                <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li><span class="dropdown-header">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Student' }}</span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.logout') : route('student.logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-1"></i> {{ auth()->user()->isAdmin() ? 'Sign out' : 'Log out & record exit' }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('admin.login') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-shield-lock me-1"></i> Admin
                        </a>
                    @endauth
                </div>
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
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small">
            <span>&copy; {{ date('Y') }} {{ $libraryName }}. All rights reserved.</span>
            <span class="d-flex align-items-center gap-2">
                <i class="bi bi-upc-scan"></i> Powered by RFID circulation
            </span>
        </div>
    </footer>
</body>
</html>
