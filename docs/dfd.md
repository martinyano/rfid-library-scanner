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

    R -->|RFID UID scan payload| SYS
    SYS -->|Entry/exit result, student summary| R

    S -->|RFID card, login credentials, borrow request| SYS
    SYS -->|Dashboard, catalog, loans, session| S

    A -->|Book, author, category, student and settings data| SYS
    A -->|Return, lost, damaged, extend actions| SYS
    SYS -->|Inventory, borrowing and attendance reports| A

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
    P6([6.0 Reminder Service])
    P7([7.0 Settings Management])
    P8([8.0 Dashboard & Reporting])

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

    %% Authentication and session
    S -->|Login / logout| P1
    A -->|Admin credentials| P1
    P1 -->|Verify credentials, role| D1
    P1 -->|Session state, role| D8
    P1 -->|Authenticated session| S
    P1 -->|Authenticated session| A

    %% RFID attendance
    S -->|RFID card presented| R
    R -->|Scan payload: rfid_uid| P2
    S -->|RFID UID via web scan page| P2
    P2 -->|Lookup student by RFID| D1
    P2 -->|Read open session| D6
    P2 -->|Create entry / record exit| D6
    P2 -->|Result: entry or exit, logged time| R
    P2 -->|Login student session| P1

    %% Catalog management
    A -->|Author data| P3
    A -->|Category data| P3
    A -->|Book data and cover image| P3
    P3 -->|Create / update / delete authors| D2
    P3 -->|Create / update / delete categories| D3
    P3 -->|Create / update / delete books| D4
    P3 -->|Store / remove cover image| D9

    %% Student management
    A -->|Student data, RFID UID, active status| P4
    P4 -->|Create / update / deactivate students| D1

    %% Borrowing lifecycle
    S -->|Borrow request| P5
    A -->|Return, lost, damaged, extend action| P5
    P5 -->|Read borrow rules| D7
    P5 -->|Check active loans and availability| D5
    P5 -->|Read book availability| D4
    P5 -->|Create loan, update status and due date| D5
    P5 -->|Adjust available_copies / total_copies| D4
    P5 -->|Loan result and due date| S

    %% Reminders
    C -->|Daily 08:00 trigger| P6
    P6 -->|Find due-soon and overdue loans| D5
    P6 -->|Student email details| D1
    P6 -->|Book details| D4
    P6 -->|Read reminder settings| D7
    P6 -->|Mark notified timestamps| D5
    P6 -->|Send reminder email| E
    E -->|Email delivered| S

    %% Scheduled attendance closing
    C -->|Daily 23:30 trigger| P2
    P2 -->|Auto-close stale open sessions| D6

    %% Settings management
    A -->|Library settings| P7
    P7 -->|Create / update settings| D7
    P7 -->|Invalidate settings cache| D8

    %% Dashboard and reporting
    A -->|Request dashboard / reports| P8
    S -->|Browse, search catalog, view loans| P8
    P8 -->|Read books, authors, categories| D4
    P8 -->|Read students| D1
    P8 -->|Read loans| D5
    P8 -->|Read attendance| D6
    P8 -->|Read settings| D7
    P8 -->|Dashboard, catalog and report views| A
    P8 -->|Catalog and loan views| S
```

---

## Data Dictionary

| ID | Data store | Backing table / location | Key attributes |
| --- | --- | --- | --- |
| D1 | users | `users` | id, name, email, password, role (admin/student), rfid_uid, student_number, course, year_level, phone, is_active |
| D2 | authors | `authors` | id, name, bio |
| D3 | categories | `categories` | id, name, description |
| D4 | books | `books` | id, title, author_id → D2, category_id → D3, isbn, description, cover_image → D9, published_year, shelf_location, total_copies, available_copies, deleted_at |
| D5 | borrowings | `borrowings` | id, user_id → D1, book_id → D4, borrowed_at, due_at, returned_at, status (borrowed/returned/lost/damaged), due_soon_notified_at, overdue_notified_at, handled_by_id → D1 |
| D6 | attendance_logs | `attendance_logs` | id, user_id → D1, rfid_uid, entry_at, exit_at, source (scan_page/api), ip_address, user_agent, closed_automatically |
| D7 | settings | `settings` | key, value — library_name, borrow_days, max_books, due_soon_days, auto_close_hours |
| D8 | sessions / cache | `sessions` table, cache store | Session id, user_id, payload, last_activity; cached `library.settings` |
| D9 | cover-image storage | `storage/app/public/covers` | Uploaded book cover image files |

---

## Process Descriptions

| Process | Inputs | Outputs | Data stores |
| --- | --- | --- | --- |
| 1.0 Authentication & Session | Student RFID card, admin email/password | Authenticated session, role-based redirect | D1, D8 |
| 2.0 RFID Attendance | RFID UID (web form or `POST /api/rfid/scan`), scheduler trigger | Entry/exit log, login session, auto-closed sessions | D1, D6 |
| 3.0 Catalog Management | Author, category, book data, cover image | Created/updated/deleted catalog records | D2, D3, D4, D9 |
| 4.0 Student Management | Student profile, RFID UID, active flag | Student records, RFID registration | D1 |
| 5.0 Borrowing Lifecycle | Borrow request, return/lost/damaged/extend actions | Loan records, updated availability | D4, D5, D7 |
| 6.0 Reminder Service | Scheduled trigger, due-soon/overdue loans | Reminder emails, notified timestamps | D1, D4, D5, D7 |
| 7.0 Settings Management | Library settings form | Updated settings, cleared cache | D7, D8 |
| 8.0 Dashboard & Reporting | Page/report requests, search filters | Dashboards, catalog, loan and attendance views | D1, D4, D5, D6, D7 |

---

## Notes and Assumptions

- Book copies are tracked as counters (`total_copies`, `available_copies`) rather than individual copy records.
- The system does not calculate or charge fines. Loans can still be marked overdue, lost, or damaged and are tracked by status.
- The RFID reader is an external system that authenticates with the `X-RFID-Key` header; the web `/scan` page is an alternative input path.
- The scheduler runs two commands: reminder emails at 08:00 and stale attendance closing at 23:30.
- Settings are cached; writes invalidate the `library.settings` cache entry.
