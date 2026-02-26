<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gabut Card Games!')</title>
    {{--
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&display=swap" rel="stylesheet">
    --}}
    <style>
        :root {
            --bg-primary: #0a0e27;
            --bg-secondary: #131640;
            --bg-card: rgba(255, 255, 255, 0.05);
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.12);
            --text-primary: #e8eaf6;
            --text-secondary: #9fa8da;
            --accent: #7c4dff;
            --accent-glow: rgba(124, 77, 255, 0.4);
            --uno-red: #ef4444;
            --uno-blue: #3b82f6;
            --uno-green: #22c55e;
            --uno-yellow: #eab308;
            --uno-black: #1e1e2e;
            --success: #22c55e;
            --danger: #ef4444;
            --radius: 16px;
            --radius-sm: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 20% 50%, rgba(124, 77, 255, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(239, 68, 68, 0.06) 0%, transparent 50%);
            animation: bgShift 20s ease-in-out infinite alternate;
            z-index: 0;
            pointer-events: none;
        }

        @keyframes bgShift {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }

            100% {
                transform: translate(-5%, 3%) rotate(3deg);
            }
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Glass Card */
        .glass-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            backdrop-filter: blur(20px);
            padding: 32px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 28px;
            border: none;
            border-radius: var(--radius-sm);
            /* font-family: 'Outfit', sans-serif; */
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, #7c4dff 0%, #536dfe 100%);
            color: white;
            box-shadow: 0 4px 24px var(--accent-glow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px var(--accent-glow);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 24px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
        }

        .btn-ghost {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Input */
        .form-input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            transition: all 0.25s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(124, 77, 255, 0.3);
            }

            50% {
                box-shadow: 0 0 40px rgba(124, 77, 255, 0.6);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.5s ease forwards;
        }

        /* Toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            padding: 14px 24px;
            border-radius: var(--radius-sm);
            color: white;
            font-weight: 600;
            animation: slideIn 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .toast-error {
            background: rgba(239, 68, 68, 0.9);
        }

        .toast-success {
            background: rgba(34, 197, 94, 0.9);
        }

        .toast-info {
            background: rgba(59, 130, 246, 0.9);
        }
    </style>
    @yield('styles')
</head>

<body>
    <div id="toast-container" class="toast-container"></div>
    @yield('content')

    <script>
        // Global variables for subdirectory support
        const APP_URL = "{{ url('/') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(20px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        async function apiPost(url, data = {}) {
            // Ensure URL is absolute relative to APP_URL if it starts with /
            const fullUrl = url.startsWith('/') ? APP_URL + url : url;

            const res = await fetch(fullUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });
            return await res.json();
        }

        async function apiGet(url) {
            const fullUrl = url.startsWith('/') ? APP_URL + url : url;

            const res = await fetch(fullUrl, {
                headers: { 'Accept': 'application/json' },
            });
            return await res.json();
        }
    </script>
    @yield('scripts')
</body>

</html>