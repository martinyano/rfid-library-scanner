<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(SettingSeeder::class);
    }

    public function test_admin_can_log_in_and_view_the_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@library.test',
            'password' => 'password',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@library.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_student_cannot_log_in_to_the_admin_panel(): void
    {
        User::factory()->student()->create([
            'email' => 'student@library.test',
            'password' => 'password',
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'student@library.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_rfid_scan_logs_attendance_and_authenticates_the_student(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->post('/scan', ['rfid_uid' => strtolower($student->rfid_uid)]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($student);
        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $student->id,
            'exit_at' => null,
        ]);
    }

    public function test_unknown_rfid_card_is_rejected(): void
    {
        $response = $this->from('/scan')->post('/scan', ['rfid_uid' => 'NOT-A-REAL-CARD']);

        $response->assertRedirect('/scan');
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_inactive_student_cannot_scan_in(): void
    {
        $student = User::factory()->student()->inactive()->create();

        $response = $this->from('/scan')->post('/scan', ['rfid_uid' => $student->rfid_uid]);

        $response->assertRedirect('/scan');
        $response->assertSessionHas('error');
        $this->assertGuest();
        $this->assertDatabaseCount('attendance_logs', 0);
    }

    public function test_rfid_api_requires_the_api_key(): void
    {
        config(['library.rfid_api_key' => 'test-key']);
        $student = User::factory()->student()->create();

        $this->postJson('/api/rfid/scan', ['rfid_uid' => $student->rfid_uid])
            ->assertStatus(401);

        $this->postJson('/api/rfid/scan', ['rfid_uid' => $student->rfid_uid], ['X-RFID-Key' => 'wrong-key'])
            ->assertStatus(401);
    }

    public function test_rfid_api_toggles_entry_and_exit(): void
    {
        config(['library.rfid_api_key' => 'test-key']);
        $student = User::factory()->student()->create();

        $this->postJson('/api/rfid/scan', ['rfid_uid' => $student->rfid_uid], ['X-RFID-Key' => 'test-key'])
            ->assertOk()
            ->assertJsonPath('action', 'entry');

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $student->id,
            'exit_at' => null,
        ]);

        $this->postJson('/api/rfid/scan', ['rfid_uid' => $student->rfid_uid], ['X-RFID-Key' => 'test-key'])
            ->assertOk()
            ->assertJsonPath('action', 'exit');

        $log = AttendanceLog::query()->where('user_id', $student->id)->first();
        $this->assertNotNull($log->exit_at);
    }
}
