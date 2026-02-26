<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = [
        'game_id', 'session_id', 'name', 'order_index',
        'is_host', 'has_said_uno', 'is_winner',
        'is_masuk', 'points',
    ];

    protected $casts = [
        'is_host' => 'boolean',
        'has_said_uno' => 'boolean',
        'is_winner' => 'boolean',
        'is_masuk' => 'boolean',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function hand()
    {
        return $this->hasMany(GameCard::class, 'player_id')
                    ->where('location', 'hand')
                    ->with('card');
    }

    public function handCount()
    {
        return $this->hasMany(GameCard::class, 'player_id')
                    ->where('location', 'hand')
                    ->count();
    }
}
