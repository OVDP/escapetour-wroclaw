@extends('layouts.admin')

@section('content')
    <h1>Nieuwe groep</h1>

    <div class="card">
        <form method="POST" action="{{ route('admin.routes.store') }}">
            @csrf

            <label>Groepsnaam</label>
            <input type="text" name="name" value="{{ old('name') }}" required>

            <button type="submit" class="btn">Opslaan</button>
        </form>
    </div>
@endsection
