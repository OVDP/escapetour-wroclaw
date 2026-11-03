<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Red de Żabka-kikker</title>
    <style>
        :root {
            --bg: #fdfdfd;
            --card: #ffffff;
            --line: #c8e5d3;
            --text: #1f2a44;
            --muted: #5774a8;
            --orange: #007b3d;
            --orange-soft: #e5f5e9;
            --blue: #006b35;
            --red: #a72335;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Avenir Next", "Trebuchet MS", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 85% 10%, rgba(0,123,61,.14), transparent 34%),
                radial-gradient(circle at 10% 90%, rgba(49,91,156,.12), transparent 38%),
                var(--bg);
            color: var(--text);
            padding: 18px;
        }

        .panel {
            width: min(430px, 100%);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(21, 34, 58, 0.08);
        }

        h1 { margin-top: 0; color: var(--orange); }
        label { display: block; margin-bottom: 8px; font-weight: 700; }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border-radius: 10px;
            border: 1px solid #c8d5ee;
            background: #fff;
            color: var(--text);
            margin-bottom: 16px;
        }

        button {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 11px;
            font-weight: 700;
            cursor: pointer;
            background: linear-gradient(135deg, #008f45, #007b3d);
            color: #ffffff;
        }

        .alert {
            margin-bottom: 14px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #efb3b3;
            background: #fff1f1;
            color: var(--red);
        }

        .ok {
            border: 1px solid #b7d4c6;
            background: #f2fff7;
            color: #2a6b45;
        }
    </style>
<link rel="stylesheet" href="{{ asset('css/zabka.css').'?v='.filemtime(public_path('css/zabka.css')) }}">
</head>
<body>
    <div class="panel">
        <h1>Admin Inloggen</h1>

        @if(session('success'))
            <div class="alert ok">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <label for="passphrase">Passphrase</label>
            <input id="passphrase" type="password" name="passphrase" autocomplete="current-password" required autofocus>

            <button type="submit">Inloggen</button>
        </form>
    </div>
</body>
</html>
