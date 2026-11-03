@extends('layouts.admin')

@section('content')
    <h1>Speloverzicht · {{ $game->name }}</h1>

    <div class="card">
        <p><strong>Plaats delict:</strong> {{ $game->incident_location ?? '-' }}</p>
        <p><strong>Moment incident:</strong> {{ $game->incident_at ?? '-' }}</p>
        <p><strong>Briefing:</strong><br>{{ $game->case_briefing ?: '-' }}</p>
        <p><strong>Mogelijke verdachten:</strong> {{ !empty($game->possible_suspects) ? implode(', ', $game->possible_suspects) : '-' }}</p>
        <p><strong>Verhaallijn intro:</strong><br>{{ $game->story_intro ?: '-' }}</p>
        <p><strong>Verhaallijn midden:</strong><br>{{ $game->story_mid ?: '-' }}</p>
        <p><strong>Verhaallijn finale:</strong><br>{{ $game->story_final ?: '-' }}</p>
    </div>
@endsection
