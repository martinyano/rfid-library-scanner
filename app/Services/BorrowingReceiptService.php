<?php

namespace App\Services;

use App\Mail\BorrowReceipt;
use App\Mail\ReturnReceipt;
use App\Models\Borrowing;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BorrowingReceiptService
{
    public function borrowed(Borrowing $borrowing): void
    {
        $borrowing->loadMissing(['user', 'book', 'copy']);

        $this->send($borrowing, new BorrowReceipt($borrowing));
    }

    public function returned(Borrowing $borrowing): void
    {
        $borrowing->loadMissing(['user', 'book', 'copy']);

        $this->send($borrowing, new ReturnReceipt($borrowing));
    }

    private function send(Borrowing $borrowing, Mailable $mailable): void
    {
        $email = $borrowing->user?->email;

        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send($mailable);
        } catch (Throwable $exception) {
            Log::error('Failed to send library receipt email.', [
                'borrowing_id' => $borrowing->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
