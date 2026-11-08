<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCopyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(SettingSeeder::class);
    }

    public function test_admin_can_add_a_copy_with_a_unique_tag(): void
    {
        $admin = User::factory()->admin()->create();
        $book = Book::factory()->create(['total_copies' => 1, 'available_copies' => 1]);

        $this->actingAs($admin)
            ->post(route('admin.books.copies.store', $book), [
                'rfid_uid' => 'copy-new-01',
                'status' => BookCopy::STATUS_AVAILABLE,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('book_copies', [
            'book_id' => $book->id,
            'rfid_uid' => 'COPY-NEW-01',
            'status' => BookCopy::STATUS_AVAILABLE,
        ]);
        $this->assertSame(2, $book->fresh()->total_copies);
        $this->assertSame(2, $book->fresh()->available_copies);
    }

    public function test_copy_tag_cannot_match_a_student_card(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create(['rfid_uid' => 'CARD-X']);
        $book = Book::factory()->create(['total_copies' => 1, 'available_copies' => 1]);

        $this->actingAs($admin)
            ->post(route('admin.books.copies.store', $book), [
                'rfid_uid' => 'card-x',
                'status' => BookCopy::STATUS_AVAILABLE,
            ])
            ->assertSessionHasErrors('rfid_uid');

        $this->assertDatabaseMissing('book_copies', ['rfid_uid' => 'CARD-X']);
    }

    public function test_student_card_cannot_match_a_copy_tag(): void
    {
        $admin = User::factory()->admin()->create();
        $book = Book::factory()->create(['total_copies' => 1, 'available_copies' => 1]);
        BookCopy::factory()->create([
            'book_id' => $book->id,
            'rfid_uid' => 'BOOK-TAG-1',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.students.store'), [
                'name' => 'Test Student',
                'email' => 'test.student@example.test',
                'rfid_uid' => 'book-tag-1',
                'is_active' => true,
            ])
            ->assertSessionHasErrors('rfid_uid');

        $this->assertDatabaseMissing('users', ['rfid_uid' => 'BOOK-TAG-1']);
    }

    public function test_marking_a_copy_lost_updates_the_counters(): void
    {
        $admin = User::factory()->admin()->create();
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 2]);
        $copy = $book->copies()->where('status', BookCopy::STATUS_AVAILABLE)->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.books.copies.update', [$book, $copy]), [
                'rfid_uid' => $copy->rfid_uid,
                'status' => BookCopy::STATUS_LOST,
            ])
            ->assertRedirect();

        $this->assertSame(BookCopy::STATUS_LOST, $copy->fresh()->status);
        $this->assertSame(1, $book->fresh()->total_copies);
        $this->assertSame(1, $book->fresh()->available_copies);
    }

    public function test_borrowed_copies_cannot_be_edited_or_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $book = Book::factory()->create(['total_copies' => 1, 'available_copies' => 0]);
        $copy = $book->copies()->where('status', BookCopy::STATUS_BORROWED)->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.books.copies.update', [$book, $copy]), [
                'rfid_uid' => 'CHANGED-TAG',
                'status' => BookCopy::STATUS_AVAILABLE,
            ])
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->delete(route('admin.books.copies.destroy', [$book, $copy]))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('book_copies', [
            'id' => $copy->id,
            'status' => BookCopy::STATUS_BORROWED,
        ]);
    }

    public function test_adjusting_total_copies_adds_and_removes_physical_copies(): void
    {
        $admin = User::factory()->admin()->create();
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 2]);

        $this->actingAs($admin)
            ->put(route('admin.books.update', $book), [
                'title' => $book->title,
                'author_id' => $book->author_id,
                'category_id' => $book->category_id,
                'total_copies' => 3,
            ])
            ->assertRedirect();

        $book->refresh();
        $this->assertSame(3, $book->copies()->count());
        $this->assertSame(3, $book->total_copies);
        $this->assertSame(3, $book->available_copies);

        $this->actingAs($admin)
            ->put(route('admin.books.update', $book), [
                'title' => $book->title,
                'author_id' => $book->author_id,
                'category_id' => $book->category_id,
                'total_copies' => 1,
            ])
            ->assertRedirect();

        $book->refresh();
        $this->assertSame(1, $book->copies()->count());
        $this->assertSame(1, $book->total_copies);
        $this->assertSame(1, $book->available_copies);
    }
}
