@component('mail::message')
Hi {{ explode(' ', trim($borrowing->user?->name ?? 'there'))[0] }},

Thanks for returning your book. Your return has been confirmed in the library system.

**Action:** Returned

**Book:** {{ $borrowing->book?->title ?? 'Unknown book' }}

**RFID Tag:** {{ $borrowing->book_rfid_uid ?: $borrowing->copy?->rfid_uid }}

**Returned At:** {{ $borrowing->returned_at?->format('M j, Y, g:i A') }}

**Status:** Completed (No longer due)

— Library Circulation System
@endcomponent
