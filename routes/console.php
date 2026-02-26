<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('game:cleanup', function () {
    $count = \App\Models\Game::where('created_at', '<', now()->subHours(6))->delete();
    $this->info("Deleted $count stale games.");
})->purpose('Delete games and players older than 6 hours');
