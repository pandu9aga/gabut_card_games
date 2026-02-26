@extends('layouts.app')

@section('title', 'Gabut Card Games! - Game #' . $game->code)

@section('styles')
    <style>
        /* ===== GAME LAYOUT ===== */
        .game-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background: var(--glass);
            border-bottom: 1px solid var(--glass-border);
            backdrop-filter: blur(16px);
        }

        .game-code {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .game-code span {
            font-weight: 900;
            color: var(--accent);
            font-size: 18px;
            letter-spacing: 3px;
        }

        .game-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .direction-indicator {
            font-size: 22px;
            transition: transform 0.5s ease;
        }

        .draw-stack-badge {
            background: var(--uno-red);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
            display: none;
        }

        .draw-stack-badge.visible {
            display: inline-block;
            animation: pulse 1s infinite;
        }

        /* ===== WAITING ROOM ===== */
        .waiting-room {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 24px;
            padding: 20px;
        }

        .waiting-title {
            font-size: 28px;
            font-weight: 900;
        }

        .player-list {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            max-width: 500px;
        }

        .player-chip {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: bounceIn 0.4s ease;
        }

        .player-chip .host-badge {
            font-size: 12px;
            background: var(--accent);
            padding: 2px 8px;
            border-radius: 10px;
        }

        .share-code {
            text-align: center;
        }

        .share-code .code-display {
            font-size: 48px;
            font-weight: 900;
            letter-spacing: 10px;
            background: linear-gradient(135deg, var(--uno-red), var(--uno-yellow), var(--uno-green), var(--uno-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 12px 0;
        }

        /* ===== GAME BOARD ===== */
        .game-board {
            display: none;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }

        .game-board.active {
            display: flex;
        }

        /* Opponents */
        .opponents-area {
            display: flex;
            justify-content: center;
            gap: 12px;
            padding: 12px 20px;
            flex-wrap: wrap;
            overflow-x: auto;
        }

        .opponent {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-sm);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 120px;
            transition: all 0.3s ease;
        }

        .opponent.is-turn {
            border-color: var(--accent);
            box-shadow: 0 0 20px var(--accent-glow);
            animation: glow 2s infinite;
        }

        .opponent-name {
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }

        .opponent-cards {
            background: rgba(255, 255, 255, 0.1);
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
        }

        .uno-badge {
            background: var(--uno-yellow);
            color: #000;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 900;
            font-size: 11px;
            animation: pulse 1s infinite;
        }

        /* Center - Pile & Deck */
        .center-area {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 40px;
            padding: 20px;
        }

        .pile-wrapper,
        .deck-wrapper {
            position: relative;
            cursor: pointer;
        }

        .uno-card {
            width: 100px;
            height: 150px;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: white;
            position: relative;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
            user-select: none;
        }

        .uno-card .card-value {
            font-size: 36px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            z-index: 2;
        }

        .uno-card .card-type-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 4px;
            opacity: 0.8;
            z-index: 2;
        }

        /* Oval in center of UNO card */
        .uno-card::before {
            content: '';
            position: absolute;
            width: 65%;
            height: 75%;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            transform: rotate(30deg);
        }

        .card-red {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        }

        .card-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .card-green {
            background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);
        }

        .card-yellow {
            background: linear-gradient(135deg, #eab308 0%, #a16207 100%);
        }

        .card-black {
            background: linear-gradient(135deg, #374151 0%, #111827 100%);
            border: 2px solid rgba(255, 255, 255, 0.15);
        }

        .deck-card {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            border: 3px solid rgba(255, 255, 255, 0.15);
            cursor: pointer;
        }

        .deck-card:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
        }

        .deck-card .card-value {
            font-size: 24px;
        }

        .deck-count {
            position: absolute;
            bottom: -10px;
            right: -10px;
            background: var(--accent);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        .pile-card {
            transform: rotate(var(--pile-rot, 0deg));
        }

        .color-ring {
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 3px;
        }

        .color-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s;
        }

        .color-dot.active-color {
            border-color: white;
            transform: scale(1.3);
            box-shadow: 0 0 10px currentColor;
        }

        /* Status text */
        .turn-status {
            text-align: center;
            padding: 8px;
            font-size: 18px;
            font-weight: 700;
        }

        .turn-status.my-turn {
            color: var(--uno-green);
            animation: pulse 1.5s infinite;
        }

        /* ===== My Hand ===== */
        .hand-area {
            padding: 12px 20px 20px;
            background: linear-gradient(to top, rgba(10, 14, 39, 0.95), transparent);
        }

        .hand-controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .hand-cards {
            display: flex;
            justify-content: center;
            gap: 4px;
            overflow-x: auto;
            padding: 10px 0;
            min-height: 130px;
            flex-wrap: nowrap;
        }

        .hand-card {
            width: 75px;
            height: 112px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }

        .hand-card::before {
            content: '';
            position: absolute;
            width: 60%;
            height: 70%;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            transform: rotate(30deg);
        }

        .hand-card .card-value {
            font-size: 26px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
            z-index: 2;
        }

        .hand-card .card-type-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
            z-index: 2;
        }

        .hand-card:hover {
            transform: translateY(-12px) scale(1.08);
            z-index: 10;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
        }

        .hand-card.selected {
            transform: translateY(-20px) scale(1.1);
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.4);
            outline: 3px solid white;
            z-index: 20;
        }

        /* Color Picker Modal */
        .color-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .color-modal.visible {
            display: flex;
        }

        .color-picker {
            background: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            padding: 32px;
            text-align: center;
        }

        .color-picker h3 {
            margin-bottom: 20px;
            font-size: 22px;
        }

        .color-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .color-option {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            border: 3px solid transparent;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .color-option:hover {
            transform: scale(1.1);
            border-color: white;
            box-shadow: 0 0 20px currentColor;
        }

        /* Winner Overlay */
        .winner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(12px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            flex-direction: column;
            gap: 24px;
        }

        .winner-overlay.visible {
            display: flex;
        }

        .winner-text {
            font-size: 48px;
            font-weight: 900;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 1.5s infinite;
        }

        .winner-name {
            font-size: 32px;
            font-weight: 700;
            color: white;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .uno-card {
                width: 80px;
                height: 120px;
            }

            .uno-card .card-value {
                font-size: 28px;
            }

            .hand-card {
                width: 60px;
                height: 90px;
            }

            .hand-card .card-value {
                font-size: 20px;
            }

            .center-area {
                gap: 20px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="game-wrapper">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="game-code">
                KODE: <span id="gameCode">{{ $game->code }}</span>
            </div>
            <div class="game-info">
                <span class="direction-indicator" id="directionIndicator">🔄</span>
                <span class="draw-stack-badge" id="drawStackBadge">+0</span>
            </div>
        </div>

        <!-- Waiting Room -->
        <div class="waiting-room" id="waitingRoom">
            <h2 class="waiting-title">⏳ Menunggu Pemain...</h2>
            <div class="share-code">
                <p style="color: var(--text-secondary);">Bagikan kode ini ke temanmu:</p>
                <div class="code-display" id="shareCode">{{ $game->code }}</div>
                <button class="btn btn-ghost" onclick="copyCode()">📋 Salin Kode</button>
            </div>
            <div class="player-list" id="playerList"></div>
            <div id="hostControls" style="display: none;">
                <button class="btn btn-primary" id="startBtn" onclick="startGame()" disabled>
                    🚀 Mulai Game
                </button>
            </div>
        </div>

        <!-- Game Board -->
        <div class="game-board" id="gameBoard">
            <!-- Opponents -->
            <div class="opponents-area" id="opponentsArea"></div>

            <!-- Turn Status -->
            <div class="turn-status" id="turnStatus"></div>

            <!-- Center: Pile + Deck -->
            <div class="center-area">
                <div class="pile-wrapper">
                    <div class="uno-card pile-card" id="pileCard" style="--pile-rot: 3deg;">
                        <span class="card-value">?</span>
                    </div>
                    <div class="color-ring" id="colorRing">
                        <div class="color-dot" id="dot-red" style="background: var(--uno-red);"></div>
                        <div class="color-dot" id="dot-blue" style="background: var(--uno-blue);"></div>
                        <div class="color-dot" id="dot-green" style="background: var(--uno-green);"></div>
                        <div class="color-dot" id="dot-yellow" style="background: var(--uno-yellow);"></div>
                    </div>
                </div>

                <div class="deck-wrapper" onclick="drawCardAction()">
                    <div class="uno-card deck-card">
                        <span class="card-value">UNO</span>
                    </div>
                    <span class="deck-count" id="deckCount">0</span>
                </div>
            </div>

            <!-- My Hand -->
            <div class="hand-area">
                <div class="hand-controls">
                    <button class="btn btn-primary" id="playBtn" onclick="playSelectedCards()" disabled>
                        🎴 Main Kartu
                    </button>
                    <button class="btn btn-danger" id="unoBtn" onclick="sayUno()" style="display: none;">
                        🔥 UNO!
                    </button>
                </div>
                <div class="hand-cards" id="handCards"></div>
            </div>
        </div>
    </div>

    <!-- Color Picker Modal -->
    <div class="color-modal" id="colorModal">
        <div class="color-picker">
            <h3>Pilih Warna</h3>
            <div class="color-options">
                <div class="color-option" style="background: var(--uno-red);" onclick="pickColor('red')">🔴</div>
                <div class="color-option" style="background: var(--uno-blue);" onclick="pickColor('blue')">🔵</div>
                <div class="color-option" style="background: var(--uno-green);" onclick="pickColor('green')">🟢</div>
                <div class="color-option" style="background: var(--uno-yellow);" onclick="pickColor('yellow')">🟡</div>
            </div>
        </div>
    </div>

    <!-- Winner Overlay -->
    <div class="winner-overlay" id="winnerOverlay">
        <div class="winner-text">🎉 PEMENANG! 🎉</div>
        <div class="winner-name" id="winnerName"></div>
        <button class="btn btn-primary" onclick="window.location.href=APP_URL">🏠 Kembali ke Lobby</button>
    </div>
@endsection

@section('scripts')
    <script>
        const GAME_CODE = '{{ $game->code }}';
        const MY_PLAYER_ID = {{ $player->id }};
        const IS_HOST = {{ $player->is_host ? 'true' : 'false' }};

        let gameState = null;
        let selectedCards = [];
        let pollInterval = null;
        let pendingColorCards = null;

        // =================== INIT ===================
        document.addEventListener('DOMContentLoaded', () => {
            if (IS_HOST) {
                document.getElementById('hostControls').style.display = 'block';
            }
            startPolling();
        });

        // =================== POLLING ===================
        function startPolling() {
            poll();
            pollInterval = setInterval(poll, 1500);
        }

        async function poll() {
            try {
                const state = await apiGet(`/game/${GAME_CODE}/state`);
                if (state.error) return;
                gameState = state;
                render();
            } catch (e) {
                console.error('Poll error:', e);
            }
        }

        // =================== RENDER ===================
        function render() {
            if (!gameState) return;

            const g = gameState.game;
            const players = gameState.players;

            if (g.status === 'waiting') {
                renderWaiting(players);
            } else if (g.status === 'playing') {
                renderPlaying();
            } else if (g.status === 'finished') {
                renderFinished();
            }
        }

        function renderWaiting(players) {
            document.getElementById('waitingRoom').style.display = 'flex';
            document.getElementById('gameBoard').classList.remove('active');

            const list = document.getElementById('playerList');
            list.innerHTML = players.map(p => `
                            <div class="player-chip">
                                ${p.is_host ? '<span class="host-badge">HOST</span>' : ''}
                                ${p.name}
                                ${p.is_me ? ' (Kamu)' : ''}
                            </div>
                        `).join('');

            const startBtn = document.getElementById('startBtn');
            if (IS_HOST) {
                startBtn.disabled = players.length < 2;
            }
        }

        function renderPlaying() {
            document.getElementById('waitingRoom').style.display = 'none';
            document.getElementById('gameBoard').classList.add('active');

            const g = gameState.game;
            const players = gameState.players;

            // Direction
            const dir = document.getElementById('directionIndicator');
            dir.textContent = g.direction === 1 ? '🔄' : '🔃';
            dir.style.transform = g.direction === 1 ? 'scaleX(1)' : 'scaleX(-1)';

            // Draw stack
            const badge = document.getElementById('drawStackBadge');
            if (g.draw_stack > 0) {
                badge.textContent = `+${g.draw_stack}`;
                badge.classList.add('visible');
            } else {
                badge.classList.remove('visible');
            }

            // Opponents
            renderOpponents(players);

            // Top card
            renderTopCard();

            // Deck count
            document.getElementById('deckCount').textContent = g.deck_count;

            // Color ring
            renderColorRing(g.current_color);

            // Turn status
            const isMyTurn = g.current_turn_player_id === MY_PLAYER_ID;
            const turnStatus = document.getElementById('turnStatus');
            if (isMyTurn) {
                turnStatus.textContent = '🟢 Giliranmu!';
                turnStatus.className = 'turn-status my-turn';
            } else {
                const currentP = players.find(p => p.id === g.current_turn_player_id);
                turnStatus.textContent = `⏳ Giliran ${currentP ? currentP.name : '...'}`;
                turnStatus.className = 'turn-status';
            }

            // My hand
            renderHand();

            // UNO button
            const hand = gameState.hand;
            const unoBtn = document.getElementById('unoBtn');
            unoBtn.style.display = (hand.length <= 2 && hand.length > 0) ? 'inline-flex' : 'none';
        }

        function renderOpponents(players) {
            const area = document.getElementById('opponentsArea');
            const g = gameState.game;

            const opponents = players.filter(p => !p.is_me);
            area.innerHTML = opponents.map(p => `
                            <div class="opponent ${p.is_current_turn ? 'is-turn' : ''}">
                                <span class="opponent-name">${p.name}</span>
                                <span class="opponent-cards">${p.card_count} 🃏</span>
                                ${p.has_said_uno && p.card_count === 1 ? '<span class="uno-badge">UNO!</span>' : ''}
                            </div>
                        `).join('');
        }

        function renderTopCard() {
            const tc = gameState.top_card;
            if (!tc) return;

            const pile = document.getElementById('pileCard');
            pile.className = `uno-card pile-card card-${tc.color === 'black' ? 'black' : gameState.game.current_color}`;
            pile.innerHTML = `
                            <span class="card-value">${getCardDisplay(tc)}</span>
                            <span class="card-type-label">${getCardTypeLabel(tc)}</span>
                        `;
        }

        function renderColorRing(color) {
            ['red', 'blue', 'green', 'yellow'].forEach(c => {
                const dot = document.getElementById(`dot-${c}`);
                dot.classList.toggle('active-color', c === color);
            });
        }

        function renderHand() {
            const hand = gameState.hand;
            const container = document.getElementById('handCards');

            // Sort hand: by color then value
            const colorOrder = { red: 0, blue: 1, green: 2, yellow: 3, black: 4 };
            const sorted = [...hand].sort((a, b) => {
                if (colorOrder[a.color] !== colorOrder[b.color]) return colorOrder[a.color] - colorOrder[b.color];
                return a.value.localeCompare(b.value);
            });

            container.innerHTML = sorted.map(c => {
                const isSelected = selectedCards.includes(c.game_card_id);
                return `
                                <div class="hand-card card-${c.color} ${isSelected ? 'selected' : ''}"
                                     onclick="toggleSelect(${c.game_card_id})"
                                     data-card-id="${c.game_card_id}"
                                     data-type="${c.type}"
                                     data-color="${c.color}"
                                     data-value="${c.value}">
                                    <span class="card-value">${getCardDisplay(c)}</span>
                                    <span class="card-type-label">${getCardTypeLabel(c)}</span>
                                </div>
                            `;
            }).join('');

            // Update play button
            document.getElementById('playBtn').disabled = selectedCards.length === 0;
        }

        function renderFinished() {
            const winner = gameState.players.find(p => p.is_winner);
            if (winner) {
                document.getElementById('winnerName').textContent = winner.name;
                document.getElementById('winnerOverlay').classList.add('visible');
            }
        }

        // =================== CARD DISPLAY ===================
        function getCardDisplay(c) {
            switch (c.type) {
                case 'number': return c.value;
                case 'skip': return '⊘';
                case 'reverse': return '↺';
                case 'draw2': return '+2';
                case 'wild': return '✦';
                case 'draw4': return '+4';
                default: return '?';
            }
        }

        function getCardTypeLabel(c) {
            switch (c.type) {
                case 'skip': return 'SKIP';
                case 'reverse': return 'REVERSE';
                case 'draw2': return 'DRAW TWO';
                case 'wild': return 'WILD';
                case 'draw4': return 'WILD +4';
                default: return '';
            }
        }

        // =================== INTERACTIONS ===================
        function toggleSelect(gcId) {
            if (!gameState || gameState.game.current_turn_player_id !== MY_PLAYER_ID) {
                showToast('Bukan giliranmu!', 'error');
                return;
            }

            const idx = selectedCards.indexOf(gcId);
            if (idx >= 0) {
                selectedCards.splice(idx, 1);
            } else {
                // Validate stackability
                if (selectedCards.length > 0) {
                    const firstCard = gameState.hand.find(c => c.game_card_id === selectedCards[0]);
                    const newCard = gameState.hand.find(c => c.game_card_id === gcId);
                    if (firstCard && newCard) {
                        // Same value for numbers, same type for specials
                        if (firstCard.type === 'number' && newCard.type === 'number') {
                            if (firstCard.value !== newCard.value) {
                                showToast('Hanya kartu bernomor sama yang bisa ditumpuk!', 'error');
                                return;
                            }
                        } else if (firstCard.type !== newCard.type) {
                            showToast('Hanya kartu bertipe sama yang bisa ditumpuk!', 'error');
                            return;
                        }
                    }
                }
                selectedCards.push(gcId);
            }
            renderHand();
        }

        async function playSelectedCards() {
            if (selectedCards.length === 0) return;

            // Check if wild/draw4 => need color
            const firstCard = gameState.hand.find(c => c.game_card_id === selectedCards[0]);
            if (firstCard && (firstCard.type === 'wild' || firstCard.type === 'draw4')) {
                pendingColorCards = [...selectedCards];
                document.getElementById('colorModal').classList.add('visible');
                return;
            }

            await submitPlay(selectedCards, null);
        }

        async function pickColor(color) {
            document.getElementById('colorModal').classList.remove('visible');
            if (pendingColorCards) {
                await submitPlay(pendingColorCards, color);
                pendingColorCards = null;
            }
        }

        async function submitPlay(cardIds, chosenColor) {
            const data = { card_ids: cardIds, chosen_color: chosenColor };
            const res = await apiPost(`/game/${GAME_CODE}/play`, data);

            if (res.error) {
                showToast(res.error, 'error');
                return;
            }

            if (res.winner) {
                showToast(`🎉 ${res.winner} menang!`, 'success');
            }

            selectedCards = [];
            poll();
        }

        async function drawCardAction() {
            if (!gameState || gameState.game.current_turn_player_id !== MY_PLAYER_ID) {
                showToast('Bukan giliranmu!', 'error');
                return;
            }

            const res = await apiPost(`/game/${GAME_CODE}/draw`);
            if (res.error) {
                showToast(res.error, 'error');
                return;
            }

            if (res.forced) {
                showToast(`Kamu ambil ${res.drawn} kartu! 😱`, 'info');
            } else {
                showToast('Kamu ambil 1 kartu', 'info');
            }

            selectedCards = [];
            poll();
        }

        async function sayUno() {
            const res = await apiPost(`/game/${GAME_CODE}/uno`);
            if (res.error) {
                showToast(res.error, 'error');
                return;
            }
            showToast('UNO! 🔥', 'success');
            poll();
        }

        async function startGame() {
            const res = await apiPost(`/game/${GAME_CODE}/start`);
            if (res.error) {
                showToast(res.error, 'error');
                return;
            }
            showToast('Game dimulai! 🎮', 'success');
            poll();
        }

        function copyCode() {
            navigator.clipboard.writeText(GAME_CODE).then(() => {
                showToast('Kode disalin! 📋', 'success');
            });
        }
    </script>
@endsection