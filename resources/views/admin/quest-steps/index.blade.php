@extends('layouts.admin')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <h1>Opdrachten</h1>
        <a href="{{ route('admin.quest-steps.create') }}" class="btn">Nieuwe opdracht</a>
    </div>

    <div class="card">
        @if($questSteps->isEmpty())
            <p>Er zijn nog geen opdrachten aangemaakt.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th>Type</th>
                        <th>Onderzoek</th>
                        <th>Gekoppelde groepen</th>
                        <th>Controle</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($questSteps as $step)
                        <tr>
                            <td>{{ $step->title }}</td>
                            <td>{{ $step->step_type }}</td>
                            <td>{{ $step->game?->name ?? '-' }}</td>
                            <td>{{ $step->routes_count }}</td>
                            <td>{{ $step->requiresManualApproval() ? 'Handmatig (override)' : 'Automatisch' }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn-secondary" href="{{ route('admin.quest-steps.show', $step) }}">Bekijk</a>
                                    <a class="btn-secondary" href="{{ route('admin.quest-steps.edit', $step) }}">Bewerk</a>

                                    <form method="POST" action="{{ route('admin.quest-steps.destroy', $step) }}" onsubmit="return confirm('Weet je het zeker?');">
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
