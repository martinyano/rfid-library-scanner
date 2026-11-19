<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->string('student_rfid_uid', 100)->nullable()->after('book_copy_id')->index();
            $table->string('book_rfid_uid', 100)->nullable()->after('student_rfid_uid')->index();
        });

        DB::table('borrowings')->orderBy('id')->chunkById(100, function ($borrowings) {
            foreach ($borrowings as $borrowing) {
                $uids = [
                    'student_rfid_uid' => DB::table('users')->where('id', $borrowing->user_id)->value('rfid_uid'),
                    'book_rfid_uid' => $borrowing->book_copy_id
                        ? DB::table('book_copies')->where('id', $borrowing->book_copy_id)->value('rfid_uid')
                        : null,
                ];

                $uids = array_filter($uids, fn ($uid) => filled($uid));

                if ($uids !== []) {
                    DB::table('borrowings')->where('id', $borrowing->id)->update($uids);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropIndex(['student_rfid_uid']);
            $table->dropIndex(['book_rfid_uid']);
            $table->dropColumn(['student_rfid_uid', 'book_rfid_uid']);
        });
    }
};
