@extends('layouts.admin')

@section('content')
    <h1>Nieuwe opdracht</h1>

    <div class="card">
        <form method="POST" action="{{ route('admin.quest-steps.store') }}">
            @csrf

            @include('admin.quest-steps._form')

            <button type="submit" class="btn">Opslaan</button>
        </form>
    </div>
@endsection