@extends('layouts.learn')

@section('title', __('Teams'))

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-zinc-900">{{ __('My teams') }}</h1>
        <p class="mt-1 text-sm text-zinc-600">{{ __('Open a team to view assignments and members.') }}</p>
    </div>

    @if($teams->isEmpty())
        <div class="rounded-xl border border-zinc-200 bg-panel px-6 py-12 text-center">
            <p class="text-sm text-zinc-600">{{ __('You are not on any teams yet.') }}</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($teams as $team)
                @php
                    $tileColors = ['bg-emerald-600', 'bg-sky-600', 'bg-amber-600', 'bg-violet-600', 'bg-rose-600', 'bg-teal-600'];
                    $tile = $tileColors[($team->id - 1) % count($tileColors)];
                @endphp
                <a href="{{ route('teams.show', $team) }}"
                    class="block rounded-xl border border-zinc-200 bg-panel p-5 transition hover:border-zinc-300 hover:bg-rail">
                    <span class="{{ $tile }} flex h-12 w-12 items-center justify-center rounded-xl text-lg font-bold text-zinc-900">
                        {{ strtoupper(mb_substr($team->name, 0, 1)) }}
                    </span>
                    <h2 class="mt-4 truncate text-base font-semibold text-zinc-900">{{ $team->name }}</h2>
                    <p class="mt-1 text-xs text-zinc-500">
                        {{ trans_choice(':count member|:count members', $team->users_count, ['count' => $team->users_count]) }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
