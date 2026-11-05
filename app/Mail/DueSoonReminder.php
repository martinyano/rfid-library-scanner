<?php

namespace App\Mail;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DueSoonReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Borrowing $borrowing,
        public int $daysLeft,
        public string $libraryName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Library reminder: '.$this->borrowing->book->title.' is due soon',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.due-soon',
        );
    }
}
