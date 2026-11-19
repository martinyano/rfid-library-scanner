<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dateTime('borrowed_at')->change();
            $table->dateTime('returned_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->date('borrowed_at')->change();
            $table->date('returned_at')->nullable()->change();
        });
    }
};
