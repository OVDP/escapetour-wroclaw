<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Groep - Red de Żabka-kikker</title>
    <style>
        :root {
            --bg: #fdfdfd;
            --card: #ffffff;
            --line: #c8e5d3;
            --text: #1f2a44;
            --muted: #5774a8;
            --orange: #007b3d;
            --red: #a72335;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            font-family: "Avenir Next", "Trebuchet MS", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 78% 8%, rgba(0,123,61,.14), transparent 36%),
                radial-gradient(circle at 12% 92%, rgba(49,91,156,.12), transparent 40%),
                var(--bg);
        }

        .card {
            width: min(720px, 100%);
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--card);
            padding: 24px;
            box-shadow: 0 10px 30px rgba(21,34,58,.08);
        }

        h1 { margin: 0 0 10px; color: var(--orange); }
        p { margin: 0 0 12px; }
        .muted { color: var(--muted); }
        .bad { color: var(--red); }
        label { display:block; margin-bottom: 8px; font-weight:700; }
        input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #c8d5ee;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 14px;
            color: var(--text);
        }

        button {
            border: 0;
            border-radius: 10px;
            padding: 11px 14px;
            font-weight: 700;
            cursor: pointer;
            background: linear-gradient(135deg, #008f45, #007b3d);
            color: #ffffff;
        }
    </style>
<link rel="stylesheet" href="{{ asset('css/zabka.css').'?v='.filemtime(public_path('css/zabka.css')) }}">
</head>
<body>
    <main class="card">
        @if($route)
            <h1>Groep: {{ $route->name }}</h1>
            <p>Onderzoek: <strong>{{ $route->game?->name ?? 'Red de Żabka-kikker' }}</strong></p>
            <p>{{ $route->game?->story_intro }}</p>
            <form method="POST" action="{{ route('join.start', $token) }}">
                @csrf
                <button type="submit">Open deze groep</button>
            </form>

            @if($errors->any())
                <p class="bad" style="margin-top:12px;">{{ $errors->first() }}</p>
            @endif
        @else
            <h1>Ongeldige link</h1>
            <p class="bad">Deze groepslink is niet (meer) geldig.</p>
            <p class="muted">Vraag de spelleider om een nieuwe QR-code.</p>
        @endif
    </main>
</body>
</html>
