<div class="sidebar-brand d-flex align-items-center gap-2">
    <span class="brand-mark"><i class="bi bi-book-half"></i></span>
    <div class="min-w-0">
        <div class="fw-bold text-white text-truncate">{{ $libraryName }}</div>
        <div class="small text-white-50">Admin panel</div>
    </div>
</div>

<div class="py-3">
    <div class="sidebar-section">Overview</div>
    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="sidebar-section">Circulation</div>
    <a class="nav-link {{ request()->routeIs('admin.borrowings.*') ? 'active' : '' }}" href="{{ route('admin.borrowings.index') }}">
        <i class="bi bi-arrow-left-right"></i> Borrowings
    </a>
    <a class="nav-link {{ request()->routeIs('admin.fines.*') ? 'active' : '' }}" href="{{ route('admin.fines.index') }}">
        <i class="bi bi-cash-coin"></i> Fines
    </a>
    <a class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}" href="{{ route('admin.attendance.index') }}">
        <i class="bi bi-clock-history"></i> Attendance
    </a>

    <div class="sidebar-section">Catalog</div>
    <a class="nav-link {{ request()->routeIs('admin.books.*') ? 'active' : '' }}" href="{{ route('admin.books.index') }}">
        <i class="bi bi-book"></i> Inventory
    </a>
    <a class="nav-link {{ request()->routeIs('admin.authors.*') ? 'active' : '' }}" href="{{ route('admin.authors.index') }}">
        <i class="bi bi-person-lines-fill"></i> Authors
    </a>
    <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
        <i class="bi bi-tags"></i> Categories
    </a>

    <div class="sidebar-section">People</div>
    <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}">
        <i class="bi bi-people"></i> Students
    </a>

    <div class="sidebar-section">System</div>
    <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">
        <i class="bi bi-gear"></i> Settings
    </a>
    <a class="nav-link" href="{{ route('home') }}">
        <i class="bi bi-box-arrow-up-right"></i> View public site
    </a>
</div>
