<!DOCTYPE html>
<html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Red de Żabka-kikker</title><link rel="stylesheet" href="{{ asset('css/zabka.css').'?v='.filemtime(public_path('css/zabka.css')) }}"></head>
<body><main class="standalone"><div class="card">
<p class="eyebrow">ESCAPETOUR · WROCŁAW</p><h1>Red de Żabka-kikker</h1>
<p>Biedronka heeft de Żabka-kikker ontvoerd. Verzamel op 12 locaties bewijs voor de policja.</p>
@if($errors->any())<p class="bad" role="alert">{{ $errors->first() }}</p>@endif
@if(session('error'))<p class="bad" role="alert">{{ session('error') }}</p>@endif
@if($ownGroup)
<h2>Jullie groep: {{ $ownGroup->name }}</h2>
<p>Op dit apparaat is al een groep aangemaakt.</p>
<form method="POST" action="{{ route('join.start', $ownGroup->join_token) }}">@csrf<button class="btn" type="submit">Verder met mijn groep</button></form>
@elseif($gameAvailable)
<h2>Maak jullie groep aan</h2>
<p>Kies een groepsnaam. Daarna bepaalt de spelleiding waar jullie beginnen.</p>
<form method="POST" action="{{ route('groups.register') }}">@csrf
<label for="group_name">Groepsnaam</label><input id="group_name" name="group_name" maxlength="100" required value="{{ old('group_name') }}" autocomplete="off">
<button class="btn" type="submit">Groep aanmaken</button></form>
<p class="muted">Je kunt in deze browser één groep aanmaken. Bewaar je browsercookies om je groep terug te vinden.</p>
@else<p>Er is nog geen spel geopend. Meld je bij de spelleiding.</p>@endif
<p><a href="{{ route('location.test') }}">Test je GPS-locatie in Utrecht</a></p>
</div></main></body></html>
