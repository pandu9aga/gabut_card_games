<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->enum('location', ['deck', 'hand', 'pile'])->default('deck');
            $table->integer('order')->default(0);

            $table->foreign('player_id')->references('id')->on('players')->onDelete('set null');
            $table->index(['game_id', 'location']);
            $table->index(['game_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_cards');
    }
};
