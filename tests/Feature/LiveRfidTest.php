<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Borrowing;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveRfidTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(SettingSeeder::class);
        config(['library.rfid_api_key' => 'test-key']);
    }

    public function test_door_tap_appears_in_the_live_attendance_feed(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();

        $this->postJson('/api/rfid/scan', ['rfid_uid' => $student->rfid_uid], ['X-RFID-Key' => 'test-key'])
            ->assertOk()
            ->assertJsonPath('action', 'entry');

        $log = AttendanceLog::query()->firstOrFail();

        $response = $this->actingAs($admin)->getJson(route('admin.attendance.live', ['after' => 0]))->assertOk();

        $this->assertStringContainsString($student->name, $response->json('html'));
        $this->assertSame($log->id, $response->json('latestId'));
        $this->assertSame(1, $response->json('counters.insideNow'));
        $this->assertSame(1, $response->json('counters.todayEntries'));
    }

    public function test_new_borrowing_appears_in_the_live_loans_feed(): void
    {
        $admin = User::factory()->admin()->create();
        $borrowing = Borrowing::factory()->create([
            'student_rfid_uid' => 'CARD-123',
            'book_rfid_uid' => 'BOOK-456',
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.borrowings.live', ['after' => 0]))->assertOk();

        $html = $response->json('html');
        $this->assertStringContainsString('CARD-123', $html);
        $this->assertStringContainsString('BOOK-456', $html);
        $this->assertSame($borrowing->id, $response->json('latestId'));
        $this->assertSame(1, $response->json('counters.activeBorrowings'));
    }
}
