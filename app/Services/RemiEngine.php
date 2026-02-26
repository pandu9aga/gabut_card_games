<?php

namespace App\Services;

use App\Models\Card;
use App\Models\Game;
use App\Models\GameCard;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RemiEngine
{
    /**
     * Create a new Remi 41 game.
     */
    public function createGame(string $playerName, string $sessionId): Game
    {
        return DB::transaction(function () use ($playerName, $sessionId) {
            $game = Game::create([
                'code' => strtoupper(Str::random(6)),
                'game_type' => 'remi',
                'status' => 'waiting',
                'direction' => 1,
                'draw_stack' => 0,
                'max_players' => 6,
            ]);

            Player::create([
                'game_id' => $game->id,
                'session_id' => $sessionId,
                'name' => $playerName,
                'order_index' => 0,
                'is_host' => true,
            ]);

            return $game->load('players');
        });
    }

    /**
     * Join an existing game.
     */
    public function joinGame(string $code, string $playerName, string $sessionId): ?array
    {
        $game = Game::where('code', $code)->first();

        if (!$game) {
            return ['error' => 'Game tidak ditemukan.'];
        }

        if ($game->status !== 'waiting') {
            return ['error' => 'Game sudah berjalan.'];
        }

        if ($game->players()->count() >= $game->max_players) {
            return ['error' => 'Game sudah penuh (maks 6 pemain).'];
        }

        $existing = $game->players()->where('session_id', $sessionId)->first();
        if ($existing) {
            return ['game' => $game->load('players'), 'player' => $existing];
        }

        $player = Player::create([
            'game_id' => $game->id,
            'session_id' => $sessionId,
            'name' => $playerName,
            'order_index' => $game->players()->count(),
            'is_host' => false,
        ]);

        return ['game' => $game->load('players'), 'player' => $player];
    }

    /**
     * Start game: shuffle deck, deal 4 cards each, flip first discard.
     */
    public function startGame(Game $game): Game
    {
        return DB::transaction(function () use ($game) {
            // Load only standard cards (for remi)
            $allCards = Card::where('type', 'standard')->get();
            $shuffled = $allCards->shuffle();

            $order = 0;
            foreach ($shuffled as $card) {
                GameCard::create([
                    'game_id' => $game->id,
                    'card_id' => $card->id,
                    'player_id' => null,
                    'location' => 'deck',
                    'order' => $order++,
                ]);
            }

            // Deal 4 cards to each player
            $players = $game->players()->orderBy('order_index')->get();
            foreach ($players as $player) {
                $this->drawCardsFromDeck($game, $player, 4);
            }

            // Set first turn
            $firstPlayer = $players->first();
            $game->update([
                'status' => 'playing',
                'current_turn_player_id' => $firstPlayer->id,
            ]);

            return $game->fresh(['players']);
        });
    }

    /**
     * Draw cards from deck to player's hand.
     */
    public function drawCardsFromDeck(Game $game, Player $player, int $count): array
    {
        $drawn = [];
        for ($i = 0; $i < $count; $i++) {
            $topDeck = $game->gameCards()
                ->where('location', 'deck')
                ->orderBy('order')
                ->first();

            if (!$topDeck) break; // No more cards

            $topDeck->update([
                'location' => 'hand',
                'player_id' => $player->id,
            ]);
            $drawn[] = $topDeck;
        }
        return $drawn;
    }

    /**
     * Player draws one card from deck.
     */
    public function drawFromDeck(Game $game, Player $player): array
    {
        if ($game->current_turn_player_id !== $player->id) {
            return ['error' => 'Bukan giliranmu!'];
        }

        return DB::transaction(function () use ($game, $player) {
            $topDeck = $game->gameCards()
                ->where('location', 'deck')
                ->orderBy('order')
                ->first();

            if (!$topDeck) {
                // Deck empty -> trigger end game scoring
                return $this->endGameScoring($game);
            }

            $topDeck->update([
                'location' => 'hand',
                'player_id' => $player->id,
            ]);

            // Check if deck is now empty
            $deckRemaining = $game->gameCards()->where('location', 'deck')->count();
            if ($deckRemaining === 0) {
                // Check if any player hasn't masuk -> score them
                $unfinished = $game->players()->where('is_masuk', false)->exists();
                if ($unfinished) {
                    return $this->endGameScoring($game);
                }
            }

            return ['success' => true, 'source' => 'deck'];
        });
    }

    /**
     * Player takes the top card from the discard pile.
     */
    public function drawFromPile(Game $game, Player $player): array
    {
        if ($game->current_turn_player_id !== $player->id) {
            return ['error' => 'Bukan giliranmu!'];
        }

        return DB::transaction(function () use ($game, $player) {
            $topPile = $game->gameCards()
                ->where('location', 'pile')
                ->orderByDesc('order')
                ->first();

            if (!$topPile) {
                return ['error' => 'Tumpukan buangan kosong.'];
            }

            $topPile->update([
                'location' => 'hand',
                'player_id' => $player->id,
            ]);

            return ['success' => true, 'source' => 'pile'];
        });
    }

    /**
     * Player discards a card to the pile (mandatory after drawing).
     */
    public function discardCard(Game $game, Player $player, int $gameCardId): array
    {
        if ($game->current_turn_player_id !== $player->id) {
            return ['error' => 'Bukan giliranmu!'];
        }

        $gameCard = GameCard::where('id', $gameCardId)
            ->where('player_id', $player->id)
            ->where('location', 'hand')
            ->with('card')
            ->first();

        if (!$gameCard) {
            return ['error' => 'Kartu tidak valid.'];
        }

        return DB::transaction(function () use ($game, $player, $gameCard) {
            $maxOrder = $game->gameCards()->where('location', 'pile')->max('order') ?? 0;
            $gameCard->update([
                'location' => 'pile',
                'player_id' => null,
                'order' => $maxOrder + 1,
            ]);

            // Check if player has "masuk" (As, K, Q, J, 10 of same suit)
            $masukResult = $this->checkMasuk($game, $player);
            if ($masukResult) {
                $player->update(['is_masuk' => true]);

                // Game still continues - adjust turn:
                // The player BEFORE the masuk player now discards to the player AFTER
                $this->advanceTurnSkipMasuk($game, $player);

                // Check if all or all but one players have masuk
                $notMasuk = $game->players()->where('is_masuk', false)->count();
                if ($notMasuk <= 1) {
                    return $this->endGameScoring($game);
                }

                return ['success' => true, 'masuk' => true, 'player_name' => $player->name];
            }

            // Check if deck is empty after discard
            $deckRemaining = $game->gameCards()->where('location', 'deck')->count();
            if ($deckRemaining === 0) {
                $unfinished = $game->players()->where('is_masuk', false)->exists();
                if ($unfinished) {
                    return $this->endGameScoring($game);
                }
            }

            // Advance to next player
            $this->advanceTurn($game);

            return ['success' => true];
        });
    }

    /**
     * Check if player has "masuk": A, K, Q, J, 10 of the same suit.
     */
    public function checkMasuk(Game $game, Player $player): bool
    {
        $hand = GameCard::where('player_id', $player->id)
            ->where('location', 'hand')
            ->with('card')
            ->get();

        $requiredValues = ['A', 'K', 'Q', 'J', '10'];
        $suits = ['spade', 'heart', 'club', 'diamond'];

        foreach ($suits as $suit) {
            $suitCards = $hand->filter(fn($gc) => $gc->card->color === $suit)
                ->pluck('card.value')
                ->toArray();

            $hasAll = true;
            foreach ($requiredValues as $val) {
                if (!in_array($val, $suitCards)) {
                    $hasAll = false;
                    break;
                }
            }

            if ($hasAll) return true;
        }

        return false;
    }

    /**
     * Advance turn to the next active (non-masuk) player.
     */
    public function advanceTurn(Game $game): void
    {
        $game->refresh();
        $players = $game->players()->orderBy('order_index')->get();
        $playerCount = $players->count();
        if ($playerCount === 0) return;

        $currentIndex = $players->search(fn($p) => $p->id === $game->current_turn_player_id);

        // Find next non-masuk player
        for ($i = 1; $i <= $playerCount; $i++) {
            $nextIndex = ($currentIndex + $i) % $playerCount;
            $nextPlayer = $players[$nextIndex];
            if (!$nextPlayer->is_masuk) {
                $game->update(['current_turn_player_id' => $nextPlayer->id]);
                return;
            }
        }
    }

    /**
     * When a player "masuk", advance turn skipping them.
     */
    private function advanceTurnSkipMasuk(Game $game, Player $masukPlayer): void
    {
        $this->advanceTurn($game);
    }

    /**
     * End game scoring when deck is empty.
     * Cards of the dominant suit are positive, others are negative (terbakar).
     */
    public function endGameScoring(Game $game): array
    {
        return DB::transaction(function () use ($game) {
            $results = [];
            $players = $game->players()->where('is_masuk', false)->get();

            foreach ($players as $player) {
                $hand = GameCard::where('player_id', $player->id)
                    ->where('location', 'hand')
                    ->with('card')
                    ->get();

                $suitPoints = [];
                foreach (['spade', 'heart', 'club', 'diamond'] as $suit) {
                    $suitPoints[$suit] = 0;
                }

                foreach ($hand as $gc) {
                    $card = $gc->card;
                    $points = $this->getCardPoints($card->value);
                    $suitPoints[$card->color] += $points;
                }

                // Find the suit with the most points (dominant suit)
                $dominantSuit = null;
                $maxPoints = -1;
                foreach ($suitPoints as $suit => $pts) {
                    if ($pts > $maxPoints) {
                        $maxPoints = $pts;
                        $dominantSuit = $suit;
                    }
                }

                // Dominant suit is positive, all others are negative (terbakar)
                $totalPoints = 0;
                foreach ($suitPoints as $suit => $pts) {
                    if ($suit === $dominantSuit) {
                        $totalPoints += $pts;
                    } else {
                        $totalPoints -= $pts; // Terbakar
                    }
                }

                $player->update(['points' => $totalPoints]);

                $results[] = [
                    'player_id' => $player->id,
                    'name' => $player->name,
                    'points' => $totalPoints,
                    'dominant_suit' => $dominantSuit,
                    'suit_breakdown' => $suitPoints,
                ];
            }

            // Players who masuk get max points
            $masukPlayers = $game->players()->where('is_masuk', true)->get();
            foreach ($masukPlayers as $mp) {
                $mp->update(['points' => 41]);
                $results[] = [
                    'player_id' => $mp->id,
                    'name' => $mp->name,
                    'points' => 41,
                    'masuk' => true,
                ];
            }

            $game->update(['status' => 'finished']);

            return [
                'success' => true,
                'finished' => true,
                'scoring' => $results,
            ];
        });
    }

    /**
     * Get point value for a card.
     */
    private function getCardPoints(string $value): int
    {
        return match ($value) {
            'A' => 11,
            'K', 'Q', 'J' => 10,
            '10' => 10,
            default => (int) $value,
        };
    }

    /**
     * Get the full game state for a specific player.
     */
    public function getGameState(Game $game, Player $player): array
    {
        $game->refresh();
        $game->load('players');

        $players = $game->players->map(function ($p) use ($player, $game) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'card_count' => GameCard::where('player_id', $p->id)->where('location', 'hand')->count(),
                'is_host' => $p->is_host,
                'is_current_turn' => $p->id === $game->current_turn_player_id,
                'is_masuk' => $p->is_masuk,
                'points' => $p->points,
                'is_me' => $p->id === $player->id,
            ];
        });

        // Current player's hand
        $hand = GameCard::where('player_id', $player->id)
            ->where('location', 'hand')
            ->with('card')
            ->get()
            ->map(fn($gc) => [
                'game_card_id' => $gc->id,
                'id' => $gc->card->id,
                'type' => $gc->card->type,
                'color' => $gc->card->color, // suit
                'value' => $gc->card->value,
            ]);

        // Top discard card
        $topPile = $game->gameCards()
            ->where('location', 'pile')
            ->orderByDesc('order')
            ->with('card')
            ->first();

        $topCardData = null;
        if ($topPile) {
            $topCardData = [
                'type' => $topPile->card->type,
                'color' => $topPile->card->color,
                'value' => $topPile->card->value,
            ];
        }

        $deckCount = $game->gameCards()->where('location', 'deck')->count();

        // Scoring results for finished game
        $scoring = null;
        if ($game->status === 'finished') {
            $scoring = $game->players->map(fn($p) => [
                'player_id' => $p->id,
                'name' => $p->name,
                'points' => $p->points,
                'is_masuk' => $p->is_masuk,
            ]);
        }

        return [
            'game' => [
                'id' => $game->id,
                'code' => $game->code,
                'game_type' => $game->game_type,
                'status' => $game->status,
                'direction' => $game->direction,
                'current_turn_player_id' => $game->current_turn_player_id,
                'deck_count' => $deckCount,
            ],
            'players' => $players,
            'hand' => $hand,
            'top_card' => $topCardData,
            'scoring' => $scoring,
            'my_player' => [
                'id' => $player->id,
                'name' => $player->name,
                'is_host' => $player->is_host,
                'is_masuk' => $player->is_masuk,
            ],
        ];
    }
}
