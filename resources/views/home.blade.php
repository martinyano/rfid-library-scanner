@extends('layouts.app')

@inject('settings', 'App\Services\SettingService')
@php($libraryName = $settings->get('library_name', config('app.name')))

@section('title', $libraryName)

@section('content')
    <section class="hero p-4 p-md-5 mb-4">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7">
                <span class="hero-badge mb-3">
                    <i class="bi bi-broadcast-pin"></i> RFID-powered library circulation
                </span>
                <h1 class="hero-title mb-3">{{ $libraryName }}</h1>
                <p class="lead mb-4">
                    Tap your RFID card to enter the library, then tap a book's tag to borrow or return it in seconds.
                    One connected system for students, librarians, and daily library operations.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-2 mb-4">
                    <a href="{{ route('scan') }}" class="btn btn-library btn-lg">
                        <i class="bi bi-upc-scan me-2"></i> Enter library
                    </a>
                    <a href="{{ route('admin.login') }}" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-shield-lock me-2"></i> Admin sign in
                    </a>
                </div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="hero-feature">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <div>
                                <div class="fw-semibold">Scan to sign in</div>
                                <div class="small opacity-75">Your visit time is logged automatically.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="hero-feature">
                            <i class="bi bi-journal-check"></i>
                            <div>
                                <div class="fw-semibold">Borrow with confidence</div>
                                <div class="small opacity-75">Due dates and reminders are handled for you.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="p-4 p-md-5 rounded-4" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16); backdrop-filter: blur(8px);">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="stat-icon bg-soft-primary" style="width: 3.5rem; height: 3.5rem; font-size: 1.6rem;">
                            <i class="bi bi-rss"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5">Ready to scan</div>
                            <div class="small text-white-50">Card reader is online</div>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge rounded-pill text-bg-light">1</span>
                            <span class="text-white-50">Tap your registered RFID card</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge rounded-pill text-bg-light">2</span>
                            <span class="text-white-50">Your entry is recorded instantly</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge rounded-pill text-bg-light">3</span>
                            <span class="text-white-50">Search, filter, and borrow books</span>
                        </div>
                    </div>
                    <hr class="border-light opacity-25 my-4">
                    <p class="small text-white-50 mb-0">
                        Card not recognised? Visit the librarian to register or reactivate your card.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-3">
            <div>
                <h2 class="h4 fw-bold mb-1">Everything in one library system</h2>
                <p class="text-muted-library mb-0">Designed for fast student entry and simple day-to-day administration.</p>
            </div>
            <a href="{{ route('scan') }}" class="btn btn-primary">
                Go to scanner <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="stat-icon bg-soft-primary mb-3"><i class="bi bi-upc-scan"></i></div>
                        <h3 class="h6 fw-bold">RFID entry logging</h3>
                        <p class="text-muted-library small mb-0">Every card tap records the student, date, time, and source, including exit logs on logout.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="stat-icon bg-soft-teal mb-3"><i class="bi bi-search"></i></div>
                        <h3 class="h6 fw-bold">Smart catalog search</h3>
                        <p class="text-muted-library small mb-0">Search titles, authors, and ISBNs, then narrow results by category or author.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="stat-icon bg-soft-amber mb-3"><i class="bi bi-calendar-check"></i></div>
                        <h3 class="h6 fw-bold">Borrowing and due dates</h3>
                        <p class="text-muted-library small mb-0">Automatic due dates, overdue tracking, and lost or damaged book records.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="stat-icon bg-soft-slate mb-3"><i class="bi bi-bell"></i></div>
                        <h3 class="h6 fw-bold">Email reminders</h3>
                        <p class="text-muted-library small mb-0">Students are reminded before a due date and when a book becomes overdue.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
