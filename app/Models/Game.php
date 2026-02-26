<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'code', 'game_type', 'status', 'current_turn_player_id', 'direction',
        'draw_stack', 'current_color', 'current_value', 'max_players',
    ];

    public function players()
    {
        return $this->hasMany(Player::class)->orderBy('order_index');
    }

    public function gameCards()
    {
        return $this->hasMany(GameCard::class);
    }

    public function currentTurnPlayer()
    {
        return $this->belongsTo(Player::class, 'current_turn_player_id');
    }

    public function deckCards()
    {
        return $this->gameCards()->where('location', 'deck')->orderBy('order');
    }

    public function pileCards()
    {
        return $this->gameCards()->where('location', 'pile')->orderByDesc('order');
    }

    public function topCard()
    {
        return $this->pileCards()->with('card')->first();
    }
}
