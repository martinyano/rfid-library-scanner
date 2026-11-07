<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(SettingSeeder::class);
    }

    public function test_student_can_borrow_an_available_book(): void
    {
        $student = User::factory()->student()->create();
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 2]);

        $response = $this->actingAs($student)->post(route('student.books.borrow', $book));

        $response->assertRedirect(route('student.borrowings.index'));
        $this->assertDatabaseHas('borrowings', [
            'user_id' => $student->id,
            'book_id' => $book->id,
            'status' => 'borrowed',
        ]);
        $this->assertSame(1, $book->fresh()->available_copies);
    }

    public function test_student_cannot_borrow_beyond_the_configured_limit(): void
    {
        app(SettingService::class)->set('max_books', '1');

        $student = User::factory()->student()->create();
        $firstBook = Book::factory()->create(['total_copies' => 1, 'available_copies' => 0]);
        $secondBook = Book::factory()->create(['total_copies' => 1, 'available_copies' => 1]);

        Borrowing::factory()->create([
            'user_id' => $student->id,
            'book_id' => $firstBook->id,
            'status' => 'borrowed',
        ]);

        $response = $this->actingAs($student)->post(route('student.books.borrow', $secondBook));

        $response->assertSessionHasErrors('borrow');
        $this->assertDatabaseMissing('borrowings', [
            'user_id' => $student->id,
            'book_id' => $secondBook->id,
        ]);
        $this->assertSame(1, $secondBook->fresh()->available_copies);
    }

    public function test_student_cannot_borrow_the_same_book_twice(): void
    {
        $student = User::factory()->student()->create();
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 1]);

        Borrowing::factory()->create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'status' => 'borrowed',
        ]);

        $response = $this->actingAs($student)->post(route('student.books.borrow', $book));

        $response->assertSessionHasErrors('borrow');
        $this->assertSame(1, Borrowing::query()->where('user_id', $student->id)->count());
    }

    public function test_returning_an_overdue_book_restores_a_copy(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 1]);
        $borrowing = Borrowing::factory()->create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'borrowed_at' => today()->subDays(10),
            'due_at' => today()->subDays(3),
            'status' => 'borrowed',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.borrowings.return', $borrowing));

        $response->assertRedirect();
        $borrowing->refresh();
        $this->assertSame('returned', $borrowing->status);
        $this->assertNotNull($borrowing->returned_at);
        $this->assertSame(2, $book->fresh()->available_copies);
    }

    public function test_returning_on_time_marks_the_loan_as_returned(): void
    {
        $admin = User::factory()->admin()->create();
        $borrowing = Borrowing::factory()->create([
            'borrowed_at' => today()->subDays(2),
            'due_at' => today()->addDays(5),
        ]);

        $this->actingAs($admin)->post(route('admin.borrowings.return', $borrowing))->assertRedirect();

        $this->assertSame('returned', $borrowing->fresh()->status);
    }

    public function test_admin_can_mark_a_book_lost_and_remove_it_from_circulation(): void
    {
        $admin = User::factory()->admin()->create();
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 1]);
        $borrowing = Borrowing::factory()->create([
            'book_id' => $book->id,
            'status' => 'borrowed',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.borrowings.lost', $borrowing));

        $response->assertRedirect();
        $this->assertSame('lost', $borrowing->fresh()->status);
        $this->assertSame(1, $book->fresh()->total_copies);
        $this->assertSame(1, $book->fresh()->available_copies);
    }

    public function test_admin_can_extend_a_loan(): void
    {
        $admin = User::factory()->admin()->create();
        $borrowing = Borrowing::factory()->create([
            'due_at' => today()->addDays(2),
            'status' => 'borrowed',
        ]);
        $originalDueDate = $borrowing->due_at->copy();

        $this->actingAs($admin)
            ->post(route('admin.borrowings.extend', $borrowing), ['days' => 7])
            ->assertRedirect();

        $this->assertTrue($borrowing->fresh()->due_at->equalTo($originalDueDate->addDays(7)));
    }
}
