@extends('layouts.admin')

@section('content')
    <h1>Wachtende goedkeuringen</h1>

    <div class="card">
        @if($pendingSubmissions->isEmpty())
            <p>Er zijn nu geen inzendingen die wachten op goedkeuring.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Groep</th>
                        <th>Onderzoek</th>
                        <th>Opdracht</th>
                        <th>Antwoord</th>
                        <th>Locatie</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingSubmissions as $submission)
                        <tr>
                            <td>{{ $submission->route?->name ?? '-' }}</td>
                            <td>{{ $submission->route?->game?->name ?? '-' }}</td>
                            <td>{{ $submission->questStep?->title ?? '-' }}</td>
                            <td>{{ $submission->submitted_answer ?: ($submission->submitted_choice ?: '-') }}
                            @if($submission->photo_path)
                                <p><a href="{{ route('admin.submissions.photo', $submission) }}" target="_blank" rel="noopener">Bekijk foto</a></p>
                            @endif
                            <small>{{ $submission->questStep?->review_notes }}</small>
                            </td>
                            <td>
                                @if($submission->submitted_latitude && $submission->submitted_longitude)
                                    {{ $submission->submitted_latitude }}, {{ $submission->submitted_longitude }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    @if($submission->route)
                                        <form method="POST" action="{{ route('admin.routes.submissions.approve', [$submission->route, $submission]) }}">
                                            @csrf
                                            <button type="submit" class="btn">Goedkeuren</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.routes.submissions.reject', [$submission->route, $submission]) }}">
                                            @csrf
                                            <button type="submit" class="btn-danger">Afkeuren</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2>Recent afgehandeld</h2>

        @if($recentReviewedSubmissions->isEmpty())
            <p>Nog geen afgehandelde inzendingen.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Groep</th>
                        <th>Opdracht</th>
                        <th>Antwoord</th>
                        <th>Afgehandeld op</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentReviewedSubmissions as $submission)
                        <tr>
                            <td>{{ $submission->status === 'approved' ? 'Goedgekeurd' : 'Afgekeurd' }}</td>
                            <td>{{ $submission->route?->name ?? '-' }}</td>
                            <td>{{ $submission->questStep?->title ?? '-' }}</td>
                            <td>{{ $submission->submitted_answer ?: ($submission->submitted_choice ?: '-') }}
                            @if($submission->photo_path)
                                <p><a href="{{ route('admin.submissions.photo', $submission) }}" target="_blank" rel="noopener">Bekijk foto</a></p>
                            @endif
                            <small>{{ $submission->questStep?->review_notes }}</small>
                            </td>
                            <td>{{ $submission->reviewed_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
