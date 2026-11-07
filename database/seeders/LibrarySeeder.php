<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\Author;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@library.test'],
            [
                'name' => 'Library Administrator',
                'password' => 'password',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $maria = User::query()->updateOrCreate(
            ['email' => 'maria.santos@student.test'],
            [
                'name' => 'Maria Santos',
                'password' => null,
                'role' => 'student',
                'rfid_uid' => 'RFID-MARIA-001',
                'student_number' => '2026-0001',
                'course' => 'BS Information Technology',
                'year_level' => '3rd Year',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $juan = User::query()->updateOrCreate(
            ['email' => 'juan.dela-cruz@student.test'],
            [
                'name' => 'Juan Dela Cruz',
                'password' => null,
                'role' => 'student',
                'rfid_uid' => 'RFID-JUAN-002',
                'student_number' => '2026-0002',
                'course' => 'BS Education',
                'year_level' => '2nd Year',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $ana = User::query()->updateOrCreate(
            ['email' => 'ana.reyes@student.test'],
            [
                'name' => 'Ana Reyes',
                'password' => null,
                'role' => 'student',
                'rfid_uid' => 'RFID-ANA-003',
                'student_number' => '2026-0003',
                'course' => 'BS Business Administration',
                'year_level' => '4th Year',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'pedro.lim@student.test'],
            [
                'name' => 'Pedro Lim',
                'password' => null,
                'role' => 'student',
                'rfid_uid' => 'RFID-PEDRO-004',
                'student_number' => '2026-0004',
                'course' => 'BS Information Technology',
                'year_level' => '1st Year',
                'is_active' => false,
                'email_verified_at' => now(),
            ]
        );

        $rizal = Author::query()->firstOrCreate(
            ['name' => 'José Rizal'],
            ['bio' => 'Philippine national hero and novelist.']
        );
        $bautista = Author::query()->firstOrCreate(
            ['name' => 'Lualhati Bautista'],
            ['bio' => 'Award-winning Filipino novelist.']
        );
        $sionil = Author::query()->firstOrCreate(
            ['name' => 'F. Sionil José'],
            ['bio' => 'Filipino novelist known for the Rosales saga.']
        );
        $lee = Author::query()->firstOrCreate(
            ['name' => 'Harper Lee'],
            ['bio' => 'American novelist.']
        );
        $orwell = Author::query()->firstOrCreate(
            ['name' => 'George Orwell'],
            ['bio' => 'English novelist and essayist.']
        );
        $hawking = Author::query()->firstOrCreate(
            ['name' => 'Stephen Hawking'],
            ['bio' => 'Theoretical physicist and author.']
        );
        $agoncillo = Author::query()->firstOrCreate(
            ['name' => 'Teodoro Agoncillo'],
            ['bio' => 'Filipino historian.']
        );

        $philippineLiterature = Category::query()->firstOrCreate(
            ['name' => 'Philippine Literature'],
            ['description' => 'Filipino novels, short stories, and poetry.']
        );
        $fiction = Category::query()->firstOrCreate(
            ['name' => 'Fiction'],
            ['description' => 'Novels and literary fiction.']
        );
        $science = Category::query()->firstOrCreate(
            ['name' => 'Science'],
            ['description' => 'Popular science and reference titles.']
        );
        $history = Category::query()->firstOrCreate(
            ['name' => 'History'],
            ['description' => 'Philippine and world history.']
        );

        $noli = Book::query()->firstOrCreate(
            ['isbn' => '9789712733456'],
            [
                'title' => 'Noli Me Tangere',
                'author_id' => $rizal->id,
                'category_id' => $philippineLiterature->id,
                'description' => 'A foundational novel of Philippine literature, first published in 1887.',
                'published_year' => 1887,
                'shelf_location' => 'A-01',
                'total_copies' => 4,
                'available_copies' => 4,
            ]
        );

        $elfili = Book::query()->firstOrCreate(
            ['isbn' => '9789712733463'],
            [
                'title' => 'El Filibusterismo',
                'author_id' => $rizal->id,
                'category_id' => $philippineLiterature->id,
                'description' => 'The sequel to Noli Me Tangere, published in 1891.',
                'published_year' => 1891,
                'shelf_location' => 'A-02',
                'total_copies' => 3,
                'available_copies' => 3,
            ]
        );

        $dekada = Book::query()->firstOrCreate(
            ['isbn' => '9789716041896'],
            [
                'title' => "Dekada '70",
                'author_id' => $bautista->id,
                'category_id' => $philippineLiterature->id,
                'description' => 'A novel about a family living through martial law in the Philippines.',
                'published_year' => 1983,
                'shelf_location' => 'A-03',
                'total_copies' => 2,
                'available_copies' => 2,
            ]
        );

        $poon = Book::query()->firstOrCreate(
            ['isbn' => '9789718845084'],
            [
                'title' => 'Po-on',
                'author_id' => $sionil->id,
                'category_id' => $philippineLiterature->id,
                'description' => 'The first novel in the Rosales saga.',
                'published_year' => 1984,
                'shelf_location' => 'A-04',
                'total_copies' => 2,
                'available_copies' => 2,
            ]
        );

        $mockingbird = Book::query()->firstOrCreate(
            ['isbn' => '9780061120084'],
            [
                'title' => 'To Kill a Mockingbird',
                'author_id' => $lee->id,
                'category_id' => $fiction->id,
                'description' => 'A classic novel about justice and childhood in the American South.',
                'published_year' => 1960,
                'shelf_location' => 'B-01',
                'total_copies' => 3,
                'available_copies' => 3,
            ]
        );

        $nineteenEightyFour = Book::query()->firstOrCreate(
            ['isbn' => '9780451524935'],
            [
                'title' => '1984',
                'author_id' => $orwell->id,
                'category_id' => $fiction->id,
                'description' => 'A dystopian novel about surveillance and political control.',
                'published_year' => 1949,
                'shelf_location' => 'B-02',
                'total_copies' => 3,
                'available_copies' => 3,
            ]
        );

        $time = Book::query()->firstOrCreate(
            ['isbn' => '9780553380163'],
            [
                'title' => 'A Brief History of Time',
                'author_id' => $hawking->id,
                'category_id' => $science->id,
                'description' => 'An introduction to cosmology for general readers.',
                'published_year' => 1988,
                'shelf_location' => 'C-01',
                'total_copies' => 2,
                'available_copies' => 2,
            ]
        );

        $phHistory = Book::query()->firstOrCreate(
            ['isbn' => '9789712725413'],
            [
                'title' => 'History of the Filipino People',
                'author_id' => $agoncillo->id,
                'category_id' => $history->id,
                'description' => 'A comprehensive history of the Philippines.',
                'published_year' => 1990,
                'shelf_location' => 'D-01',
                'total_copies' => 2,
                'available_copies' => 2,
            ]
        );

        $mariaLoan = Borrowing::query()->firstOrCreate(
            [
                'user_id' => $maria->id,
                'book_id' => $noli->id,
                'borrowed_at' => today()->subDays(2)->toDateString(),
            ],
            [
                'due_at' => today()->addDays(5)->toDateString(),
                'status' => 'borrowed',
                'handled_by_id' => $admin->id,
                'notes' => 'Borrowed at the front desk.',
            ]
        );
        if ($mariaLoan->wasRecentlyCreated) {
            $noli->decrement('available_copies');
        }

        $mariaSecondLoan = Borrowing::query()->firstOrCreate(
            [
                'user_id' => $maria->id,
                'book_id' => $nineteenEightyFour->id,
                'borrowed_at' => today()->subDays(1)->toDateString(),
            ],
            [
                'due_at' => today()->addDays(6)->toDateString(),
                'status' => 'borrowed',
                'handled_by_id' => $admin->id,
            ]
        );
        if ($mariaSecondLoan->wasRecentlyCreated) {
            $nineteenEightyFour->decrement('available_copies');
        }

        $juanLoan = Borrowing::query()->firstOrCreate(
            [
                'user_id' => $juan->id,
                'book_id' => $elfili->id,
                'borrowed_at' => today()->subDays(12)->toDateString(),
            ],
            [
                'due_at' => today()->subDays(5)->toDateString(),
                'status' => 'borrowed',
                'handled_by_id' => $admin->id,
                'notes' => 'Overdue demo loan.',
            ]
        );
        if ($juanLoan->wasRecentlyCreated) {
            $elfili->decrement('available_copies');
        }

        Borrowing::query()->firstOrCreate(
            [
                'user_id' => $ana->id,
                'book_id' => $dekada->id,
                'borrowed_at' => today()->subDays(14)->toDateString(),
            ],
            [
                'due_at' => today()->subDays(7)->toDateString(),
                'returned_at' => today()->subDays(4)->toDateString(),
                'status' => 'returned',
                'handled_by_id' => $admin->id,
                'notes' => 'Returned late.',
            ]
        );

        Borrowing::query()->firstOrCreate(
            [
                'user_id' => $ana->id,
                'book_id' => $poon->id,
                'borrowed_at' => today()->subDays(20)->toDateString(),
            ],
            [
                'due_at' => today()->subDays(13)->toDateString(),
                'returned_at' => today()->subDays(14)->toDateString(),
                'status' => 'returned',
                'handled_by_id' => $admin->id,
                'notes' => 'Returned on time.',
            ]
        );

        AttendanceLog::query()->firstOrCreate(
            [
                'user_id' => $maria->id,
                'entry_at' => today()->setTime(9, 0),
            ],
            [
                'rfid_uid' => $maria->rfid_uid,
                'exit_at' => null,
                'source' => 'scan_page',
                'closed_automatically' => false,
            ]
        );

        AttendanceLog::query()->firstOrCreate(
            [
                'user_id' => $juan->id,
                'entry_at' => today()->setTime(10, 0),
            ],
            [
                'rfid_uid' => $juan->rfid_uid,
                'exit_at' => today()->setTime(11, 30),
                'source' => 'iot',
                'closed_automatically' => false,
            ]
        );

        AttendanceLog::query()->firstOrCreate(
            [
                'user_id' => $ana->id,
                'entry_at' => today()->subDay()->setTime(14, 0),
            ],
            [
                'rfid_uid' => $ana->rfid_uid,
                'exit_at' => today()->subDay()->setTime(15, 0),
                'source' => 'scan_page',
                'closed_automatically' => false,
            ]
        );

        $this->command?->info('Library demo data seeded.');
    }
}
