@extends('layouts.admin')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <h1>Onderzoeken</h1>
        <a href="{{ route('admin.games.create') }}" class="btn">Nieuw onderzoek</a>
    </div>

    <div class="card">
        @if($games->isEmpty())
            <p>Er zijn nog geen onderzoeken aangemaakt.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Start</th>
                        <th>Einde</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($games as $game)
                        <tr>
                            <td>{{ $game->name }}</td>
                            <td>{{ $game->slug }}</td>
                            <td>{{ $game->status }}</td>
                            <td>{{ $game->start_at }}</td>
                            <td>{{ $game->end_at }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn-secondary" href="{{ route('admin.games.show', $game) }}">Bekijk</a>
                                    <a class="btn-secondary" href="{{ route('admin.games.edit', $game) }}">Bewerk</a>

                                    <form method="POST" action="{{ route('admin.games.destroy', $game) }}" onsubmit="return confirm('Weet je het zeker?');">
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
