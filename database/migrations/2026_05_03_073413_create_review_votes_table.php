<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['up', 'down']); // Track if it's an upvote or downvote
            $table->unique(['user_id', 'review_id']); // This line prevents double voting!
            $table->timestamps();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->integer('downvotes')->default(0); // Add downvotes column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_votes');
    }
};
