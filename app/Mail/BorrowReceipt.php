<?php

namespace App\Mail;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BorrowReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Borrowing $borrowing,
    ) {}

    public function envelope(): Envelope
    {
        $title = $this->borrowing->book?->title ?? 'Book';
        $dueDate = $this->borrowing->due_at?->format('M d, Y') ?? '';

        return new Envelope(
            subject: "[Library Receipt] Borrowed: {$title} — Due {$dueDate}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.borrow-receipt',
        );
    }
}
