{{-- Team workspace chrome: Microsoft Teams–style left rail --}}
@php
    $tileColors = ['bg-emerald-600', 'bg-sky-600', 'bg-amber-600', 'bg-violet-600', 'bg-rose-600', 'bg-teal-600'];
    $tile = $tileColors[(($team->id ?? 1) - 1) % count($tileColors)];
    $teamNav = $teamNav ?? 'assignments';
@endphp

<aside class="flex w-full shrink-0 flex-col self-stretch border-b border-white/5 bg-rail md:w-64 md:border-b-0 md:border-r" data-compact-my>
    <div class="border-b border-white/5 px-3 py-3">
        <a href="{{ route('teams.index') }}" class="inline-flex items-center gap-1 text-xs font-medium text-zinc-400 hover:text-white">
            ← {{ __('All teams') }}
        </a>
        <div class="mt-3 flex items-start gap-2.5">
            <span class="{{ $tile }} flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-sm font-bold text-white">
                {{ strtoupper(mb_substr($team->name, 0, 1)) }}
            </span>
            <div class="min-w-0 pt-0.5">
                <p class="truncate text-sm font-semibold text-white" title="{{ $team->name }}">{{ $team->name }}</p>
            </div>
        </div>
    </div>

    <nav class="flex flex-1 flex-col gap-0.5 p-2">
        <a href="{{ route('teams.show', $team) }}"
            class="rounded-md px-3 py-2 text-sm font-medium transition {{ $teamNav === 'assignments' ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/70 hover:text-white' }}">
            {{ __('Assignments') }}
        </a>

        <p class="mb-1 mt-4 px-3 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">{{ __('Channels') }}</p>
        <a href="{{ route('teams.general', $team) }}"
            class="rounded-md px-3 py-2 text-sm font-medium transition {{ $teamNav === 'general' ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/70 hover:text-white' }}">
            {{ __('General') }}
        </a>
    </nav>
</aside>
