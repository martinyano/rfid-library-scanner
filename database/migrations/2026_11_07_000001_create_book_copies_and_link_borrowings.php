<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('rfid_uid', 100)->nullable()->unique();
            $table->string('status', 20)->default('available')->index();
            $table->timestamps();
        });

        Schema::table('borrowings', function (Blueprint $table) {
            $table->foreignId('book_copy_id')
                ->nullable()
                ->constrained('book_copies')
                ->nullOnDelete();
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('book_copy_id');
        });

        Schema::dropIfExists('book_copies');
    }

    private function backfill(): void
    {
        $now = now();

        $books = DB::table('books')->get(['id', 'total_copies']);

        foreach ($books as $book) {
            $activeLoans = DB::table('borrowings')
                ->where('book_id', $book->id)
                ->where('status', 'borrowed')
                ->orderBy('id')
                ->get(['id']);

            $copyCount = max((int) $book->total_copies, $activeLoans->count());

            $copyIds = [];

            for ($i = 0; $i < $copyCount; $i++) {
                $copyIds[] = DB::table('book_copies')->insertGetId([
                    'book_id' => $book->id,
                    'rfid_uid' => null,
                    'status' => 'available',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($activeLoans as $index => $loan) {
                $copyId = $copyIds[$index] ?? null;

                if (! $copyId) {
                    continue;
                }

                DB::table('book_copies')
                    ->where('id', $copyId)
                    ->update(['status' => 'borrowed', 'updated_at' => $now]);

                DB::table('borrowings')
                    ->where('id', $loan->id)
                    ->update(['book_copy_id' => $copyId, 'updated_at' => $now]);
            }

            $available = DB::table('book_copies')
                ->where('book_id', $book->id)
                ->where('status', 'available')
                ->count();

            $total = DB::table('book_copies')
                ->where('book_id', $book->id)
                ->whereIn('status', ['available', 'borrowed'])
                ->count();

            DB::table('books')
                ->where('id', $book->id)
                ->update([
                    'total_copies' => $total,
                    'available_copies' => $available,
                    'updated_at' => $now,
                ]);
        }
    }
};
