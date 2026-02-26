@extends('layouts.app')

@section('title', 'Uno Gaes! - Lobby')

@section('styles')
    <style>
        .lobby-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .lobby-card {
            max-width: 480px;
            width: 100%;
            text-align: center;
        }

        .logo {
            margin-bottom: 32px;
            animation: fadeInUp 0.6s ease;
        }

        .logo h1 {
            font-size: 56px;
            font-weight: 900;
            background: linear-gradient(135deg, var(--uno-red), var(--uno-yellow), var(--uno-green), var(--uno-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: none;
            filter: drop-shadow(0 0 20px rgba(124, 77, 255, 0.3));
        }

        .logo p {
            color: var(--text-secondary);
            font-size: 16px;
            margin-top: 8px;
        }

        .tab-buttons {
            display: flex;
            gap: 0;
            margin-bottom: 28px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: var(--radius-sm);
            padding: 4px;
            animation: fadeInUp 0.6s ease 0.1s both;
        }

        .tab-btn {
            flex: 1;
            padding: 12px;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background: var(--accent);
            color: white;
            box-shadow: 0 4px 16px var(--accent-glow);
        }

        .tab-content {
            display: none;
            animation: fadeInUp 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        .form-section {
            animation: fadeInUp 0.6s ease 0.2s both;
        }

        .cards-decoration {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 32px;
            animation: fadeInUp 0.6s ease 0.05s both;
        }

        .mini-card {
            width: 40px;
            height: 56px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 18px;
            color: white;
            transform: rotate(var(--rot));
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .mini-card:hover {
            transform: rotate(0deg) scale(1.15) translateY(-8px);
        }
    </style>
@endsection

@section('content')
    <div class="lobby-wrapper">
        <div class="lobby-card glass-card">
            <div class="logo">
                <h1>GABUT CARD GAMES</h1>
                <p>Main bareng teman, beda perangkat! 🎴</p>
            </div>

            <div class="cards-decoration">
                <div class="mini-card" style="background: var(--uno-red); --rot: -12deg;">7</div>
                <div class="mini-card" style="background: var(--uno-blue); --rot: -4deg;">⊘</div>
                <div class="mini-card" style="background: var(--uno-green); --rot: 3deg;">↺</div>
                <div class="mini-card" style="background: var(--uno-yellow); --rot: 10deg;">+2</div>
                <div class="mini-card"
                    style="background: var(--uno-black); --rot: 16deg; border: 2px solid rgba(255,255,255,0.2);">+4</div>
            </div>

            <div class="tab-buttons">
                <button class="tab-btn active" onclick="switchTab('create')">Buat Game</button>
                <button class="tab-btn" onclick="switchTab('join')">Gabung Game</button>
            </div>

            <div id="tab-create" class="tab-content active form-section">
                <div class="form-group">
                    <label class="form-label">Nama Kamu</label>
                    <input type="text" id="create-name" class="form-input" placeholder="Masukkan namamu..." maxlength="50">
                </div>
                <button class="btn btn-primary" style="width: 100%;" onclick="createGame()">
                    🎮 Buat Game Baru
                </button>
            </div>

            <div id="tab-join" class="tab-content form-section">
                <div class="form-group">
                    <label class="form-label">Nama Kamu</label>
                    <input type="text" id="join-name" class="form-input" placeholder="Masukkan namamu..." maxlength="50">
                </div>
                <div class="form-group">
                    <label class="form-label">Kode Game</label>
                    <input type="text" id="join-code" class="form-input" placeholder="Masukkan 6 digit kode..."
                        maxlength="6"
                        style="text-transform: uppercase; letter-spacing: 6px; text-align: center; font-size: 24px; font-weight: 700;">
                </div>
                <button class="btn btn-success" style="width: 100%;" onclick="joinGame()">
                    🚀 Gabung Game
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach((b, i) => {
                b.classList.toggle('active', (tab === 'create' && i === 0) || (tab === 'join' && i === 1));
            });
            document.getElementById('tab-create').classList.toggle('active', tab === 'create');
            document.getElementById('tab-join').classList.toggle('active', tab === 'join');
        }

        async function createGame() {
            const name = document.getElementById('create-name').value.trim();
            if (!name) { showToast('Isi nama dulu ya!', 'error'); return; }

            const res = await apiPost('/game/create', { player_name: name });
            if (res.error) { showToast(res.error, 'error'); return; }
            window.location.href = APP_URL + '/game/' + res.code;
        }

        async function joinGame() {
            const name = document.getElementById('join-name').value.trim();
            const code = document.getElementById('join-code').value.trim().toUpperCase();
            if (!name) { showToast('Isi nama dulu ya!', 'error'); return; }
            if (code.length !== 6) { showToast('Kode harus 6 karakter!', 'error'); return; }

            const res = await apiPost('/game/join', { player_name: name, code: code });
            if (res.error) { showToast(res.error, 'error'); return; }
            window.location.href = APP_URL + '/game/' + res.code;
        }
    </script>
@endsection