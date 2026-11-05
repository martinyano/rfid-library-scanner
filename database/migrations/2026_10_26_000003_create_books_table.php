<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->foreignId('author_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('isbn', 50)->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedSmallInteger('published_year')->nullable();
            $table->string('shelf_location', 80)->nullable();
            $table->unsignedInteger('total_copies')->default(1);
            $table->unsignedInteger('available_copies')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['author_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
