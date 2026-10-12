{{-- Team workspace chrome: Microsoft Teams–style left rail --}}
@php
    $tileColors = ['bg-emerald-600', 'bg-sky-600', 'bg-amber-600', 'bg-violet-600', 'bg-rose-600', 'bg-teal-600'];
    $tile = $tileColors[(($team->id ?? 1) - 1) % count($tileColors)];
    $teamNav = $teamNav ?? 'assignments';
    $sidebarChannels = $sidebarChannels ?? collect();
    $canManageTeam = $canManageTeam ?? false;
@endphp

<aside class="flex w-full shrink-0 flex-col self-stretch border-b border-zinc-200 bg-rail md:w-64 md:border-b-0 md:border-r" data-compact-my>
    <div class="border-b border-zinc-200 px-3 py-3">
        <div class="flex items-center justify-between gap-2">
            <a href="{{ route('teams.index') }}" class="inline-flex items-center gap-1 text-xs font-medium text-zinc-600 hover:text-zinc-900">
                ← {{ __('All teams') }}
            </a>

            @if($canManageTeam)
                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button type="button" @click="open = !open"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 {{ $teamNav === 'manage' ? 'bg-zinc-100 text-zinc-900' : '' }}"
                        aria-label="{{ __('More team options') }}"
                        aria-haspopup="menu"
                        :aria-expanded="open.toString()">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                        class="absolute right-0 z-30 mt-1 w-44 overflow-hidden rounded-lg border border-zinc-200 bg-white py-1 shadow-lg"
                        role="menu">
                        <a href="{{ route('teams.manage', $team) }}" role="menuitem"
                            class="block px-3 py-2 text-sm text-zinc-800 hover:bg-zinc-100 {{ $teamNav === 'manage' ? 'bg-zinc-50 font-medium' : '' }}">
                            {{ __('Manage team') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-3 flex items-start gap-2.5">
            <span class="{{ $tile }} flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-sm font-bold text-white">
                {{ strtoupper(mb_substr($team->name, 0, 1)) }}
            </span>
            <div class="min-w-0 pt-0.5">
                <p class="truncate text-sm font-semibold text-zinc-900" title="{{ $team->name }}">{{ $team->name }}</p>
            </div>
        </div>
    </div>

    <nav class="flex flex-1 flex-col gap-0.5 p-2">
        <a href="{{ route('teams.show', $team) }}"
            class="rounded-md px-3 py-2 text-sm font-medium transition {{ $teamNav === 'assignments' ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
            {{ __('Assignments') }}
        </a>

        <p class="mb-1 mt-4 px-3 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">{{ __('Channels') }}</p>
        @foreach($sidebarChannels as $navChannel)
            <a href="{{ route('teams.channels.show', [$team, $navChannel]) }}"
                class="rounded-md px-3 py-2 text-sm font-medium transition {{ $teamNav === 'channel-'.$navChannel->id ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                @if($navChannel->is_general)
                    <span class="mr-1 inline-block text-zinc-400" aria-hidden="true">⌂</span>
                @endif
                {{ $navChannel->name }}
            </a>
        @endforeach
    </nav>
</aside>
