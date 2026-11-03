@extends('layouts.admin')

@section('content')
    <h1>Opdracht bewerken</h1>

    <div class="card">
        <form method="POST" action="{{ route('admin.quest-steps.update', $quest_step) }}">
            @csrf
            @method('PUT')

            @include('admin.quest-steps._form')

            <button type="submit" class="btn">Opslaan</button>
        </form>
    </div>
@endsection