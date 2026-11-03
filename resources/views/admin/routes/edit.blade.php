@extends('layouts.admin')
@section('content')
<h1>Groep · {{ $route->name }}</h1>
<div class="card" style="margin-bottom:20px">
<form method="POST" action="{{ route('admin.routes.update', $route) }}">@csrf @method('PUT')
<label for="name">Groepsnaam</label><input id="name" name="name" value="{{ old('name', $route->name) }}" required>
<button class="btn" type="submit">Naam opslaan</button></form>
</div>
<div class="card" style="margin-bottom:20px">
<h2>Startlocatie</h2>
<p>{{ $route->start_assigned ? 'Startlocatie ingesteld.' : 'Deze groep wacht op een startlocatie.' }}</p>
@if($completedSteps === 0 && !$progress->location_locked_at && $route->submissions()->doesntExist())
<form method="POST" action="{{ route('admin.routes.start', $route) }}">@csrf @method('PUT')
<label for="starting_quest_step_id">Waar begint deze groep?</label>
<select id="starting_quest_step_id" name="starting_quest_step_id" required>
<option value="">Kies een plek</option>
@foreach($availableQuestSteps as $step)<option value="{{ $step->id }}" @selected((int) old('starting_quest_step_id', $route->starting_quest_step_id) === $step->id)>{{ $step->title }}</option>@endforeach
</select>
<p>Bij start op plek 5 wordt de volgorde 5 → 6 → … → 12 → 1 → … → 4. Iedere opdracht komt één keer aan bod.</p>
<button class="btn" type="submit">Startlocatie toewijzen</button></form>
@else<p>Deze groep is begonnen. De startlocatie staat vast om hun voortgang te bewaren.</p>@endif
</div>
<div class="card" style="margin-bottom:20px"><h2>Voortgang</h2>
<p>{{ $progressPercent }}% · {{ $completedSteps }} van {{ $totalSteps }} opdrachten afgerond</p>
<p>Huidige opdracht: {{ $currentStep?->title ?? ($route->start_assigned ? 'Afgerond' : 'Wacht op startlocatie') }}</p>
<ol>@foreach($route->questSteps as $step)<li>{{ $step->pivot->sort_order < $progress->current_sort_order ? '✓ ' : '' }}{{ $step->title }}</li>@endforeach</ol>
<p><a href="{{ route('join.with-token', $route->join_token) }}">Groepslink</a> · <a href="{{ route('admin.routes.qr', $route) }}" target="_blank" rel="noopener">QR-code voor deze groep</a></p>
</div>
<div class="card" style="margin-bottom:20px"><h2>Foto’s en antwoorden beoordelen</h2>
@forelse($pendingSubmissions as $submission)
<article><h3>{{ $submission->questStep?->title }}</h3><p>{{ $submission->submitted_answer }}</p>
@if($submission->photo_path)<p><a href="{{ route('admin.submissions.photo', $submission) }}" target="_blank" rel="noopener">Bekijk foto</a></p>@endif
<p>{{ $submission->questStep?->review_notes }}</p>
<div class="actions"><form method="POST" action="{{ route('admin.routes.submissions.approve', [$route, $submission]) }}">@csrf<button class="btn" type="submit">Goedkeuren</button></form>
<form method="POST" action="{{ route('admin.routes.submissions.reject', [$route, $submission]) }}">@csrf<button class="btn-danger" type="submit">Afkeuren</button></form></div></article>
@empty<p>Er wachten geen inzendingen op beoordeling.</p>@endforelse
</div>
<div class="card"><h2>Berichten voor deze groep</h2>
<form method="POST" action="{{ route('admin.routes.messages.post', $route) }}">@csrf<input type="hidden" name="audience" value="group">
<label for="message">Bericht</label><textarea id="message" name="message" required></textarea><button class="btn" type="submit">Verstuur bericht</button></form>
@foreach($activeMessages as $message)<p><strong>{{ $message->title }}</strong> {{ $message->message }}</p>@endforeach
</div>
@endsection
