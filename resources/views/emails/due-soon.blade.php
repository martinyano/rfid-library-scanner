@component('mail::message')
# Book due soon

Hello {{ $borrowing->user?->name ?? 'there' }},

This is a reminder that the book below is due **{{ $daysLeft === 0 ? 'today' : ($daysLeft === 1 ? 'tomorrow' : "in {$daysLeft} days") }}**.

**Book:** {{ $borrowing->book?->title ?? 'Unknown book' }}

**Author:** {{ $borrowing->book?->author?->name ?? 'Unknown' }}

**Due Date:** {{ $borrowing->due_at?->format('M j, Y') }}

**Loan Reference:** #{{ $borrowing->id }}

Please return the book on or before the due date. If you need more time, contact the library desk to ask about a renewal.

Thank you,<br>
{{ $libraryName }}
@endcomponent
