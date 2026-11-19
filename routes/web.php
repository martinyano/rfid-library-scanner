<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\BookCopyController as AdminBookCopyController;
use App\Http\Controllers\Admin\BorrowingController as AdminBorrowingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Api\RfidCirculationController;
use App\Http\Controllers\Api\RfidScanController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

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
        Route::post('books/{book}/copies', [AdminBookCopyController::class, 'store'])->name('books.copies.store');
        Route::put('books/{book}/copies/{copy}', [AdminBookCopyController::class, 'update'])->name('books.copies.update');
        Route::delete('books/{book}/copies/{copy}', [AdminBookCopyController::class, 'destroy'])->name('books.copies.destroy');

        Route::get('borrowings/create', [AdminBorrowingController::class, 'create'])->name('borrowings.create');
        Route::post('borrowings', [AdminBorrowingController::class, 'store'])->name('borrowings.store');
        Route::get('borrowings/live', [AdminBorrowingController::class, 'live'])->name('borrowings.live');
        Route::get('borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
        Route::get('borrowings/{borrowing}', [AdminBorrowingController::class, 'show'])->name('borrowings.show');
        Route::post('borrowings/{borrowing}/return', [AdminBorrowingController::class, 'markReturned'])->name('borrowings.return');
        Route::post('borrowings/{borrowing}/lost', [AdminBorrowingController::class, 'markLost'])->name('borrowings.lost');
        Route::post('borrowings/{borrowing}/damaged', [AdminBorrowingController::class, 'markDamaged'])->name('borrowings.damaged');
        Route::post('borrowings/{borrowing}/extend', [AdminBorrowingController::class, 'extend'])->name('borrowings.extend');
        Route::post('borrowings/{borrowing}/transactions', [AdminTransactionController::class, 'store'])->name('borrowings.transactions.store');

        Route::get('attendance/live', [AdminAttendanceController::class, 'live'])->name('attendance.live');
        Route::get('attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance/{log}/close', [AdminAttendanceController::class, 'close'])->name('attendance.close');

        Route::get('transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
        Route::post('transactions/{transaction}/pay', [AdminTransactionController::class, 'markPaid'])->name('transactions.pay');

        Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [AdminNotificationController::class, 'readAll'])->name('notifications.read-all');

        Route::get('settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    });
});
