<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\BorrowingController as AdminBorrowingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Api\RfidScanController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\Student\BookController as StudentBookController;
use App\Http\Controllers\Student\BorrowingController as StudentBorrowingController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\SessionController as StudentSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/scan', [ScanController::class, 'show'])->name('scan');
Route::post('/scan', [ScanController::class, 'store'])->middleware('throttle:20,1')->name('scan.store');

Route::post('/api/rfid/scan', RfidScanController::class)
    ->middleware(['rfid.key', 'throttle:60,1'])
    ->name('api.rfid.scan');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::post('logout', [AdminAuthController::class, 'destroy'])->name('logout');

        Route::resource('authors', AuthorController::class)->except(['show']);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('students', AdminStudentController::class);
        Route::resource('books', AdminBookController::class)->except(['show']);

        Route::get('borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
        Route::get('borrowings/{borrowing}', [AdminBorrowingController::class, 'show'])->name('borrowings.show');
        Route::post('borrowings/{borrowing}/return', [AdminBorrowingController::class, 'markReturned'])->name('borrowings.return');
        Route::post('borrowings/{borrowing}/lost', [AdminBorrowingController::class, 'markLost'])->name('borrowings.lost');
        Route::post('borrowings/{borrowing}/damaged', [AdminBorrowingController::class, 'markDamaged'])->name('borrowings.damaged');
        Route::post('borrowings/{borrowing}/extend', [AdminBorrowingController::class, 'extend'])->name('borrowings.extend');

        Route::get('attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance/{log}/close', [AdminAttendanceController::class, 'close'])->name('attendance.close');

        Route::get('settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    });
});

Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('dashboard', StudentDashboardController::class)->name('dashboard');
    Route::get('books/{book}', [StudentBookController::class, 'show'])->name('books.show');
    Route::get('books/{book}/borrow', [StudentBookController::class, 'borrowForm'])->name('books.borrow.form');
    Route::post('books/{book}/borrow', [StudentBookController::class, 'borrow'])->name('books.borrow');
    Route::get('borrowings', StudentBorrowingController::class)->name('borrowings.index');
    Route::post('logout', [StudentSessionController::class, 'destroy'])->name('logout');
});
