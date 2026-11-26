<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions') && ! Schema::hasTable('fines')) {
            Schema::rename('transactions', 'fines');
        }

        if (Schema::hasTable('borrowings') && ! Schema::hasTable('transactions')) {
            Schema::rename('borrowings', 'transactions');
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('days_to_return')->nullable()->after('due_at');
            $table->decimal('penalty', 10, 2)->nullable()->after('returned_at');
            $table->string('penalty_status', 10)->default('unpaid')->after('penalty');
            $table->text('penalty_notes')->nullable()->after('penalty_status');
            $table->foreignId('penalty_handled_by_id')->nullable()->constrained('users')->nullOnDelete()->after('penalty_notes');
            $table->timestamp('penalty_paid_at')->nullable()->after('penalty_handled_by_id');
        });

        DB::table('transactions')->orderBy('id')->chunkById(100, function ($loans) {
            foreach ($loans as $loan) {
                if (! filled($loan->borrowed_at) || ! filled($loan->due_at)) {
                    continue;
                }

                DB::table('transactions')->where('id', $loan->id)->update([
                    'days_to_return' => Carbon::parse($loan->borrowed_at)
                        ->startOfDay()
                        ->diffInDays(Carbon::parse($loan->due_at)->startOfDay()),
                ]);
            }
        });

        if (Schema::hasTable('fines')) {
            DB::table('fines')->orderBy('borrowing_id')->chunkById(100, function ($fines) {
                foreach ($fines->groupBy('borrowing_id') as $borrowingId => $group) {
                    $paid = $group->every(fn ($fine) => $fine->status === 'paid');
                    $latest = $group->sortByDesc('id')->first();

                    DB::table('transactions')->where('id', $borrowingId)->update([
                        'penalty' => $group->sum('amount'),
                        'penalty_status' => $paid ? 'paid' : 'unpaid',
                        'penalty_notes' => $latest->notes,
                        'penalty_handled_by_id' => $latest->handled_by_id,
                        'penalty_paid_at' => $paid ? $latest->paid_at : null,
                    ]);
                }
            });

            Schema::drop('fines');
        }
    }

    public function down(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('borrowing_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2);
            $table->string('status', 10)->default('unpaid')->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('handled_by_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        if (Schema::hasColumn('transactions', 'penalty')) {
            DB::table('transactions')->whereNotNull('penalty')->orderBy('id')->chunkById(100, function ($loans) {
                foreach ($loans as $loan) {
                    DB::table('fines')->insert([
                        'borrowing_id' => $loan->id,
                        'user_id' => $loan->user_id,
                        'amount' => $loan->penalty,
                        'status' => $loan->penalty_status ?? 'unpaid',
                        'notes' => $loan->penalty_notes,
                        'handled_by_id' => $loan->penalty_handled_by_id,
                        'paid_at' => $loan->penalty_paid_at,
                        'created_at' => $loan->created_at,
                        'updated_at' => $loan->updated_at,
                    ]);
                }
            });
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['penalty_handled_by_id']);
            $table->dropColumn([
                'days_to_return',
                'penalty',
                'penalty_status',
                'penalty_notes',
                'penalty_handled_by_id',
                'penalty_paid_at',
            ]);
        });

        Schema::rename('transactions', 'borrowings');

        Schema::table('fines', function (Blueprint $table) {
            $table->foreign('borrowing_id')->references('id')->on('borrowings')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('handled_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
