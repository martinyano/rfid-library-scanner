<?php

namespace Tests\Feature;

use App\Mail\DueSoonReminder;
use App\Mail\OverdueReminder;
use App\Models\Borrowing;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(SettingSeeder::class);
    }

    public function test_due_soon_and_overdue_reminders_are_sent_once(): void
    {
        Mail::fake();

        $dueSoon = Borrowing::factory()->create([
            'borrowed_at' => today()->subDays(6),
            'due_at' => today()->addDay(),
            'status' => 'borrowed',
        ]);

        $overdue = Borrowing::factory()->create([
            'borrowed_at' => today()->subDays(12),
            'due_at' => today()->subDays(4),
            'status' => 'borrowed',
        ]);

        $this->artisan('library:send-due-reminders')->assertSuccessful();

        Mail::assertSent(DueSoonReminder::class, function (DueSoonReminder $mail) use ($dueSoon) {
            return $mail->borrowing->id === $dueSoon->id && $mail->daysLeft === 1;
        });
        Mail::assertSent(OverdueReminder::class, function (OverdueReminder $mail) use ($overdue) {
            return $mail->borrowing->id === $overdue->id
                && $mail->daysOverdue === 4
                && $mail->estimatedFine === 40.0;
        });

        $this->assertNotNull($dueSoon->fresh()->due_soon_notified_at);
        $this->assertNotNull($overdue->fresh()->overdue_notified_at);

        $this->artisan('library:send-due-reminders')->assertSuccessful();
        Mail::assertSent(DueSoonReminder::class, 1);
        Mail::assertSent(OverdueReminder::class, 1);
    }

    public function test_reminder_email_templates_render(): void
    {
        $user = User::factory()->student()->create();
        $borrowing = Borrowing::factory()->create([
            'user_id' => $user->id,
            'due_at' => today()->addDays(2),
        ]);

        $dueSoon = (new DueSoonReminder($borrowing, 2, 'Test Library'))->render();
        $this->assertStringContainsString($borrowing->book->title, $dueSoon);
        $this->assertStringContainsString('Test Library', $dueSoon);

        $overdue = (new OverdueReminder($borrowing, 3, 30.0, 'Test Library'))->render();
        $this->assertStringContainsString('30.00', $overdue);
        $this->assertStringContainsString('Overdue book notice', $overdue);
    }

    public function test_stale_attendance_sessions_are_closed(): void
    {
        $student = User::factory()->student()->create();
        $log = $student->attendanceLogs()->create([
            'rfid_uid' => $student->rfid_uid,
            'entry_at' => now()->subHours(20),
            'source' => 'scan_page',
        ]);

        $this->artisan('library:close-stale-attendance')->assertSuccessful();

        $log->refresh();
        $this->assertNotNull($log->exit_at);
        $this->assertTrue($log->closed_automatically);
    }
}
