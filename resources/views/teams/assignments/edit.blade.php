@extends('layouts.team')

@section('title', __('Edit assignment'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('teams.assignments.show', [$team, $assignment]) }}" class="text-sm text-emerald-400 hover:underline">
            ← {{ $assignment->title }}
        </a>
    </div>

    <h1 class="text-2xl font-semibold text-white">{{ __('Edit assignment') }}</h1>

    <form method="POST" action="{{ route('teams.assignments.update', [$team, $assignment]) }}" enctype="multipart/form-data"
        class="mt-8 max-w-2xl space-y-5 rounded-xl border border-white/5 bg-panel p-6">
        @csrf
        @method('PUT')
        @include('teams.assignments._form')

        @if($assignment->attachments->isNotEmpty())
            <div>
                <p class="text-sm font-medium text-zinc-300">{{ __('Existing resources') }}</p>
                <ul class="mt-2 space-y-1 text-sm text-zinc-400">
                    @foreach($assignment->attachments as $attachment)
                        <li>{{ $attachment->original_name }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
            {{ __('Save changes') }}
        </button>
    </form>
@endsection
