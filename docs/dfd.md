# Data Flow Diagram — RFID Library System

This document describes the data flows of the RFID Library System (Laravel application).
It uses two levels of detail:

- **Level 0 (Context Diagram)** — the system as a single process and its external entities.
- **Level 1 (Detailed DFD)** — the internal processes, data stores, and data flows.

## Notation

| Symbol | Mermaid shape | Meaning |
| --- | --- | --- |
| Rectangle | `Entity[Name]` | External entity (actor or external system) |
| Rounded / stadium | `Process([1.0 Name])` | Process |
| Cylinder | `Store[(D1 Name)]` | Data store |
| Arrow | `-->|label|` | Data flow |

---

## Level 0 — Context Diagram

```mermaid
flowchart LR
    S[Student]
    A[Librarian / Admin]
    R[RFID Reader / Scanner]
    E[Email Server]
    C[Scheduler / Cron]

    SYS((RFID Library System))

    R -->|RFID UID scan payload: student card or book tag| SYS
    SYS -->|Attendance, identify, borrow/return result| R

    S -->|RFID card and book tag taps| SYS

    A -->|Book, author, category, student, settings and fine data| SYS
    A -->|Return, lost, damaged, extend and mark-paid actions| SYS
    SYS -->|Inventory, live attendance, live loans, fines and notifications| A

    SYS -->|Due-soon and overdue reminder emails| E
    E -->|Delivered email| S

    C -->|Trigger reminder and attendance-closing jobs| SYS
```

---

## Level 1 — Detailed DFD

```mermaid
flowchart LR
    %% External entities
    S[Student]
    A[Librarian / Admin]
    R[RFID Reader / Scanner]
    E[Email Server]
    C[Scheduler / Cron]

    %% Processes
    P1([1.0 Authentication & Session])
    P2([2.0 RFID Attendance])
    P3([3.0 Catalog Management])
    P4([4.0 Student Management])
    P5([5.0 Borrowing Lifecycle])
    P6([6.0 RFID Circulation])
    P7([7.0 Reminder Service])
    P8([8.0 Settings Management])
    P9([9.0 Dashboard & Reporting])
    P10([10.0 Notifications])
    P11([11.0 Fine Transactions])

    %% Data stores
    D1[(D1 users)]
    D2[(D2 authors)]
    D3[(D3 categories)]
    D4[(D4 books)]
    D5[(D5 borrowings)]
    D6[(D6 attendance_logs)]
    D7[(D7 settings)]
    D8[(D8 sessions / cache)]
    D9[(D9 cover-image storage)]
    D10[(D10 book_copies)]
    D11[(D11 transactions)]
    D12[(D12 notifications)]

    %% Authentication and session
    A -->|Admin credentials| P1
    P1 -->|Verify credentials, role| D1
    P1 -->|Session state, role| D8
    P1 -->|Authenticated session| A

    %% RFID attendance
    S -->|RFID card presented| R
    R -->|Attendance scan payload: rfid_uid| P2
    P2 -->|Lookup student by RFID| D1
    P2 -->|Read open session| D6
    P2 -->|Create entry / record exit| D6
    P2 -->|Result: entry or exit, logged time| R

    %% Catalog management
    A -->|Author data| P3
    A -->|Category data| P3
    A -->|Book data, copy tags and cover image| P3
    P3 -->|Create / update / delete authors| D2
    P3 -->|Create / update / delete categories| D3
    P3 -->|Create / update / delete books| D4
    P3 -->|Create / update / delete physical copies and RFID tags| D10
    P3 -->|Store / remove cover image| D9

    %% Student management
    A -->|Student data, RFID UID, active status| P4
    P4 -->|Create / update / deactivate students| D1

    %% Borrowing lifecycle
    A -->|Manual borrowing form: student, book, copy| P5
    A -->|Return, lost, damaged, extend action| P5
    P5 -->|Read borrow rules| D7
    P5 -->|Check active loans and availability| D5
    P5 -->|Select / update physical copy status| D10
    P5 -->|Read book availability| D4
    P5 -->|Create loan, update status and due date| D5
    P5 -->|Adjust available_copies / total_copies| D4
    P5 -->|Loan result and due date| A

    %% RFID circulation kiosk
    S -->|Student card, then book tag| R
    R -->|Circulation scan: rfid_uid, device_id| P6
    P6 -->|Lookup student card| D1
    P6 -->|Lookup physical copy by tag| D10
    P6 -->|Read borrow rules| D7
    P6 -->|Read active loan for the copy| D5
    P6 -->|Create loan / mark returned| P5
    P6 -->|Read / write pending student session| D8
    P6 -->|Store scanned card UID and book tag UID| D5
    P6 -->|Log entry only| P2
    P6 -->|Failed tap alert| P10
    P6 -->|Result: identify, borrow, or return| R

    %% Reminders
    C -->|Daily 08:00 trigger| P7
    P7 -->|Find due-soon and overdue loans| D5
    P7 -->|Student email details| D1
    P7 -->|Book details| D4
    P7 -->|Read reminder settings| D7
    P7 -->|Mark notified timestamps| D5
    P7 -->|Due-soon / overdue alerts| P10
    P7 -->|Send reminder email| E
    E -->|Email delivered| S

    %% Fine transactions
    A -->|Overdue fine amount and notes| P11
    P11 -->|Create unpaid transaction| D11
    A -->|Mark paid| P11
    P11 -->|Update payment status| D11
    P11 -->|Fine-recorded alert| P10

    %% Notifications
    P10 -->|Write admin notifications| D12
    P10 -->|Unread count and dropdown| P9

    %% Scheduled attendance closing
    C -->|Daily 23:30 trigger| P2
    P2 -->|Auto-close stale open sessions| D6

    %% Settings management
    A -->|Library settings| P8
    P8 -->|Create / update settings| D7
    P8 -->|Invalidate settings cache| D8

    %% Dashboard and reporting
    A -->|Request dashboard / reports| P9
    P9 -->|Read books, authors, categories| D4
    P9 -->|Read physical copies and tags| D10
    P9 -->|Read students| D1
    P9 -->|Read loans| D5
    P9 -->|Read attendance| D6
    P9 -->|Read transactions and fines| D11
    P9 -->|Read settings| D7
    P9 -->|Dashboard, live attendance, live loans and reports| A
```

