@extends('layouts.team')

@section('title', __('Edit assignment'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('teams.assignments.show', [$team, $assignment]) }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-600">
            ← {{ $assignment->title }}
        </a>
        <h1 class="mt-3 text-2xl font-bold tracking-tight text-zinc-900 sm:text-3xl">{{ __('Edit assignment') }}</h1>
    </div>

    <form method="POST" action="{{ route('teams.assignments.update', [$team, $assignment]) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            @include('teams.assignments._form')
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-zinc-200 pt-6">
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('Save changes') }}
            </button>
            <a href="{{ route('teams.assignments.show', [$team, $assignment]) }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900">
                {{ __('Discard') }}
            </a>
        </div>
    </form>
@endsection
