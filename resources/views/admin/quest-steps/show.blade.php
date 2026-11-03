@extends('layouts.admin')

@section('content')
    @if($quest_step->review_notes)
        <div class="card"><strong>Notities uit de brainstorm:</strong> {{ $quest_step->review_notes }}</div>
    @endif
    @if($quest_step->question_image)
        <img src="{{ asset($quest_step->question_image) }}" alt="Puzzelafbeelding" style="max-width:600px;width:100%;">
    @endif

    <h1>{{ $quest_step->title }}</h1>

    <div class="card">
        <p><strong>Onderzoek:</strong> {{ $quest_step->game?->name ?? '-' }}</p>
        <p><strong>Vraagtype:</strong> {{ $quest_step->question_type ?? ($quest_step->requires_location ? 'location' : 'direct') }}</p>
        <p><strong>Controlemodus:</strong> {{ $quest_step->requiresManualApproval() ? 'Handmatig' : 'Automatisch' }}</p>
        <p><strong>Verhaalinleiding:</strong><br>{{ $quest_step->story_intro ?: '-' }}</p>
        <p><strong>Vraagtekst:</strong><br>{{ $quest_step->question_text ?: '-' }}</p>
        <p><strong>Extra toelichting:</strong><br>{{ $quest_step->extra_explanation ?: '-' }}</p>
        <p><strong>Correct antwoord:</strong> {{ $quest_step->correct_answer ?: '-' }}</p>
        @if(!empty($quest_step->multiple_choice_json))
            <p><strong>Multiple choice opties:</strong> {{ implode(', ', $quest_step->multiple_choice_json) }}</p>
            <p><strong>Juiste optie:</strong> {{ $quest_step->correct_choice ?: '-' }}</p>
        @endif
        <p><strong>Timeout bij fout antwoord:</strong> {{ $quest_step->timeout_seconds ?? 30 }} seconden</p>
        <p><strong>Handmatige goedkeuring:</strong> {{ $quest_step->manual_approval ? 'Ja' : 'Nee' }}</p>
        <p><strong>WhatsApp inzending:</strong> {{ $quest_step->accepts_whatsapp_upload ? 'Ja' : 'Nee' }}</p>
        <p><strong>Beloning verhaallijn:</strong><br>{{ $quest_step->reward_story_text ?: '-' }}</p>
        <p><strong>Beloning letter:</strong> {{ $quest_step->reward_letter ?: '-' }}</p>
        <p><strong>Beloning verdachte:</strong> {{ $quest_step->reward_suspect_name ?: '-' }}</p>
        <p><strong>Startlocatie vereist:</strong> {{ $quest_step->requires_location ? 'Ja' : 'Nee' }}</p>
        <p><strong>Startlocatie adres:</strong> {{ $quest_step->start_location_address ?? '-' }}</p>
        <p><strong>Startlocatie latitude:</strong> {{ $quest_step->start_location_lat ?? '-' }}</p>
        <p><strong>Startlocatie longitude:</strong> {{ $quest_step->start_location_lng ?? '-' }}</p>
        <p><strong>Startlocatie straal:</strong> {{ $quest_step->start_location_radius_meters ?? '-' }}</p>
        <p><strong>Vraag pas tonen na startlocatie:</strong> {{ $quest_step->show_question_only_when_in_radius ? 'Ja' : 'Nee' }}</p>
        <p><strong>Inzendlocatie adres:</strong> {{ $quest_step->location_address ?? '-' }}</p>
        <p><strong>Inzendlocatie latitude:</strong> {{ $quest_step->location_lat ?? '-' }}</p>
        <p><strong>Inzendlocatie longitude:</strong> {{ $quest_step->location_lng ?? '-' }}</p>
        <p><strong>Inzendlocatie straal:</strong> {{ $quest_step->location_radius_meters ?? '-' }}</p>
        <p><strong>Locatie inzenden als antwoord:</strong> {{ $quest_step->location_submission_required ? 'Ja' : 'Nee' }}</p>

        <hr style="border-color:var(--line); margin: 24px 0;">
        <p><strong>Gekoppeld aan groepen:</strong></p>
        @if($quest_step->routes->isEmpty())
            <p>-</p>
        @else
            <ul>
                @foreach($quest_step->routes->sortBy('pivot.sort_order') as $route)
                    <li>{{ $route->name }} (volgorde: {{ $route->pivot->sort_order }})</li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
