@extends('layouts.admin')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <h1>Groepen</h1>
        <div class="actions">
            <form method="POST" action="{{ route('admin.routes.regenerate-all-tokens') }}" onsubmit="return confirm('Weet je zeker dat je ALLE groepstokens wilt vernieuwen?');">
                @csrf
                <button type="submit" class="btn-secondary">Vernieuw alle tokens</button>
            </form>
            <a href="{{ route('admin.routes.create') }}" class="btn">Nieuwe groep</a>
        </div>
    </div>

    <div class="card">
        @if($routes->isEmpty())
            <p>Er zijn nog geen groepen aangemaakt.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Onderzoek</th>
                        <th>Opdrachten</th>
                        <th>Voortgang</th>
                        <th>Token</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routes as $route)
                        <tr>
                            <td>{{ $route->name }} @if(!$route->start_assigned)<br><strong>Wacht op startlocatie</strong>@endif</td>
                            <td>{{ $route->game?->name ?? '-' }}</td>
                            <td>{{ $route->quest_steps_count }}</td>
                            <td>{{ $route->progress_percent ?? 0 }}%</td>
                            <td><code>{{ $route->join_token ?? '-' }}</code></td>
                            <td>
                                <div class="actions">
                                    <a class="btn-secondary" href="{{ route('admin.routes.qr', $route) }}" target="_blank" rel="noopener noreferrer">QR</a>
                                    <a class="btn-secondary" href="{{ route('admin.routes.edit', $route) }}">Bewerk</a>

                                    <form method="POST" action="{{ route('admin.routes.destroy', $route) }}" onsubmit="return confirm('Weet je het zeker?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Verwijder</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
