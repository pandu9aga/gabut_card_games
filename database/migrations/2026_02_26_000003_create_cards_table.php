<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);    // number, skip, reverse, draw2, wild, draw4
            $table->string('color', 10);   // red, green, blue, yellow, black
            $table->string('value', 10);   // 0-9, skip, reverse, +2, wild, +4
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
