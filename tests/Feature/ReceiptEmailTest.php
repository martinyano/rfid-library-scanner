<?php

namespace Tests\Feature;

use App\Mail\BorrowReceipt;
use App\Mail\ReturnReceipt;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReceiptEmailTest extends TestCase
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

    public function test_admin_borrow_sends_receipt_to_student_email(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create([
            'name' => 'Mark Santos',
            'email' => 'mark@example.test',
        ]);
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 2]);

        $this->actingAs($admin)->post(route('admin.borrowings.store'), [
            'student_id' => $student->id,
            'book_id' => $book->id,
        ])->assertRedirect();

        $borrowing = Borrowing::query()->firstOrFail();

        Mail::assertSent(BorrowReceipt::class, function (BorrowReceipt $mail) use ($student, $borrowing) {
            $body = $mail->render();
            $text = strip_tags($body);

            return $mail->hasTo($student->email)
                && $mail->borrowing->id === $borrowing->id
                && str_contains($mail->envelope()->subject, '[Library Receipt] Borrowed: ')
                && str_contains($text, 'Your RFID book borrowing transaction has been recorded.')
                && str_contains($text, 'Action: Borrowed')
                && str_contains($text, 'RFID Tag: '.$borrowing->book_rfid_uid)
                && str_contains($text, 'Borrowed At: '.$borrowing->borrowed_at->format('M j, Y, g:i A'))
                && str_contains($body, '— Library Circulation System');
        });
    }

    public function test_admin_return_sends_receipt_to_student_email(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create(['email' => 'mark@example.test']);
        $book = Book::factory()->create(['title' => 'Introduction to Algorithms']);
        $borrowing = Borrowing::factory()->create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'book_rfid_uid' => '855C2707',
            'status' => 'borrowed',
        ]);

        $this->actingAs($admin)->post(route('admin.borrowings.return', $borrowing))->assertRedirect();

        Mail::assertSent(ReturnReceipt::class, function (ReturnReceipt $mail) use ($student, $borrowing, $book) {
            $body = $mail->render();
            $text = strip_tags($body);

            return $mail->hasTo($student->email)
                && $mail->borrowing->id === $borrowing->id
                && $mail->envelope()->subject === '[Library Receipt] Returned: '.$book->title
                && str_contains($text, 'Thanks for returning your book. Your return has been confirmed in the library system.')
                && str_contains($text, 'Action: Returned')
                && str_contains($text, 'RFID Tag: 855C2707')
                && str_contains($text, 'Status: Completed (No longer due)');
        });
    }

    public function test_rfid_borrow_and_return_send_receipts(): void
    {
        Mail::fake();

        $student = User::factory()->student()->create(['email' => 'mark@example.test']);
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 2]);
        $copy = $book->copies()->where('status', 'available')->firstOrFail();

        $this->scan($student->rfid_uid)->assertOk();
        $this->scan($copy->rfid_uid)->assertOk();

        Mail::assertSent(BorrowReceipt::class, fn (BorrowReceipt $mail) => $mail->hasTo($student->email));

        $this->scan($student->rfid_uid)->assertOk();
        $this->scan($copy->rfid_uid)->assertOk();

        Mail::assertSent(ReturnReceipt::class, fn (ReturnReceipt $mail) => $mail->hasTo($student->email));
    }

    public function test_marking_a_book_lost_does_not_send_a_return_receipt(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $borrowing = Borrowing::factory()->create(['status' => 'borrowed']);

        $this->actingAs($admin)->post(route('admin.borrowings.lost', $borrowing))->assertRedirect();

        Mail::assertNotSent(ReturnReceipt::class);
    }

    private function scan(string $uid)
    {
        return $this->postJson('/api/rfid/circulation', [
            'rfid_uid' => $uid,
            'device_id' => 'kiosk-1',
        ], ['X-RFID-Key' => 'test-key']);
    }
}
