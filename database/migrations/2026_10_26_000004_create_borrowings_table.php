<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->date('borrowed_at');
            $table->date('due_at')->index();
            $table->date('returned_at')->nullable();
            $table->string('status', 20)->default('borrowed')->index();
            $table->text('notes')->nullable();
            $table->timestamp('due_soon_notified_at')->nullable();
            $table->timestamp('overdue_notified_at')->nullable();
            $table->foreignId('handled_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
