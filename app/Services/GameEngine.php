<?php

namespace App\Services;

use App\Models\Card;
use App\Models\Game;
use App\Models\GameCard;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GameEngine
{
    /**
     * Create a new game and return the game model.
     */
    public function createGame(string $playerName, string $sessionId): Game
    {
        return DB::transaction(function () use ($playerName, $sessionId) {
            $game = Game::create([
                'code' => strtoupper(Str::random(6)),
                'status' => 'waiting',
                'direction' => 1,
                'draw_stack' => 0,
                'max_players' => 10,
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
            return ['error' => 'Game sudah penuh.'];
        }

        // Check if session already in game
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
     * Start the game: shuffle deck, deal cards, flip first card.
     */
    public function startGame(Game $game): Game
    {
        return DB::transaction(function () use ($game) {
            // Load all master cards and duplicate them into game_cards
            $allCards = Card::all();
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

            // Deal 7 cards to each player
            $players = $game->players()->orderBy('order_index')->get();
            foreach ($players as $player) {
                $this->drawCards($game, $player, 7);
            }

            // Flip the first card from deck to pile
            $this->flipFirstCard($game);

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
     * Flip the first card of the deck to start the pile.
     */
    private function flipFirstCard(Game $game): void
    {
        // Keep flipping until we get a number card
        $attempts = 0;
        do {
            $topDeck = $game->gameCards()
                ->where('location', 'deck')
                ->orderBy('order')
                ->first();

            if (!$topDeck) break;

            $topDeck->update(['location' => 'pile', 'order' => 0]);
            $card = $topDeck->card;

            if ($card->type === 'number') {
                $game->update([
                    'current_color' => $card->color,
                    'current_value' => $card->value,
                ]);
                break;
            }

            // If it's a special card, put it back and reshuffle
            if ($attempts > 10) {
                // Force use it
                $game->update([
                    'current_color' => $card->color === 'black' ? 'red' : $card->color,
                    'current_value' => $card->value,
                ]);
                break;
            }

            // Put it back
            $maxOrder = $game->gameCards()->where('location', 'deck')->max('order') ?? 0;
            $topDeck->update(['location' => 'deck', 'order' => $maxOrder + 1]);
            $attempts++;
        } while (true);
    }

    /**
     * Draw cards from the deck to a player's hand.
     */
    public function drawCards(Game $game, Player $player, int $count): array
    {
        $drawn = [];
        for ($i = 0; $i < $count; $i++) {
            $topDeck = $game->gameCards()
                ->where('location', 'deck')
                ->orderBy('order')
                ->first();

            if (!$topDeck) {
                $this->reshufflePile($game);
                $topDeck = $game->gameCards()
                    ->where('location', 'deck')
                    ->orderBy('order')
                    ->first();
                if (!$topDeck) break;
            }

            $topDeck->update([
                'location' => 'hand',
                'player_id' => $player->id,
            ]);
            $drawn[] = $topDeck;
        }
        return $drawn;
    }

    /**
     * Reshuffle the pile (except top card) back into the deck.
     */
    private function reshufflePile(Game $game): void
    {
        $pileCards = $game->gameCards()
            ->where('location', 'pile')
            ->orderByDesc('order')
            ->get();

        if ($pileCards->count() <= 1) return;

        // Keep top card
        $topPile = $pileCards->shift();

        // Shuffle and put back as deck
        $shuffled = $pileCards->shuffle()->values();
        $order = 0;
        foreach ($shuffled as $gc) {
            $gc->update([
                'location' => 'deck',
                'player_id' => null,
                'order' => $order++,
            ]);
        }
    }

    /**
     * Validate and play cards. Supports stacking multiple cards of same value/type.
     */
    public function playCards(Game $game, Player $player, array $gameCardIds, ?string $chosenColor = null): array
    {
        // Must be this player's turn
        if ($game->current_turn_player_id !== $player->id) {
            return ['error' => 'Bukan giliranmu!'];
        }

        // Verify all cards belong to this player
        $gameCards = GameCard::whereIn('id', $gameCardIds)
            ->where('player_id', $player->id)
            ->where('location', 'hand')
            ->with('card')
            ->get();

        if ($gameCards->count() !== count($gameCardIds)) {
            return ['error' => 'Kartu tidak valid.'];
        }

        $cards = $gameCards->map(fn($gc) => $gc->card);

        // Check if there's an active draw stack and player must respond
        if ($game->draw_stack > 0) {
            $canStack = $this->canStackDraw($game, $cards);
            if (!$canStack) {
                return ['error' => 'Kamu harus menumpuk +2/+4 atau ambil kartu!'];
            }
        }

        // Validate stacking: all cards must have the same value or same type
        if (!$this->validateStackedCards($cards)) {
            return ['error' => 'Kartu yang ditumpuk harus bernomor/tipe sama!'];
        }

        // Validate that cards can be played on current pile
        $firstCard = $cards->first();
        if (!$this->canPlayOnPile($game, $firstCard)) {
            return ['error' => 'Kartu tidak bisa dimainkan!'];
        }

        // If wild or draw4, color must be chosen
        if (in_array($firstCard->type, ['wild', 'draw4']) && !$chosenColor) {
            return ['error' => 'Pilih warna!', 'need_color' => true];
        }

        return DB::transaction(function () use ($game, $player, $gameCards, $cards, $firstCard, $chosenColor) {
            $maxOrder = $game->gameCards()->where('location', 'pile')->max('order') ?? 0;

            // Move all played cards to pile
            foreach ($gameCards as $gc) {
                $gc->update([
                    'location' => 'pile',
                    'player_id' => null,
                    'order' => ++$maxOrder,
                ]);
            }

            $cardCount = $cards->count();

            // Determine the new color
            $newColor = $chosenColor ?? $firstCard->color;
            $game->update([
                'current_color' => $newColor,
                'current_value' => $firstCard->value,
            ]);

            // Apply card effects
            $this->applyCardEffects($game, $firstCard, $cardCount);

            // Check for win
            $remainingCards = GameCard::where('player_id', $player->id)
                ->where('location', 'hand')
                ->count();

            if ($remainingCards === 0) {
                $player->update(['is_winner' => true]);
                $game->update(['status' => 'finished']);
                return ['success' => true, 'winner' => $player->name];
            }

            // Auto-say-uno check
            if ($remainingCards === 1) {
                $player->update(['has_said_uno' => true]);
            }

            // If no draw stack effect, advance turn
            if (!in_array($firstCard->type, ['draw2', 'draw4'])) {
                $this->advanceTurn($game);
            } else {
                // For draw cards, advance to next who must respond
                $this->advanceTurn($game);
            }

            return ['success' => true];
        });
    }

    /**
     * Check if stacked draw cards can respond to active draw stack.
     */
    private function canStackDraw(Game $game, $cards): bool
    {
        $firstCard = $cards->first();

        // Check current draw stack type from pile
        $pileTop = $game->topCard();
        if (!$pileTop) return false;

        $pileCard = $pileTop->card;

        // +2 can be stacked with +2 or +4
        if ($pileCard->type === 'draw2') {
            return in_array($firstCard->type, ['draw2', 'draw4']);
        }

        // +4 can only be stacked with +4
        if ($pileCard->type === 'draw4') {
            return $firstCard->type === 'draw4';
        }

        return false;
    }

    /**
     * Validate that all stacked cards share the same value or same type.
     */
    private function validateStackedCards($cards): bool
    {
        if ($cards->count() <= 1) return true;

        $first = $cards->first();

        // For number cards: same value
        if ($first->type === 'number') {
            return $cards->every(fn($c) => $c->type === 'number' && $c->value === $first->value);
        }

        // For special cards: same type
        return $cards->every(fn($c) => $c->type === $first->type);
    }

    /**
     * Check if a card can be played on the current pile.
     */
    private function canPlayOnPile(Game $game, Card $card): bool
    {
        // Wild and Draw4 can always be played (if no draw stack)
        if (in_array($card->type, ['wild', 'draw4'])) {
            return true;
        }

        // Match by color
        if ($card->color === $game->current_color) {
            return true;
        }

        // Match by value
        if ($card->value === $game->current_value) {
            return true;
        }

        return false;
    }

    /**
     * Apply effects of played cards (stacking multiplies effects).
     */
    private function applyCardEffects(Game $game, Card $card, int $count): void
    {
        switch ($card->type) {
            case 'skip':
                // Each skip card skips one player
                // advanceTurn will be called once, then skip extra
                $game->_skip_count = $count;
                break;

            case 'reverse':
                // Odd number of reverses flips direction, even keeps it
                if ($count % 2 === 1) {
                    $game->update(['direction' => $game->direction * -1]);
                    
                    // In 2-player games, reverse acts as a skip
                    if ($game->players()->count() === 2) {
                        $game->_skip_count = $count;
                    }
                }
                // Even count = no direction change (as per rules)
                break;

            case 'draw2':
                $game->update(['draw_stack' => $game->draw_stack + (2 * $count)]);
                break;

            case 'draw4':
                $game->update(['draw_stack' => $game->draw_stack + (4 * $count)]);
                break;
        }
    }

    /**
     * Advance to the next player's turn.
     */
    public function advanceTurn(Game $game): void
    {
        $game->refresh();
        $players = $game->players()->orderBy('order_index')->get();
        $playerCount = $players->count();

        if ($playerCount === 0) return;

        $currentIndex = $players->search(fn($p) => $p->id === $game->current_turn_player_id);

        $skipCount = $game->_skip_count ?? 0;

        // Move direction + skip extras
        $steps = 1 + $skipCount;
        $nextIndex = ($currentIndex + ($game->direction * $steps)) % $playerCount;
        if ($nextIndex < 0) $nextIndex += $playerCount;

        $game->update([
            'current_turn_player_id' => $players[$nextIndex]->id,
        ]);

        // Clear temporary skip count
        $game->_skip_count = 0;
    }

    /**
     * Player draws a card (either because they must or choose to).
     */
    public function drawCard(Game $game, Player $player): array
    {
        if ($game->current_turn_player_id !== $player->id) {
            return ['error' => 'Bukan giliranmu!'];
        }

        return DB::transaction(function () use ($game, $player) {
            // If there's a draw stack, draw that many
            if ($game->draw_stack > 0) {
                $drawn = $this->drawCards($game, $player, $game->draw_stack);
                $game->update(['draw_stack' => 0]);
                $this->advanceTurn($game);
                return ['success' => true, 'drawn' => count($drawn), 'forced' => true];
            }

            // Normal draw: draw one card
            $drawn = $this->drawCards($game, $player, 1);
            $this->advanceTurn($game);

            return ['success' => true, 'drawn' => count($drawn)];
        });
    }

    /**
     * Get the full game state for a specific player.
     */
    public function getGameState(Game $game, Player $player): array
    {
        $game->refresh();
        $game->load('players');

        $players = $game->players->map(function ($p) use ($player, $game) {
            $data = [
                'id' => $p->id,
                'name' => $p->name,
                'card_count' => GameCard::where('player_id', $p->id)->where('location', 'hand')->count(),
                'is_host' => $p->is_host,
                'is_current_turn' => $p->id === $game->current_turn_player_id,
                'has_said_uno' => $p->has_said_uno,
                'is_winner' => $p->is_winner,
                'is_me' => $p->id === $player->id,
            ];
            return $data;
        });

        // Get this player's hand
        $hand = GameCard::where('player_id', $player->id)
            ->where('location', 'hand')
            ->with('card')
            ->get()
            ->map(fn($gc) => [
                'game_card_id' => $gc->id,
                'id' => $gc->card->id,
                'type' => $gc->card->type,
                'color' => $gc->card->color,
                'value' => $gc->card->value,
            ]);

        // Get top pile card
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

        return [
            'game' => [
                'id' => $game->id,
                'code' => $game->code,
                'status' => $game->status,
                'direction' => $game->direction,
                'draw_stack' => $game->draw_stack,
                'current_color' => $game->current_color,
                'current_value' => $game->current_value,
                'current_turn_player_id' => $game->current_turn_player_id,
                'deck_count' => $deckCount,
            ],
            'players' => $players,
            'hand' => $hand,
            'top_card' => $topCardData,
            'my_player' => [
                'id' => $player->id,
                'name' => $player->name,
                'is_host' => $player->is_host,
            ],
        ];
    }
}
