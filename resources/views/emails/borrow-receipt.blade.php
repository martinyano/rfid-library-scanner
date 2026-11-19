@component('mail::message')
Hi {{ explode(' ', trim($borrowing->user?->name ?? 'there'))[0] }},

Your RFID book borrowing transaction has been recorded.

**Action:** Borrowed

**Book:** {{ $borrowing->book?->title ?? 'Unknown book' }}

**RFID Tag:** {{ $borrowing->book_rfid_uid ?: $borrowing->copy?->rfid_uid }}

**Borrowed At:** {{ $borrowing->borrowed_at?->format('M j, Y, g:i A') }}

**Due Date:** {{ $borrowing->due_at?->format('M j, Y') }}

Please return this book on or before the due date so it remains available for other students. If you did not authorize this transaction, please report it to the library desk.

— Library Circulation System
@endcomponent
