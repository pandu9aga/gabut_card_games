@extends('layouts.app')

@section('title', 'Gabut Card Games! - Remi 41 #' . $game->code)

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

        .game-type-badge {
            background: linear-gradient(135deg, #a855f7, #6366f1);
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
        }

        .deck-badge {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
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
            background: linear-gradient(135deg, #a855f7, #ec4899, #f43f5e);
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
            gap: 10px;
            padding: 10px 16px;
            flex-wrap: wrap;
            overflow-x: auto;
        }

        .opponent {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-sm);
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 110px;
            transition: all 0.3s ease;
        }

        .opponent.is-turn {
            border-color: var(--accent);
            box-shadow: 0 0 20px var(--accent-glow);
            animation: glow 2s infinite;
        }

        .opponent.is-masuk {
            border-color: #22c55e;
            background: rgba(34, 197, 94, 0.1);
        }

        .opponent-name {
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }

        .opponent-cards {
            background: rgba(255, 255, 255, 0.1);
            padding: 3px 8px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 12px;
        }

        .masuk-badge {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 900;
            font-size: 11px;
            animation: pulse 1.5s infinite;
        }

        /* Center Area */
        .center-area {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 32px;
            padding: 16px;
        }

        .pile-wrapper,
        .deck-wrapper {
            position: relative;
            cursor: pointer;
        }

        .pile-label,
        .deck-label {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        /* Remi Card */
        .remi-card {
            width: 90px;
            height: 130px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            position: relative;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
            user-select: none;
            background: linear-gradient(145deg, #ffffff 0%, #f0f0f0 100%);
            color: #1a1a2e;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .remi-card .card-suit-icon {
            font-size: 14px;
            position: absolute;
            top: 6px;
            left: 8px;
        }

        .remi-card .card-suit-icon-br {
            font-size: 14px;
            position: absolute;
            bottom: 6px;
            right: 8px;
            transform: rotate(180deg);
        }

        .remi-card .card-value {
            font-size: 32px;
            z-index: 2;
        }

        .remi-card .card-suit-big {
            font-size: 20px;
            margin-top: -2px;
            z-index: 2;
        }

        .remi-card.suit-heart,
        .remi-card.suit-diamond {
            color: #dc2626;
        }

        .remi-card.suit-spade,
        .remi-card.suit-club {
            color: #1e293b;
        }

        .deck-card-remi {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            border: 3px solid rgba(255, 255, 255, 0.15);
            cursor: pointer;
            color: white;
        }

        .deck-card-remi:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
        }

        .deck-card-remi .card-value {
            font-size: 16px;
            letter-spacing: 1px;
        }

        .pile-card-remi {
            transform: rotate(3deg);
        }

        .pile-card-remi:hover {
            transform: rotate(0deg) scale(1.05);
        }

        .deck-count {
            position: absolute;
            bottom: -10px;
            right: -10px;
            background: var(--accent);
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: white;
        }

        /* Turn Status */
        .turn-status {
            text-align: center;
            padding: 6px;
            font-size: 16px;
            font-weight: 700;
        }

        .turn-status.my-turn {
            color: var(--uno-green);
            animation: pulse 1.5s infinite;
        }

        .phase-indicator {
            text-align: center;
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .phase-indicator span {
            background: rgba(124, 77, 255, 0.2);
            padding: 2px 12px;
            border-radius: 12px;
            font-weight: 600;
        }

        /* ===== HAND AREA ===== */
        .hand-area {
            padding: 10px 20px 16px;
            background: linear-gradient(to top, rgba(10, 14, 39, 0.95), transparent);
        }

        .hand-controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .hand-cards {
            display: flex;
            justify-content: center;
            gap: 4px;
            overflow-x: auto;
            padding: 10px 0;
            min-height: 120px;
            flex-wrap: nowrap;
        }

        .hand-card-remi {
            width: 68px;
            height: 100px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            background: linear-gradient(145deg, #ffffff 0%, #f0f0f0 100%);
            border: 2px solid rgba(200, 200, 200, 0.5);
        }

        .hand-card-remi .card-suit-icon {
            font-size: 11px;
            position: absolute;
            top: 4px;
            left: 6px;
        }

        .hand-card-remi .card-value {
            font-size: 24px;
            z-index: 2;
        }

        .hand-card-remi .card-suit-big {
            font-size: 16px;
            margin-top: -2px;
            z-index: 2;
        }

        .hand-card-remi.suit-heart,
        .hand-card-remi.suit-diamond {
            color: #dc2626;
        }

        .hand-card-remi.suit-spade,
        .hand-card-remi.suit-club {
            color: #1e293b;
        }

        .hand-card-remi:hover {
            transform: translateY(-12px) scale(1.08);
            z-index: 10;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        }

        .hand-card-remi.selected {
            transform: translateY(-20px) scale(1.1);
            box-shadow: 0 0 25px rgba(124, 77, 255, 0.6);
            outline: 3px solid var(--accent);
            z-index: 20;
        }

        /* Scoring Overlay */
        .scoring-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(12px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            flex-direction: column;
            gap: 20px;
            padding: 20px;
        }

        .scoring-overlay.visible {
            display: flex;
        }

        .scoring-title {
            font-size: 36px;
            font-weight: 900;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .scoring-table {
            width: 100%;
            max-width: 500px;
            border-collapse: collapse;
        }

        .scoring-table th,
        .scoring-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .scoring-table th {
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .scoring-table td {
            font-weight: 600;
        }

        .scoring-table .points-positive {
            color: #22c55e;
            font-weight: 800;
        }

        .scoring-table .points-negative {
            color: #ef4444;
            font-weight: 800;
        }

        .scoring-table .masuk-row {
            background: rgba(34, 197, 94, 0.1);
        }

        .scoring-table .masuk-label {
            color: #22c55e;
            font-weight: 900;
            font-size: 13px;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .remi-card {
                width: 72px;
                height: 105px;
            }

            .remi-card .card-value {
                font-size: 26px;
            }

            .hand-card-remi {
                width: 56px;
                height: 82px;
            }

            .hand-card-remi .card-value {
                font-size: 18px;
            }

            .center-area {
                gap: 16px;
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
                <span class="game-type-badge">♠ REMI 41</span>
                <span class="deck-badge" id="deckBadge">Deck: 0</span>
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
            <div class="phase-indicator" id="phaseIndicator"></div>

            <!-- Center: Pile + Deck -->
            <div class="center-area">
                <div class="pile-wrapper" onclick="drawFromPile()">
                    <div class="pile-label">BUANGAN</div>
                    <div class="remi-card pile-card-remi" id="pileCard">
                        <span class="card-value">?</span>
                    </div>
                </div>

                <div class="deck-wrapper" onclick="drawFromDeck()">
                    <div class="deck-label">DECK</div>
                    <div class="remi-card deck-card-remi">
                        <span class="card-value">REMI</span>
                    </div>
                    <span class="deck-count" id="deckCount">0</span>
                </div>
            </div>

            <!-- My Hand -->
            <div class="hand-area">
                <div class="hand-controls">
                    <button class="btn btn-danger" id="discardBtn" onclick="discardSelectedCard()" disabled>
                        🗑️ Buang Kartu
                    </button>
                </div>
                <div class="hand-cards" id="handCards"></div>
            </div>
        </div>
    </div>

    <!-- Scoring Overlay -->
    <div class="scoring-overlay" id="scoringOverlay">
        <div class="scoring-title">📊 Hasil Permainan</div>
        <table class="scoring-table" id="scoringTable">
            <thead>
                <tr>
                    <th>Pemain</th>
                    <th>Status</th>
                    <th>Poin</th>
                </tr>
            </thead>
            <tbody id="scoringBody"></tbody>
        </table>
        <button class="btn btn-primary" onclick="window.location.href=APP_URL">🏠 Kembali ke Lobby</button>
    </div>
@endsection

@section('scripts')
    <script>
        const GAME_CODE = '{{ $game->code }}';
        const MY_PLAYER_ID = {{ $player->id }};
        const IS_HOST = {{ $player->is_host ? 'true' : 'false' }};

        let gameState = null;
        let selectedCard = null;
        let turnPhase = 'draw'; // 'draw' or 'discard'
        let hasDrawn = false;
        let pollInterval = null;

        // =================== SUIT DISPLAY ===================
        const suitSymbols = {
            spade: '♠', heart: '♥', club: '♣', diamond: '♦'
        };

        const suitNames = {
            spade: 'Sekop', heart: 'Hati', club: 'Keriting', diamond: 'Wajik'
        };

        function getSuitClass(suit) {
            return 'suit-' + suit;
        }

        function getSuitSymbol(suit) {
            return suitSymbols[suit] || '?';
        }

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

            // Deck count
            document.getElementById('deckCount').textContent = g.deck_count;
            document.getElementById('deckBadge').textContent = `Deck: ${g.deck_count}`;

            // Opponents
            renderOpponents(players);

            // Top card
            renderTopCard();

            // Turn status
            const isMyTurn = g.current_turn_player_id === MY_PLAYER_ID;
            const myPlayer = gameState.my_player;
            const turnStatus = document.getElementById('turnStatus');
            const phaseIndicator = document.getElementById('phaseIndicator');

            if (myPlayer.is_masuk) {
                turnStatus.textContent = '✅ Kamu sudah MASUK!';
                turnStatus.className = 'turn-status';
                phaseIndicator.innerHTML = '<span>Menunggu permainan selesai...</span>';
            } else if (isMyTurn) {
                turnStatus.textContent = '🟢 Giliranmu!';
                turnStatus.className = 'turn-status my-turn';
                if (!hasDrawn) {
                    phaseIndicator.innerHTML = '<span>Ambil kartu dari DECK atau BUANGAN</span>';
                } else {
                    phaseIndicator.innerHTML = '<span>Pilih 1 kartu untuk dibuang</span>';
                }
            } else {
                const currentP = players.find(p => p.id === g.current_turn_player_id);
                turnStatus.textContent = `⏳ Giliran ${currentP ? currentP.name : '...'}`;
                turnStatus.className = 'turn-status';
                phaseIndicator.innerHTML = '';
            }

            // Hand
            renderHand();

            // Discard button
            const discardBtn = document.getElementById('discardBtn');
            discardBtn.disabled = !hasDrawn || selectedCard === null;
        }

        function renderOpponents(players) {
            const area = document.getElementById('opponentsArea');

            const opponents = players.filter(p => !p.is_me);
            area.innerHTML = opponents.map(p => `
                    <div class="opponent ${p.is_current_turn ? 'is-turn' : ''} ${p.is_masuk ? 'is-masuk' : ''}">
                        <span class="opponent-name">${p.name}</span>
                        <span class="opponent-cards">${p.card_count} 🃏</span>
                        ${p.is_masuk ? '<span class="masuk-badge">MASUK!</span>' : ''}
                    </div>
                `).join('');
        }

        function renderTopCard() {
            const tc = gameState.top_card;
            if (!tc) {
                const pile = document.getElementById('pileCard');
                pile.className = 'remi-card pile-card-remi';
                pile.innerHTML = '<span class="card-value" style="font-size:14px;color:#999;">Kosong</span>';
                return;
            }

            const pile = document.getElementById('pileCard');
            pile.className = `remi-card pile-card-remi ${getSuitClass(tc.color)}`;
            pile.innerHTML = `
                    <span class="card-suit-icon">${getSuitSymbol(tc.color)}</span>
                    <span class="card-value">${tc.value}</span>
                    <span class="card-suit-big">${getSuitSymbol(tc.color)}</span>
                    <span class="card-suit-icon-br">${getSuitSymbol(tc.color)}</span>
                `;
        }

        function renderHand() {
            const hand = gameState.hand;
            const container = document.getElementById('handCards');

            // Sort hand: by suit then by value
            const suitOrder = { spade: 0, heart: 1, club: 2, diamond: 3 };
            const valueOrder = { '2': 2, '3': 3, '4': 4, '5': 5, '6': 6, '7': 7, '8': 8, '9': 9, '10': 10, 'J': 11, 'Q': 12, 'K': 13, 'A': 14 };

            const sorted = [...hand].sort((a, b) => {
                if (suitOrder[a.color] !== suitOrder[b.color]) return suitOrder[a.color] - suitOrder[b.color];
                return (valueOrder[a.value] || 0) - (valueOrder[b.value] || 0);
            });

            container.innerHTML = sorted.map(c => {
                const isSelected = selectedCard === c.game_card_id;
                return `
                        <div class="hand-card-remi ${getSuitClass(c.color)} ${isSelected ? 'selected' : ''}"
                             onclick="toggleSelect(${c.game_card_id})"
                             data-card-id="${c.game_card_id}">
                            <span class="card-suit-icon">${getSuitSymbol(c.color)}</span>
                            <span class="card-value">${c.value}</span>
                            <span class="card-suit-big">${getSuitSymbol(c.color)}</span>
                        </div>
                    `;
            }).join('');

            // Update discard button
            const discardBtn = document.getElementById('discardBtn');
            discardBtn.disabled = !hasDrawn || selectedCard === null;
        }

        function renderFinished() {
            const overlay = document.getElementById('scoringOverlay');
            const body = document.getElementById('scoringBody');

            if (gameState.scoring) {
                // Sort by points descending
                const sorted = [...gameState.scoring].sort((a, b) => b.points - a.points);

                body.innerHTML = sorted.map((p, idx) => {
                    const pointsClass = p.points >= 0 ? 'points-positive' : 'points-negative';
                    const rowClass = p.is_masuk ? 'masuk-row' : '';
                    const medal = idx === 0 ? '🥇 ' : idx === 1 ? '🥈 ' : idx === 2 ? '🥉 ' : '';
                    return `
                            <tr class="${rowClass}">
                                <td>${medal}${p.name}</td>
                                <td>${p.is_masuk ? '<span class="masuk-label">✅ MASUK</span>' : '❌ Belum Masuk'}</td>
                                <td class="${pointsClass}">${p.points > 0 ? '+' : ''}${p.points}</td>
                            </tr>
                        `;
                }).join('');
            }

            overlay.classList.add('visible');
        }

        // =================== INTERACTIONS ===================
        function toggleSelect(gcId) {
            if (!gameState) return;

            const g = gameState.game;
            const isMyTurn = g.current_turn_player_id === MY_PLAYER_ID;

            if (!isMyTurn || !hasDrawn) {
                if (!hasDrawn && isMyTurn) {
                    showToast('Ambil kartu dulu dari deck atau buangan!', 'info');
                } else if (!isMyTurn) {
                    showToast('Bukan giliranmu!', 'error');
                }
                return;
            }

            if (selectedCard === gcId) {
                selectedCard = null;
            } else {
                selectedCard = gcId;
            }
            renderHand();
        }

        async function drawFromDeck() {
            if (!gameState || gameState.game.current_turn_player_id !== MY_PLAYER_ID) {
                showToast('Bukan giliranmu!', 'error');
                return;
            }

            if (gameState.my_player.is_masuk) {
                showToast('Kamu sudah masuk!', 'info');
                return;
            }

            if (hasDrawn) {
                showToast('Kamu sudah ambil kartu! Buang satu kartu dulu.', 'error');
                return;
            }

            const res = await apiPost(`/game/${GAME_CODE}/draw`, { source: 'deck' });
            if (res.error) {
                showToast(res.error, 'error');
                return;
            }

            if (res.finished) {
                showToast('Deck habis! Menghitung skor...', 'info');
                hasDrawn = false;
                selectedCard = null;
                poll();
                return;
            }

            hasDrawn = true;
            showToast('Ambil 1 kartu dari deck 📤', 'info');
            selectedCard = null;
            poll();
        }

        async function drawFromPile() {
            if (!gameState || gameState.game.current_turn_player_id !== MY_PLAYER_ID) {
                showToast('Bukan giliranmu!', 'error');
                return;
            }

            if (gameState.my_player.is_masuk) {
                showToast('Kamu sudah masuk!', 'info');
                return;
            }

            if (hasDrawn) {
                showToast('Kamu sudah ambil kartu! Buang satu kartu dulu.', 'error');
                return;
            }

            const res = await apiPost(`/game/${GAME_CODE}/draw`, { source: 'pile' });
            if (res.error) {
                showToast(res.error, 'error');
                return;
            }

            hasDrawn = true;
            showToast('Ambil kartu dari buangan ♻️', 'info');
            selectedCard = null;
            poll();
        }

        async function discardSelectedCard() {
            if (selectedCard === null) {
                showToast('Pilih kartu yang mau dibuang!', 'error');
                return;
            }

            const res = await apiPost(`/game/${GAME_CODE}/discard`, { card_id: selectedCard });
            if (res.error) {
                showToast(res.error, 'error');
                return;
            }

            if (res.masuk) {
                showToast(`🎉 ${res.player_name} MASUK!`, 'success');
            }

            if (res.finished) {
                showToast('Permainan selesai!', 'info');
            }

            hasDrawn = false;
            selectedCard = null;
            poll();
        }

        async function startGame() {
            const res = await apiPost(`/game/${GAME_CODE}/start`);
            if (res.error) {
                showToast(res.error, 'error');
                return;
            }
            showToast('Game Remi 41 dimulai! ♠️', 'success');
            poll();
        }

        function copyCode() {
            navigator.clipboard.writeText(GAME_CODE).then(() => {
                showToast('Kode disalin! 📋', 'success');
            });
        }
    </script>
@endsection