<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use App\Services\SettingService;

class DashboardController extends Controller
{
    public function __invoke(SettingService $settings)
    {
        $dueSoonDays = $settings->int('due_soon_days', 2);

        return view('admin.dashboard', [
            'totalTitles' => Book::query()->count(),
            'totalCopies' => (int) Book::query()->sum('total_copies'),
            'availableCopies' => (int) Book::query()->sum('available_copies'),
            'activeBorrowings' => Borrowing::query()->active()->count(),
            'overdueBorrowings' => Borrowing::query()->overdue()->count(),
            'studentCount' => User::query()->students()->count(),
            'activeSessions' => AttendanceLog::query()->open()->count(),
            'todayEntries' => AttendanceLog::query()->whereDate('entry_at', today())->count(),
            'recentBorrowings' => Borrowing::query()
                ->with(['user', 'book'])
                ->latest('borrowed_at')
                ->limit(6)
                ->get(),
            'dueSoon' => Borrowing::query()
                ->with(['user', 'book'])
                ->active()
                ->whereDate('due_at', '>=', today())
                ->whereDate('due_at', '<=', today()->addDays($dueSoonDays))
                ->orderBy('due_at')
                ->limit(5)
                ->get(),
            'overdueLoans' => Borrowing::query()
                ->with(['user', 'book'])
                ->overdue()
                ->orderBy('due_at')
                ->limit(5)
                ->get(),
            'recentAttendance' => AttendanceLog::query()
                ->with('user')
                ->latest('entry_at')
                ->limit(6)
                ->get(),
            'dueSoonDays' => $dueSoonDays,
        ]);
    }
}
