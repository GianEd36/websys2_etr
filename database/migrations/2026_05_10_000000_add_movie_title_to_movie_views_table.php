<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movie_views', function (Blueprint $table) {
            if (!Schema::hasColumn('movie_views', 'movie_title')) {
                $table->string('movie_title')->nullable()->after('movie_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_views', function (Blueprint $table) {
            if (Schema::hasColumn('movie_views', 'movie_title')) {
                $table->dropColumn('movie_title');
            }
        });
    }
};
