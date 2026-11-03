<!DOCTYPE html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Wachten op startlocatie · Żabka</title><link rel="stylesheet" href="{{ asset('css/zabka.css').'?v='.filemtime(public_path('css/zabka.css')) }}"></head>
<body><main class="standalone"><div class="card">
<p class="eyebrow">RED DE ŻABKA-KIKKER</p><h1>{{ $group->name }}</h1>
<h2>Je groep is aangemaakt</h2><p>De spelleiding kiest nu jullie startlocatie. Zodra die klaarstaat, verschijnt hier automatisch jullie eerste opdracht.</p>
<p>Na het startpunt gaan jullie steeds naar de volgende plek, tot jullie alle 12 opdrachten hebben afgerond.</p>
<a class="btn-secondary" href="{{ route('play.dashboard') }}">Opnieuw controleren</a>
</div></main>
<script>setInterval(async () => { try { const response = await fetch(@json(route('play.live'))); if (response.status === 401) { location.href = @json(route('landing')); return; } const data = await response.json(); if (data.start_assigned) location.reload(); } catch {} }, 4000);</script>
</body></html>
