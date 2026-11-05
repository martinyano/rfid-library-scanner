<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed();
    }

    public function test_student_can_open_the_catalog_and_their_borrowings(): void
    {
        $student = User::query()->where('email', 'maria.santos@student.test')->firstOrFail();
        $activeLoan = Borrowing::query()
            ->where('user_id', $student->id)
            ->where('status', 'borrowed')
            ->firstOrFail();

        $this->actingAs($student);

        $this->get(route('student.dashboard'))->assertOk();
        $this->get(route('student.books.show', $activeLoan->book))->assertOk();
        $this->get(route('student.borrowings.index'))->assertOk();
        $this->get(route('student.dashboard', ['q' => $activeLoan->book->title]))->assertOk();
    }

    public function test_student_can_open_the_borrow_confirmation_page(): void
    {
        $student = User::query()->where('email', 'maria.santos@student.test')->firstOrFail();
        $book = Book::factory()->create(['total_copies' => 1, 'available_copies' => 1]);

        $this->actingAs($student)
            ->get(route('student.books.borrow.form', $book))
            ->assertOk()
            ->assertSee($book->title);
    }

    public function test_inactive_student_cannot_access_student_pages(): void
    {
        $inactive = User::query()->where('email', 'pedro.lim@student.test')->firstOrFail();

        $this->actingAs($inactive)
            ->get(route('student.dashboard'))
            ->assertRedirect(route('scan'));
        $this->assertGuest();
    }
}
