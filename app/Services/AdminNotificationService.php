<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\LibraryNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class AdminNotificationService
{
    public function dueSoon(Borrowing $borrowing): void
    {
        $this->send(new LibraryNotification(
            title: 'Book due soon',
            message: ($borrowing->book?->title ?? 'A book').' is due on '.$borrowing->due_at->format('M d, Y').' for '.($borrowing->user?->name ?? 'a student').'.',
            icon: 'bi-calendar-check',
            tone: 'warning',
            url: route('admin.borrowings.show', $borrowing),
            meta: ['borrowing_id' => $borrowing->id],
        ));
    }

    public function overdue(Borrowing $borrowing): void
    {
        $this->send(new LibraryNotification(
            title: 'Overdue book',
            message: ($borrowing->book?->title ?? 'A book').' is '.$borrowing->overdue_days.' day(s) overdue for '.($borrowing->user?->name ?? 'a student').'.',
            icon: 'bi-exclamation-octagon',
            tone: 'danger',
            url: route('admin.borrowings.show', $borrowing),
            meta: ['borrowing_id' => $borrowing->id],
        ));
    }

    public function fineRecorded(Transaction $transaction): void
    {
        $this->send(new LibraryNotification(
            title: 'Fine recorded',
            message: 'A fine of '.$transaction->penalty.' was recorded for '.($transaction->user?->name ?? 'a student').'.',
            icon: 'bi-cash-coin',
            tone: 'danger',
            url: route('admin.borrowings.show', $transaction->id),
            meta: ['transaction_id' => $transaction->id, 'borrowing_id' => $transaction->id],
        ));
    }

    public function rfidTapFailed(string $reason, string $uid): void
    {
        $key = 'notifications.rfid-failed.'.hash('sha256', $reason.':'.$uid);

        if (! Cache::add($key, true, now()->addMinutes(5))) {
            return;
        }

        $this->send(new LibraryNotification(
            title: 'RFID tap needs attention',
            message: $reason.' Card: '.$uid.'.',
            icon: 'bi-upc-scan',
            tone: 'warning',
            url: route('admin.attendance.index'),
            meta: ['rfid_uid' => $uid],
        ));
    }

    private function send(LibraryNotification $notification): void
    {
        $admins = User::query()->admins()->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, $notification);
        }
    }
}
