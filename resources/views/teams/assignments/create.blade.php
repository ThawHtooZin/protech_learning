@extends('layouts.team')

@section('title', __('New assignment'))

@section('content')
    <h1 class="text-2xl font-semibold text-white">{{ __('New assignment') }}</h1>
    <p class="mt-1 text-sm text-zinc-400">{{ __('Students on this team will see this assignment and can upload files.') }}</p>

    <form method="POST" action="{{ route('teams.assignments.store', $team) }}" enctype="multipart/form-data"
        class="mt-8 max-w-2xl space-y-5 rounded-xl border border-white/5 bg-panel p-6">
        @csrf
        @include('teams.assignments._form')
        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
            {{ __('Create assignment') }}
        </button>
    </form>
@endsection
