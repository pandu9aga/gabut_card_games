<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameCard extends Model
{
    public $timestamps = false;

    protected $fillable = ['game_id', 'card_id', 'player_id', 'location', 'order'];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
