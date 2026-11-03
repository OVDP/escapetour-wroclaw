@extends('layouts.admin')
@section('content')
<h1>Spelleiding</h1>
<div class="card-grid">
<div class="card"><h2>Goedkeuringen</h2><p>{{ $pendingApprovalsCount }}</p><a href="{{ route('admin.submissions.pending') }}">Bekijk inzendingen</a></div>
<div class="card"><h2>Groepen</h2><p>{{ $routesCount }}</p><a href="{{ route('admin.routes.index') }}">Startlocaties instellen</a></div>
<div class="card"><h2>Opdrachten</h2><p>{{ $stepsCount }}</p></div>
</div>
<div class="card" style="margin-top:20px"><h2>Voortgang per groep</h2>
@if($groupsOverview->isEmpty())<p>Er zijn nog geen groepen aangemaakt.</p>@else
<table><thead><tr><th>Groep</th><th>Status</th><th>Voortgang</th><th>Laatst gezien</th></tr></thead>
<tbody>@foreach($groupsOverview as $group)<tr>
<td><a href="{{ route('admin.routes.edit', $group['id']) }}">{{ $group['name'] }}</a></td>
<td>{{ $group['start_assigned'] ? 'Startlocatie toegewezen' : 'Wacht op startlocatie' }}</td>
<td>{{ $group['progress_percent'] }}%</td><td>{{ $group['last_seen_at'] ?? '-' }}</td>
</tr>@endforeach</tbody></table>@endif
<p><a href="{{ route('admin.dashboard') }}">Overzicht verversen</a></p></div>
@endsection
