<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Borrowing;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RfidCirculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(SettingSeeder::class);
        config(['library.rfid_api_key' => 'test-key']);
        Cache::flush();
    }

    public function test_student_tap_identifies_student_and_logs_entry(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->scan($student->rfid_uid);

        $response->assertOk()
            ->assertJsonPath('action', 'identify')
            ->assertJsonPath('next', 'tap_book')
            ->assertJsonPath('attendance', 'entry')
            ->assertJsonPath('student.name', $student->name);

        $this->assertDatabaseCount('attendance_logs', 1);
        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $student->id,
            'exit_at' => null,
        ]);
    }

    public function test_student_can_borrow_and_return_a_tapped_copy(): void
    {
        $student = User::factory()->student()->create();
        [$book, $copy] = $this->bookWithAvailableCopy();

        $this->scan($student->rfid_uid)->assertOk();

        $this->scan($copy->rfid_uid)
            ->assertOk()
            ->assertJsonPath('action', 'borrow')
            ->assertJsonPath('book.copy_id', $copy->id)
            ->assertJsonPath('due_at', today()->addDays(7)->toDateString());

        $this->assertDatabaseHas('borrowings', [
            'user_id' => $student->id,
            'book_id' => $book->id,
            'book_copy_id' => $copy->id,
            'status' => 'borrowed',
        ]);
        $this->assertSame(BookCopy::STATUS_BORROWED, $copy->fresh()->status);
        $this->assertSame($book->total_copies - 1, $book->fresh()->available_copies);

        $this->scan($student->rfid_uid)->assertOk();

        $this->scan($copy->rfid_uid)
            ->assertOk()
            ->assertJsonPath('action', 'return');

        $this->assertSame(BookCopy::STATUS_AVAILABLE, $copy->fresh()->status);
        $this->assertSame($book->total_copies, $book->fresh()->available_copies);
        $this->assertDatabaseHas('borrowings', [
            'user_id' => $student->id,
            'book_copy_id' => $copy->id,
            'status' => 'returned',
        ]);

        $this->assertDatabaseCount('attendance_logs', 1);
        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $student->id,
            'exit_at' => null,
        ]);
    }

    public function test_book_tap_without_a_student_tap_is_rejected(): void
    {
        [, $copy] = $this->bookWithAvailableCopy();

        $this->scan($copy->rfid_uid)->assertStatus(409);
    }

    public function test_inactive_student_cannot_start_a_session(): void
    {
        $student = User::factory()->student()->inactive()->create();

        $this->scan($student->rfid_uid)->assertStatus(403);

        $this->assertDatabaseCount('attendance_logs', 0);
    }

    public function test_unknown_tag_returns_not_found(): void
    {
        $this->scan('NOT-A-REAL-TAG')->assertStatus(404);
    }

    public function test_device_sessions_are_isolated(): void
    {
        $student = User::factory()->student()->create();
        [, $copy] = $this->bookWithAvailableCopy();

        $this->scan($student->rfid_uid, 'kiosk-a')->assertOk();

        $this->scan($copy->rfid_uid, 'kiosk-b')->assertStatus(409);
        $this->assertDatabaseMissing('borrowings', ['book_copy_id' => $copy->id]);
    }

    public function test_borrowing_respects_the_configured_limit(): void
    {
        app(SettingService::class)->set('max_books', '1');

        $student = User::factory()->student()->create();

        $existingBook = Book::factory()->create(['total_copies' => 1, 'available_copies' => 0]);
        Borrowing::factory()->create([
            'user_id' => $student->id,
            'book_id' => $existingBook->id,
            'status' => 'borrowed',
        ]);

        [, $copy] = $this->bookWithAvailableCopy();

        $this->scan($student->rfid_uid)->assertOk();
        $this->scan($copy->rfid_uid)->assertStatus(409);

        $this->assertDatabaseMissing('borrowings', ['book_copy_id' => $copy->id]);
    }

    public function test_tapping_a_copy_on_loan_to_another_student_is_rejected(): void
    {
        $otherStudent = User::factory()->student()->create();
        $student = User::factory()->student()->create();

        $book = Book::factory()->create(['total_copies' => 1, 'available_copies' => 0]);
        $copy = $book->copies()->where('status', BookCopy::STATUS_BORROWED)->firstOrFail();

        Borrowing::factory()->create([
            'user_id' => $otherStudent->id,
            'book_id' => $book->id,
            'book_copy_id' => $copy->id,
            'status' => 'borrowed',
        ]);

        $this->scan($student->rfid_uid)->assertOk();
        $this->scan($copy->rfid_uid)->assertStatus(409);
    }

    /**
     * @return array{0: Book, 1: BookCopy}
     */
    private function bookWithAvailableCopy(): array
    {
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 2]);
        $copy = $book->copies()->where('status', BookCopy::STATUS_AVAILABLE)->firstOrFail();

        return [$book, $copy];
    }

    private function scan(string $uid, string $deviceId = 'kiosk-1')
    {
        return $this->postJson('/api/rfid/circulation', [
            'rfid_uid' => $uid,
            'device_id' => $deviceId,
        ], ['X-RFID-Key' => 'test-key']);
    }
}
