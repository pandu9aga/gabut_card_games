<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Services\GameEngine;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected GameEngine $engine;

    public function __construct(GameEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Show the lobby / home page.
     */
    public function index()
    {
        return view('lobby');
    }

    /**
     * Create a new game.
     */
    public function create(Request $request)
    {
        $request->validate([
            'player_name' => 'required|string|max:50',
        ]);

        $sessionId = $request->session()->getId();
        $game = $this->engine->createGame($request->player_name, $sessionId);

        return response()->json([
            'success' => true,
            'code' => $game->code,
            'game_id' => $game->id,
        ]);
    }

    /**
     * Join an existing game.
     */
    public function join(Request $request)
    {
        $request->validate([
            'player_name' => 'required|string|max:50',
            'code' => 'required|string|size:6',
        ]);

        $sessionId = $request->session()->getId();
        $result = $this->engine->joinGame(
            strtoupper($request->code),
            $request->player_name,
            $sessionId
        );

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json([
            'success' => true,
            'code' => $result['game']->code,
            'game_id' => $result['game']->id,
        ]);
    }

    /**
     * Show the game page.
     */
    public function show(string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionId = session()->getId();
        $player = $game->players()->where('session_id', $sessionId)->first();

        if (!$player) {
            return redirect()->route('lobby')->with('error', 'Kamu belum tergabung di game ini.');
        }

        return view('game', [
            'game' => $game,
            'player' => $player,
        ]);
    }

    /**
     * Start the game (host only).
     */
    public function start(Request $request, string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionId = $request->session()->getId();
        $player = $game->players()->where('session_id', $sessionId)->where('is_host', true)->first();

        if (!$player) {
            return response()->json(['error' => 'Hanya host yang bisa memulai game.'], 403);
        }

        if ($game->players()->count() < 2) {
            return response()->json(['error' => 'Minimal 2 pemain untuk mulai.'], 422);
        }

        $this->engine->startGame($game);

        return response()->json(['success' => true]);
    }

    /**
     * Get current game state via polling.
     */
    public function state(string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionId = session()->getId();
        $player = $game->players()->where('session_id', $sessionId)->first();

        if (!$player) {
            return response()->json(['error' => 'Not in game'], 403);
        }

        $state = $this->engine->getGameState($game, $player);
        return response()->json($state);
    }

    /**
     * Play one or more cards.
     */
    public function playCards(Request $request, string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionId = $request->session()->getId();
        $player = $game->players()->where('session_id', $sessionId)->first();

        if (!$player) {
            return response()->json(['error' => 'Not in game'], 403);
        }

        $request->validate([
            'card_ids' => 'required|array|min:1',
            'card_ids.*' => 'integer',
            'chosen_color' => 'nullable|string|in:red,green,blue,yellow',
        ]);

        $result = $this->engine->playCards(
            $game,
            $player,
            $request->card_ids,
            $request->chosen_color
        );

        if (isset($result['error'])) {
            $status = 422;
            if (isset($result['need_color'])) {
                $status = 200;
            }
            return response()->json($result, $status);
        }

        return response()->json($result);
    }

    /**
     * Draw a card.
     */
    public function drawCard(Request $request, string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionId = $request->session()->getId();
        $player = $game->players()->where('session_id', $sessionId)->first();

        if (!$player) {
            return response()->json(['error' => 'Not in game'], 403);
        }

        $result = $this->engine->drawCard($game, $player);

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Say UNO!
     */
    public function sayUno(Request $request, string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionId = $request->session()->getId();
        $player = $game->players()->where('session_id', $sessionId)->first();

        if (!$player) {
            return response()->json(['error' => 'Not in game'], 403);
        }

        $handCount = $player->hand()->count();
        if ($handCount <= 2) {
            $player->update(['has_said_uno' => true]);
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Belum waktunya bilang UNO!'], 422);
    }
}
