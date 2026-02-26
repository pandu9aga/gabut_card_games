<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('game_type', 10)->default('uno')->after('code'); // uno, remi
        });

        Schema::table('players', function (Blueprint $table) {
            $table->boolean('is_masuk')->default(false)->after('is_winner');
            $table->integer('points')->default(0)->after('is_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('game_type');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['is_masuk', 'points']);
        });
    }
};
