<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

// Lobby
Route::get('/', [GameController::class, 'index'])->name('lobby');

// Game API
Route::post('/game/create', [GameController::class, 'create'])->name('game.create');
Route::post('/game/join', [GameController::class, 'join'])->name('game.join');
Route::get('/game/{code}', [GameController::class, 'show'])->name('game.show');
Route::post('/game/{code}/start', [GameController::class, 'start'])->name('game.start');
Route::get('/game/{code}/state', [GameController::class, 'state'])->name('game.state');
Route::post('/game/{code}/play', [GameController::class, 'playCards'])->name('game.play');
Route::post('/game/{code}/draw', [GameController::class, 'drawCard'])->name('game.draw');
Route::post('/game/{code}/discard', [GameController::class, 'discardCard'])->name('game.discard');
Route::post('/game/{code}/uno', [GameController::class, 'sayUno'])->name('game.uno');
