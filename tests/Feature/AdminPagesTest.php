<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Fine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed();
    }

    public function test_admin_can_open_every_admin_page(): void
    {
        $admin = User::query()->where('email', 'admin@library.test')->firstOrFail();
        $student = User::query()->where('email', 'maria.santos@student.test')->firstOrFail();
        $author = Author::query()->firstOrFail();
        $category = Category::query()->firstOrFail();
        $book = Book::query()->firstOrFail();
        $borrowing = Borrowing::query()->firstOrFail();
        $fine = Fine::query()->firstOrFail();

        $this->actingAs($admin);

        $urls = [
            route('admin.dashboard'),
            route('admin.authors.index'),
            route('admin.authors.create'),
            route('admin.authors.edit', $author),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.categories.edit', $category),
            route('admin.students.index'),
            route('admin.students.create'),
            route('admin.students.show', $student),
            route('admin.students.edit', $student),
            route('admin.books.index'),
            route('admin.books.create'),
            route('admin.books.edit', $book),
            route('admin.borrowings.index'),
            route('admin.borrowings.show', $borrowing),
            route('admin.borrowings.index', ['status' => 'overdue']),
            route('admin.fines.index'),
            route('admin.fines.index', ['status' => 'unpaid']),
            route('admin.attendance.index'),
            route('admin.attendance.index', ['date' => 'all']),
            route('admin.settings.edit'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }

        $this->assertNotNull($fine);
    }

    public function test_admin_can_update_library_settings(): void
    {
        $admin = User::query()->where('email', 'admin@library.test')->firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'library_name' => 'Updated Library',
            'borrow_days' => 10,
            'max_books' => 4,
            'fine_per_day' => 15,
            'lost_fee' => 600,
            'damaged_fee' => 250,
            'due_soon_days' => 3,
            'auto_close_hours' => 10,
            'block_on_unpaid_fine' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'library_name', 'value' => 'Updated Library']);
        $this->assertDatabaseHas('settings', ['key' => 'block_on_unpaid_fine', 'value' => '1']);
    }

    public function test_guest_is_redirected_from_admin_pages(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.books.index'))->assertRedirect(route('admin.login'));
    }

    public function test_student_is_redirected_out_of_the_admin_panel(): void
    {
        $student = User::query()->where('email', 'maria.santos@student.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('error');
    }
}
