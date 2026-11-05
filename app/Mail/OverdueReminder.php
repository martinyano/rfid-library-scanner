<?php

namespace App\Mail;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Borrowing $borrowing,
        public int $daysOverdue,
        public float $estimatedFine,
        public string $libraryName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Overdue library book: '.$this->borrowing->book->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.overdue',
        );
    }
}
