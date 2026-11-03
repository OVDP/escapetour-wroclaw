@extends('layouts.admin')

@section('content')
    <h1>Spelinstellingen · Red de Żabka-kikker</h1>

    <div class="card">
        <form method="POST" action="{{ route('admin.games.update', $game) }}">
            @csrf
            @method('PUT')

            <label>Naam spel</label>
            <input type="text" name="name" value="{{ old('name', $game->name) }}" required>

            <p style="color: var(--muted); margin-top: -6px;">Single-case opzet: dit spel gebruikt één vast onderzoek.</p>

            <hr style="border-color:var(--line); margin: 24px 0;">
            <h2 style="font-size: 18px;">Zaakdetails</h2>

            <label>Plaats delict / locatie incident</label>
            <input type="text" name="incident_location" value="{{ old('incident_location', $game->incident_location) }}">

            <label>Moment van incident</label>
            <input type="datetime-local" name="incident_at" value="{{ old('incident_at', $game->incident_at ? \Carbon\Carbon::parse($game->incident_at)->format('Y-m-d\TH:i') : '') }}">

            <label>Briefing voor groepen</label>
            <textarea name="case_briefing" rows="5">{{ old('case_briefing', $game->case_briefing) }}</textarea>

            <hr style="border-color:var(--line); margin: 24px 0;">
            <h2 style="font-size: 18px;">Mogelijke verdachten</h2>

            <label>Mogelijke verdachten (één per regel)</label>
            <textarea name="possible_suspects_text" rows="5">{{ old('possible_suspects_text', implode("\n", $game->possible_suspects ?? [])) }}</textarea>

            <hr style="border-color:var(--line); margin: 24px 0;">
            <h2 style="font-size: 18px;">Verhaallijn</h2>

            <label>Intro verhaallijn (start spel)</label>
            <textarea name="story_intro" rows="4">{{ old('story_intro', $game->story_intro) }}</textarea>

            <label>Middenstuk verhaallijn (halverwege)</label>
            <textarea name="story_mid" rows="4">{{ old('story_mid', $game->story_mid) }}</textarea>

            <label>Eind verhaallijn (laatste fase)</label>
            <textarea name="story_final" rows="4">{{ old('story_final', $game->story_final) }}</textarea>

            <button type="submit" class="btn">Opslaan</button>
        </form>
    </div>
@endsection
