<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red de Żabka-kikker Admin</title>
    <style>
        :root {
            --bg: #fdfdfd;
            --bg-soft: #ffffff;
            --panel: #ffffff;
            --line: #c8e5d3;
            --text: #1f2a44;
            --muted: #5774a8;
            --accent: #007b3d;
            --accent-strong: #007b3d;
            --danger: #354b40;
            --success-bg: #f2fff7;
            --success-line: #b7d4c6;
            --error-bg: #fff1f1;
            --error-line: #efb3b3;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Avenir Next", "Trebuchet MS", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top right, rgba(0, 123, 61, 0.14), transparent 34%),
                radial-gradient(circle at bottom left, rgba(49, 91, 156, 0.12), transparent 38%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            background: linear-gradient(180deg, #fff, #edf7f0);
            backdrop-filter: blur(6px);
            padding: 26px;
            border-right: 1px solid var(--line);
        }

        .sidebar h1 {
            font-size: 21px;
            letter-spacing: 0.04em;
            color: var(--accent-strong);
            margin-bottom: 26px;
        }

        .sidebar a {
            display: block;
            color: #006b35;
            text-decoration: none;
            margin-bottom: 12px;
            padding: 9px 10px;
            border-radius: 8px;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .sidebar a:hover {
            color: var(--text);
            background: rgba(0, 123, 61, 0.12);
        }

        .menu-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .pending-badge {
            min-width: 24px;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid rgba(0, 123, 61, 0.25);
            background: rgba(0, 123, 61, 0.12);
            color: #28573a;
            font-size: 12px;
            text-align: center;
            font-weight: 700;
        }

        .pending-badge.is-hidden {
            display: none;
        }

        .toast {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 1200;
            max-width: 360px;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid rgba(0, 123, 61, 0.25);
            background: #ffffff;
            color: #1f2a44;
            display: none;
        }

        .toast.show {
            display: block;
        }

        .urgent-overlay {
            position: fixed;
            inset: 0;
            z-index: 1300;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(18, 8, 2, 0.72);
            backdrop-filter: blur(3px);
        }

        .urgent-overlay.show {
            display: flex;
        }

        .urgent-card {
            width: min(760px, 100%);
            border-radius: 16px;
            border: 1px solid rgba(0, 123, 61, 0.25);
            background: #ffffff;
            padding: 20px;
        }

        .urgent-title {
            margin-top: 0;
            color: #007b3d;
        }

        .content {
            margin-left: 250px;
            padding: 34px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 20px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
            box-shadow: 0 8px 24px rgba(21,34,58,.06);
        }

        .card h2 {
            margin-top: 0;
            color: var(--accent);
            letter-spacing: 0.02em;
        }

        .btn,
        .btn-danger,
        .btn-secondary {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            border: 0;
            cursor: pointer;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }

        .btn:hover,
        .btn-danger:hover,
        .btn-secondary:hover {
            transform: translateY(-1px);
            opacity: 0.95;
        }

        .btn {
            background: linear-gradient(135deg, var(--accent), #007b3d);
            color: #ffffff;
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-secondary {
            background: #edf2fb;
            color: #005a2d;
            border: 1px solid #c8d5ee;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .route-editor-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .step-picker {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px;
            background: #ffffff;
            max-height: 460px;
            overflow: auto;
        }

        .step-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 6px;
            border: 1px solid transparent;
            background: #ffffff;
        }

        .step-option:hover {
            border-color: var(--line);
            background: rgba(241, 193, 91, 0.08);
        }

        .sortable-list {
            margin: 0;
            padding: 0;
            list-style: none;
            border: 1px solid var(--line);
            border-radius: 12px;
            min-height: 56px;
            background: #ffffff;
        }

        .sortable-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 10px;
            padding: 11px 12px;
            border-bottom: 1px solid var(--line);
            cursor: move;
            background: #ffffff;
        }

        .sortable-item:last-child {
            border-bottom: 0;
        }

        .sortable-item.dragging {
            opacity: 0.4;
        }

        .drag-handle {
            font-weight: 700;
            color: var(--muted);
            user-select: none;
        }

        .sort-badge {
            background: rgba(241, 193, 91, 0.2);
            color: #ffe6b4;
            border: 1px solid rgba(241, 193, 91, 0.35);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        th {
            color: var(--muted);
            font-weight: 700;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #c8d5ee;
            background: var(--bg-soft);
            color: var(--text);
            margin-top: 6px;
            margin-bottom: 16px;
        }

        label {
            font-weight: 700;
            display: block;
        }

        .callout {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 10px;
            background: rgba(241, 193, 91, 0.14);
            border: 1px solid rgba(241, 193, 91, 0.35);
            color: #ffe6b4;
        }

        .alert-success {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 10px;
            background: var(--success-bg);
            border: 1px solid var(--success-line);
            color: #2a6b45;
        }

        .alert-error {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 10px;
            background: var(--error-bg);
            border: 1px solid var(--error-line);
            color: #a72335;
        }

        @media (max-width: 900px) {
            .sidebar {
                position: static;
                width: 100%;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .content {
                margin-left: 0;
                padding: 20px;
            }

            .route-editor-grid {
                grid-template-columns: 1fr;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
<link rel="stylesheet" href="{{ asset('css/zabka.css').'?v='.filemtime(public_path('css/zabka.css')) }}">
</head>
<body>
    <div class="sidebar">
        <h1>Red de Żabka-kikker</h1>
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.routes.index') }}">Groepen</a>
        <a href="{{ route('admin.quest-steps.index') }}">Opdrachten</a>
        <a href="{{ route('admin.submissions.pending') }}" class="menu-row">
            <span>Goedkeuringen</span>
            <span id="pending-approvals-badge" class="pending-badge is-hidden">0</span>
        </a>
        @if(session('admin_authenticated'))
            <form method="POST" action="{{ route('admin.logout') }}" style="margin-top: 18px;">
                @csrf
                <button class="btn-secondary" type="submit" style="width: 100%;">Uitloggen</button>
            </form>
        @endif
    </div>

    <div class="content">
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                <strong>Er ging iets mis:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    @if(session('admin_authenticated'))
        <div id="pending-approvals-toast" class="toast"></div>
        <div id="urgent-approval-overlay" class="urgent-overlay">
            <div class="urgent-card">
                <h2 class="urgent-title">Nieuwe goedkeuringsaanvragen</h2>
                <p id="urgent-approval-text" style="margin-top: 0;"></p>
                <ul id="urgent-approval-items" style="margin-bottom: 16px;"></ul>
                <div class="actions">
                    <a class="btn" href="{{ route('admin.submissions.pending') }}">Open goedkeuringen</a>
                    <button class="btn-secondary" type="button" id="urgent-approval-close">Sluiten</button>
                </div>
            </div>
        </div>
        <script>
            (() => {
                const badgeEl = document.getElementById('pending-approvals-badge');
                const toastEl = document.getElementById('pending-approvals-toast');
                const urgentOverlay = document.getElementById('urgent-approval-overlay');
                const urgentTextEl = document.getElementById('urgent-approval-text');
                const urgentItemsEl = document.getElementById('urgent-approval-items');
                const urgentCloseEl = document.getElementById('urgent-approval-close');
                let previousCount = null;

                const beep = () => {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.value = 880;
                        gain.gain.value = 0.02;
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.18);
                    } catch (error) {}
                };

                const showToast = (message) => {
                    if (!toastEl) return;
                    toastEl.textContent = message;
                    toastEl.classList.add('show');
                    window.setTimeout(() => toastEl.classList.remove('show'), 6000);
                };

                const showUrgentOverlay = (count, items) => {
                    if (!urgentOverlay || !urgentTextEl || !urgentItemsEl) return;
                    urgentTextEl.textContent = `${count} inzending(en) wachten op jullie goedkeuring.`;
                    urgentItemsEl.innerHTML = (Array.isArray(items) ? items : []).map((item) =>
                        `<li><strong>${item.group}</strong> · ${item.quest_step}</li>`
                    ).join('');
                    urgentOverlay.classList.add('show');
                };

                const updatePending = () => {
                    fetch('{{ route('admin.submissions.pending.live') }}')
                        .then((response) => response.json())
                        .then((data) => {
                            const count = Number(data.pending_count ?? 0);

                            if (badgeEl) {
                                badgeEl.textContent = String(count);
                                badgeEl.classList.toggle('is-hidden', count <= 0);
                            }

                            if (previousCount !== null && count > previousCount) {
                                const added = count - previousCount;
                                showToast(`Nieuwe goedkeuringsaanvraag${added > 1 ? 'en' : ''}: +${added}`);
                                showUrgentOverlay(count, data.items);
                                beep();
                                if (document.visibilityState !== 'visible' && 'Notification' in window) {
                                    if (Notification.permission === 'granted') {
                                        new Notification('Nieuwe goedkeuringsaanvraag', {
                                            body: `${count} inzending(en) wachten op goedkeuring.`,
                                        });
                                    } else if (Notification.permission !== 'denied') {
                                        Notification.requestPermission();
                                    }
                                }
                            }

                            previousCount = count;
                        })
                        .catch(() => {});
                };

                updatePending();
                window.setInterval(updatePending, 8000);

                if (urgentCloseEl && urgentOverlay) {
                    urgentCloseEl.addEventListener('click', () => urgentOverlay.classList.remove('show'));
                    urgentOverlay.addEventListener('click', (event) => {
                        if (event.target === urgentOverlay) urgentOverlay.classList.remove('show');
                    });
                }
            })();
        </script>
    @endif
</body>
</html>
