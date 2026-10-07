@extends('layouts.team')

@section('title', $team->name.' — '.__('Assignments'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-700 text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </span>
            <h1 class="text-xl font-semibold text-zinc-900">{{ __('Assignments') }}</h1>
        </div>
        @if($canManageAssignments)
            <a href="{{ route('teams.assignments.create', $team) }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white hover:bg-emerald-500"
                title="{{ __('New assignment') }}" aria-label="{{ __('New assignment') }}">+</a>
        @endif
    </div>

    <div class="mb-6 flex flex-wrap items-center gap-4 border-b border-zinc-200" data-compact-my>
        @php
            $tabs = [
                'upcoming' => __('Upcoming'),
                'past_due' => __('Past due'),
                'completed' => __('Completed'),
            ];
        @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('teams.show', ['team' => $team, 'tab' => $key]) }}"
                class="relative pb-3 text-sm font-medium transition {{ $tab === $key ? 'text-zinc-900' : 'text-zinc-500 hover:text-zinc-700' }}">
                {{ $label }}
                @if(($counts[$key] ?? 0) > 0)
                    <span class="ml-1 text-xs text-zinc-500">({{ $counts[$key] }})</span>
                @endif
                @if($tab === $key)
                    <span class="absolute inset-x-0 -bottom-px h-0.5 bg-emerald-500"></span>
                @endif
            </a>
        @endforeach
    </div>

    @if($grouped->isEmpty())
        <div class="rounded-xl border border-zinc-200 bg-panel px-6 py-16 text-center">
            <p class="text-sm text-zinc-600">{{ __('No assignments yet.') }}</p>
            @if($canManageAssignments && $tab === 'upcoming')
                <a href="{{ route('teams.assignments.create', $team) }}" class="mt-3 inline-block text-sm text-emerald-700 hover:underline">
                    {{ __('Create the first assignment') }}
                </a>
            @endif
        </div>
    @else
        <div class="space-y-8">
            @foreach($grouped as $dateLabel => $items)
                <section>
                    <h2 class="mb-3 text-sm font-medium text-zinc-600">{{ $dateLabel }}</h2>
                    <ul class="space-y-3">
                        @foreach($items as $assignment)
                            @php
                                $own = $ownSubmissionMap[$assignment->id] ?? null;
                                $duePast = $assignment->due_at && $assignment->due_at->isPast();
                            @endphp
                            <li>
                                <a href="{{ route('teams.assignments.show', [$team, $assignment]) }}"
                                    class="block rounded-xl border border-zinc-200 bg-panel px-5 py-4 transition hover:border-zinc-300 hover:bg-rail">
                                    <p class="truncate text-base font-semibold text-zinc-900">{{ $assignment->title }}</p>
                                    <p class="mt-1 text-sm {{ $duePast && $assignment->isOpen() ? 'text-rose-600' : 'text-zinc-500' }}">
                                        @if($assignment->due_at)
                                            {{ __('Due at :time', ['time' => $assignment->due_at->timezone(config('app.timezone'))->format('g:i A')]) }}
                                        @else
                                            {{ __('No due date') }}
                                        @endif
                                        @if(auth()->user()->isStudent() && $own)
                                            ·
                                            @if($own->status->value === 'returned')
                                                {{ __('Returned') }}
                                            @elseif($own->status->value === 'submitted')
                                                {{ __('Turned in') }}
                                            @endif
                                        @elseif($assignment->isClosed())
                                            · {{ __('Closed') }}
                                        @endif
                                    </p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif
@endsection
