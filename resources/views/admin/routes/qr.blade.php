<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR · {{ $route->name }}</title>
    <style>
        :root {
            --bg: #fdfdfd;
            --card: #ffffff;
            --line: #c8e5d3;
            --text: #1f2a44;
            --orange: #007b3d;
            --blue: #006b35;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: "Avenir Next", "Trebuchet MS", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 78% 8%, rgba(0,123,61,.14), transparent 36%),
                radial-gradient(circle at 12% 92%, rgba(49,91,156,.12), transparent 40%),
                var(--bg);
            color: var(--text);
        }

        .panel {
            width: min(560px, 100%);
            border-radius: 16px;
            border: 1px solid var(--line);
            background: var(--card);
            padding: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(21,34,58,.08);
        }

        h1 { margin-top: 0; color: var(--orange); }
        img { width: min(420px, 100%); background: #fff; border-radius: 14px; padding: 12px; }
        code { color: var(--blue); }

        .actions { margin-top: 14px; display:flex; justify-content:center; gap:10px; flex-wrap:wrap; }
        .btn {
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
            background: #edf2fb;
            color: #005a2d;
            text-decoration: none;
            border: 1px solid #c8d5ee;
        }
    </style>
<link rel="stylesheet" href="{{ asset('css/zabka.css') }}">
</head>
<body>
    <main class="panel">
        <h1>QR-code · {{ $route->name }}</h1>
        <p>Laat spelers deze QR-code scannen om direct bij de juiste groep te landen.</p>

        <img src="{{ $qrUrl }}" alt="QR-code voor {{ $route->name }}">

        <p style="margin-top: 14px;"><code>{{ $joinUrl }}</code></p>

        <div class="actions">
            <a class="btn" href="#" onclick="window.print(); return false;">Print</a>
            <a class="btn" href="{{ route('admin.routes.edit', $route) }}">Terug naar groep</a>
        </div>
    </main>
</body>
</html>
