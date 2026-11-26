<?php

namespace Tests\Feature;

use App\Models\Borrowing;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(SettingSeeder::class);
    }

    public function test_admin_can_record_an_overdue_fine_and_notify_admins(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $borrowing = Borrowing::factory()->create([
            'user_id' => $student->id,
            'due_at' => today()->subDays(3),
            'status' => 'borrowed',
        ]);

        $this->actingAs($admin)->post(route('admin.borrowings.transactions.store', $borrowing), [
            'amount' => '25.50',
            'notes' => 'Returned late',
        ])->assertRedirect();

        $transaction = Transaction::query()->findOrFail($borrowing->id);
        $this->assertSame('25.50', $transaction->penalty);
        $this->assertSame('unpaid', $transaction->penalty_status);
        $this->assertSame('Returned late', $transaction->penalty_notes);
        $this->assertSame($student->id, $transaction->user_id);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_admin_can_mark_a_fine_as_paid(): void
    {
        $admin = User::factory()->admin()->create();
        $transaction = Transaction::factory()->create();

        $this->actingAs($admin)->post(route('admin.transactions.pay', $transaction))->assertRedirect();

        $transaction->refresh();
        $this->assertSame('paid', $transaction->penalty_status);
        $this->assertNotNull($transaction->penalty_paid_at);
        $this->assertSame($admin->id, $transaction->penalty_handled_by_id);
    }

    public function test_a_fine_cannot_be_recorded_for_a_loan_that_is_not_overdue(): void
    {
        $admin = User::factory()->admin()->create();
        $borrowing = Borrowing::factory()->create(['due_at' => today()->addDays(3)]);

        $this->actingAs($admin)->post(route('admin.borrowings.transactions.store', $borrowing), [
            'amount' => 10,
        ])->assertSessionHasErrors('amount');

        $this->assertNull($borrowing->fresh()->penalty);
    }

    public function test_transaction_index_shows_recorded_fines(): void
    {
        $admin = User::factory()->admin()->create();
        Transaction::factory()->create(['penalty' => 42]);

        $this->actingAs($admin)->get(route('admin.transactions.index'))
            ->assertOk()
            ->assertSee('42.00');
    }
}
