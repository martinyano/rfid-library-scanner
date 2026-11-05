@inject('settings', 'App\Services\SettingService')
@php($libraryName = $settings->get('library_name', config('app.name')))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ $libraryName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="admin-shell">
        <aside class="admin-sidebar d-none d-lg-flex flex-column">
            @include('admin.partials.sidebar', ['libraryName' => $libraryName])
        </aside>

        <div class="admin-main d-flex flex-column min-vh-100">
            <nav class="admin-topbar py-2 px-3 px-lg-4 d-flex align-items-center gap-2 sticky-top">
                <button class="btn btn-light border d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminOffcanvas" aria-controls="adminOffcanvas">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="small text-muted-library d-none d-md-inline">
                    <i class="bi bi-shield-lock me-1"></i> Library administration
                </div>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-sm btn-light border d-none d-sm-inline">
                        <i class="bi bi-box-arrow-up-right me-1"></i> View site
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar-circle" style="width: 1.9rem; height: 1.9rem; font-size: 0.72rem;">{{ auth()->user()->initials }}</span>
                            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><span class="dropdown-header">Administrator</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-1"></i> Sign out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="flex-grow-1 p-3 p-lg-4">
                @include('partials.flash')
                @include('partials.errors')
                @yield('content')
            </main>
        </div>
    </div>

    <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="adminOffcanvas" aria-labelledby="adminOffcanvasLabel">
        <div class="offcanvas-header justify-content-end p-2">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            @include('admin.partials.sidebar', ['libraryName' => $libraryName])
        </div>
    </div>
</body>
</html>
