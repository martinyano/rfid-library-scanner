<?php

namespace App\Mail;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Borrowing $borrowing,
    ) {}

    public function envelope(): Envelope
    {
        $title = $this->borrowing->book?->title ?? 'Book';

        return new Envelope(
            subject: "[Library Receipt] Returned: {$title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.return-receipt',
        );
    }
}