---

## Data Dictionary

| ID | Data store | Backing table / location | Key attributes |
| --- | --- | --- | --- |
| D1 | users | `users` | id, name, email, password, role (admin/student), rfid_uid, student_number, course, year_level, phone, is_active |
| D2 | authors | `authors` | id, name, bio |
| D3 | categories | `categories` | id, name, description |
| D4 | books | `books` | id, title, author_id → D2, category_id → D3, isbn, description, cover_image → D9, published_year, shelf_location, total_copies, available_copies (derived from D10), deleted_at |
| D5 | borrowings | `borrowings` | id, user_id → D1, book_id → D4, book_copy_id → D10, student_rfid_uid, book_rfid_uid, borrowed_at, due_at, returned_at, status (borrowed/returned/lost/damaged), due_soon_notified_at, overdue_notified_at, handled_by_id → D1 |
| D6 | attendance_logs | `attendance_logs` | id, user_id → D1, rfid_uid, entry_at, exit_at, source (api/kiosk), ip_address, user_agent, closed_automatically |
| D7 | settings | `settings` | key, value — library_name, borrow_days, max_books, due_soon_days, auto_close_hours |
| D8 | sessions / cache | `sessions` table, cache store | Session id, user_id, payload, last_activity; cached `library.settings`; pending kiosk student sessions `rfid.pending.*` |
| D9 | cover-image storage | `storage/app/public/covers` | Uploaded book cover image files |
| D10 | book_copies | `book_copies` | id, book_id → D4, rfid_uid (unique), status (available/borrowed/lost/damaged) |
| D11 | transactions | `transactions` | id, borrowing_id → D5, user_id → D1, amount, status (unpaid/paid), notes, handled_by_id → D1, paid_at |
| D12 | notifications | `notifications` | id, type, notifiable_type/id → D1, data, read_at |

---

## Process Descriptions

| Process | Inputs | Outputs | Data stores |
| --- | --- | --- | --- |
| 1.0 Authentication & Session | Admin email/password | Authenticated admin session | D1, D8 |
| 2.0 RFID Attendance | RFID UID (`POST /api/rfid/scan` door reader or kiosk card tap), scheduler trigger | Entry/exit log, auto-closed sessions, live attendance feed | D1, D6 |
| 3.0 Catalog Management | Author, category, book data, copy tags, cover image | Created/updated/deleted catalog records and physical copies | D2, D3, D4, D9, D10 |
| 4.0 Student Management | Student profile, RFID UID, active flag | Student records, RFID registration | D1 |
| 5.0 Borrowing Lifecycle | RFID card + book tag taps or admin manual form, return/lost/damaged/extend actions | Loan records with scanned UIDs, updated copy status and availability, live loans feed | D4, D5, D7, D10 |
| 6.0 RFID Circulation | Kiosk scan (`POST /api/rfid/circulation`): student card or book tag, device_id | Identify, borrow, or return result; entry log; pending session; failed-tap alert | D1, D4, D5, D6, D7, D8, D10, D12 |
| 7.0 Reminder Service | Scheduled trigger, due-soon/overdue loans | Reminder emails, admin notifications, notified timestamps | D1, D4, D5, D7, D12 |
| 8.0 Settings Management | Library settings form | Updated settings, cleared cache | D7, D8 |
| 9.0 Dashboard & Reporting | Page/report requests, search filters, live-feed polls | Dashboards, live attendance and loans, fine totals, notification menu | D1, D4, D5, D6, D7, D10, D11, D12 |
| 10.0 Notifications | Due-soon, overdue, fine, failed-tap events | Admin notification records, unread badge, read status | D1, D12 |
| 11.0 Fine Transactions | Admin-entered amount and notes, mark-paid action | Unpaid/paid fine records and totals | D1, D5, D11, D12 |

---

## Notes and Assumptions

- Physical copies are tracked individually in `book_copies`, each with its own RFID tag. `books.total_copies` and `books.available_copies` are counters derived from copy status.
- Student-facing pages and student login have been removed. Students are registered and deactivated by admins; cards identify them at the reader.
- The kiosk flow is: tap the student card (logs entry only and opens a short pending session), then tap a book copy tag. Tapping a copy already on loan to that student returns it; otherwise the copy is borrowed. The exact card and book tag UIDs are snapshotted on the borrowing. Pending sessions are scoped by `device_id` (or client IP when omitted).
- Lost and damaged copies remain in `book_copies` for records but are excluded from circulation counters.
- Fines are not calculated automatically. Admins enter a fine amount on an overdue borrowing; the amount and payment status are stored in `transactions`.
- Admins receive in-app database notifications for due-soon and overdue loans, recorded fines, and failed RFID taps. Reminder emails are still sent to students.
- The RFID reader is an external system that authenticates with the `X-RFID-Key` header. `POST /api/rfid/scan` is the door entry/exit toggle; `POST /api/rfid/circulation` handles kiosk borrow/return. The Python bridge selects the endpoint with `RFID_MODE`.
- The admin attendance and borrowings pages poll live JSON endpoints so taps appear without a reload.
- The scheduler runs two commands: reminder emails at 08:00 and stale attendance closing at 23:30.
- Settings are cached; writes invalidate the `library.settings` cache entry.
