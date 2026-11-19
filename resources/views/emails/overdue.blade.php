@component('mail::message')
# Overdue book notice

Hello {{ $borrowing->user?->name ?? 'there' }},

Our records show that the book below is overdue by **{{ $daysOverdue }} day{{ $daysOverdue === 1 ? '' : 's' }}**. Please return it as soon as possible.

**Book:** {{ $borrowing->book?->title ?? 'Unknown book' }}

**Author:** {{ $borrowing->book?->author?->name ?? 'Unknown' }}

**Due Date:** {{ $borrowing->due_at?->format('M j, Y') }}

**Loan Reference:** #{{ $borrowing->id }}

Return the book at the library desk. If the book has been lost or damaged, please inform the librarian so the record can be updated.

Thank you,<br>
{{ $libraryName }}
@endcomponent
