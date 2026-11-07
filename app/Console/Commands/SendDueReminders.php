<?php

namespace App\Console\Commands;

use App\Mail\DueSoonReminder;
use App\Mail\OverdueReminder;
use App\Models\Borrowing;
use App\Services\SettingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendDueReminders extends Command
{
    protected $signature = 'library:send-due-reminders';

    protected $description = 'Email students about books that are due soon or overdue';

    public function handle(SettingService $settings): int
    {
        $libraryName = (string) $settings->get('library_name', config('app.name'));
        $dueSoonDays = $settings->int('due_soon_days', 2);
        $sent = 0;
        $failed = 0;

        $dueSoon = Borrowing::query()
            ->with(['user', 'book'])
            ->active()
            ->whereNull('due_soon_notified_at')
            ->whereDate('due_at', '>=', today())
            ->whereDate('due_at', '<=', today()->addDays($dueSoonDays))
            ->get();

        foreach ($dueSoon as $borrowing) {
            if (! $borrowing->user?->email || ! $borrowing->book) {
                continue;
            }

            try {
                Mail::to($borrowing->user->email)->send(new DueSoonReminder(
                    $borrowing,
                    max(0, (int) today()->diffInDays($borrowing->due_at, false)),
                    $libraryName
                ));

                $borrowing->update(['due_soon_notified_at' => now()]);
                $sent++;
            } catch (Throwable $exception) {
                $failed++;
                Log::error('Failed to send due-soon reminder.', [
                    'borrowing_id' => $borrowing->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $overdue = Borrowing::query()
            ->with(['user', 'book'])
            ->active()
            ->whereNull('overdue_notified_at')
            ->whereDate('due_at', '<', today())
            ->get();

        foreach ($overdue as $borrowing) {
            if (! $borrowing->user?->email || ! $borrowing->book) {
                continue;
            }

            try {
                Mail::to($borrowing->user->email)->send(new OverdueReminder(
                    $borrowing,
                    $borrowing->overdue_days,
                    $libraryName
                ));

                $borrowing->update(['overdue_notified_at' => now()]);
                $sent++;
            } catch (Throwable $exception) {
                $failed++;
                Log::error('Failed to send overdue reminder.', [
                    'borrowing_id' => $borrowing->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->info("Reminder emails sent: {$sent}. Failed: {$failed}.");

        return self::SUCCESS;
    }
}
