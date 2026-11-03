@extends('layouts.admin')

@section('content')
    <h1>Nieuw onderzoek</h1>

    <div class="card">
        <form method="POST" action="{{ route('admin.games.store') }}">
            @csrf

            <label>Naam onderzoek</label>
            <input type="text" name="name" value="{{ old('name') }}" required>

            <label>Slug</label>
            <input type="text" name="slug" value="{{ old('slug') }}">

            <label>Status</label>
            <select name="status" required>
                <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                <option value="live" @selected(old('status') === 'live')>Live</option>
                <option value="finished" @selected(old('status') === 'finished')>Finished</option>
            </select>

            <label>Start</label>
            <input type="datetime-local" name="start_at" value="{{ old('start_at') }}">

            <label>Einde</label>
            <input type="datetime-local" name="end_at" value="{{ old('end_at') }}">

            <hr style="border-color:var(--line); margin: 24px 0;">
            <h2 style="font-size: 18px;">Zaakdetails</h2>

            <label>Plaats delict / locatie incident</label>
            <input type="text" name="incident_location" value="{{ old('incident_location') }}">

            <label>Moment van incident</label>
            <input type="datetime-local" name="incident_at" value="{{ old('incident_at') }}">

            <label>Briefing voor groepen</label>
            <textarea name="case_briefing" rows="5">{{ old('case_briefing') }}</textarea>

            <button type="submit" class="btn">Opslaan</button>
        </form>
    </div>
@endsection
