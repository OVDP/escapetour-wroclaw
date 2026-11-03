@extends('layouts.admin')

@section('content')
    <h1>Berichten</h1>

    <div class="card" style="margin-bottom: 20px;">
        <form method="POST" action="{{ route('admin.messages.store') }}">
            @csrf

            <label>Doelgroep</label>
            <select name="audience" id="audience" required>
                <option value="all_active">Iedere actieve groep</option>
                <option value="group">Specifieke groep</option>
            </select>

            <div id="group-picker" style="display:none;">
                <label>Groep</label>
                <select name="game_route_id">
                    <option value="">Kies groep</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <label>Titel (optioneel)</label>
            <input type="text" name="title" value="{{ old('title') }}">

            <label>Bericht</label>
            <textarea name="message" rows="4" required>{{ old('message') }}</textarea>

            <label>Vervalt op (optioneel)</label>
            <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}">

            <button type="submit" class="btn">Verstuur bericht</button>
        </form>
    </div>

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
            <h2 style="margin:0;">Recente berichten</h2>
            @if($activeMessages->isNotEmpty())
                <form method="POST" action="{{ route('admin.messages.destroy-all') }}" onsubmit="return confirm('Weet je zeker dat je alle berichten wilt verwijderen?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Verwijder alle berichten</button>
                </form>
            @endif
        </div>

        @if($activeMessages->isEmpty())
            <p>Nog geen berichten verzonden.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th>Doelgroep</th>
                        <th>Bericht</th>
                        <th>Aangemaakt</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeMessages as $message)
                        <tr>
                            <td>{{ $message->title ?: 'Melding' }}</td>
                            <td>{{ $message->audience }}</td>
                            <td>{{ $message->message }}</td>
                            <td>{{ $message->created_at?->format('d-m-Y H:i:s') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Bericht verwijderen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Verwijder</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <script>
        (() => {
            const audience = document.getElementById('audience');
            const groupPicker = document.getElementById('group-picker');

            const render = () => {
                groupPicker.style.display = audience.value === 'group' ? 'block' : 'none';
            };

            audience.addEventListener('change', render);
            render();
        })();
    </script>
@endsection
