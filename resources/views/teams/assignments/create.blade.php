@extends('layouts.team')

@section('title', __('New assignment'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900 sm:text-3xl">{{ __('New assignment') }}</h1>
        <p class="mt-1.5 text-sm text-zinc-600">{{ __('Students on this team will see this assignment and can upload files.') }}</p>
    </div>

    <form method="POST" action="{{ route('teams.assignments.store', $team) }}" enctype="multipart/form-data">
        @csrf

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            @include('teams.assignments._form')
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-zinc-200 pt-6">
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('Assign') }}
            </button>
            <a href="{{ route('teams.show', $team) }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900">
                {{ __('Discard') }}
            </a>
        </div>
    </form>
@endsection
