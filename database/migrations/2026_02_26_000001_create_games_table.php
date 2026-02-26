<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');
            $table->unsignedBigInteger('current_turn_player_id')->nullable();
            $table->tinyInteger('direction')->default(1); // 1 = clockwise, -1 = counter
            $table->integer('draw_stack')->default(0);
            $table->string('current_color', 10)->nullable();
            $table->string('current_value', 10)->nullable();
            $table->integer('max_players')->default(4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
