<header class="sticky top-0 z-50 border-b border-zinc-200 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <a href="{{ route('courses.index') }}" class="group flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-sm font-bold text-white shadow-lg shadow-emerald-200/60">P</span>
                <span class="text-base font-semibold tracking-tight text-zinc-900 group-hover:text-emerald-700">{{ config('app.name') }}</span>
            </a>
            <nav class="hidden items-center gap-1 md:flex" data-compact-my>
                <a href="{{ route('courses.index') }}"
                    class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('courses.index') || request()->routeIs('courses.show') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">{{ __('Browse') }}</a>
                <a href="{{ route('forums.index') }}"
                    class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('forums.*') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">{{ __('Forum') }}</a>
                @auth
                    <a href="{{ route('teams.index') }}"
                        class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('teams.*') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">{{ __('Teams') }}</a>
                    <a href="{{ route('dashboard') }}"
                        class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">{{ __('Dashboard') }}</a>
                @endauth
            </nav>
        </div>
        <nav class="flex flex-wrap items-center justify-end gap-2 sm:gap-3" data-compact-my>
            @include('partials.locale-switcher')
            @auth
                <a href="{{ route('notifications.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 {{ request()->routeIs('notifications.*') ? 'bg-zinc-100 text-zinc-900' : '' }}">
                    {{ __('Notifications') }}
                    @if(($unreadNotificationCount ?? 0) > 0)
                        <span class="inline-flex min-h-[1.25rem] min-w-[1.25rem] items-center justify-center rounded-full bg-emerald-600 px-1 text-[10px] font-bold leading-none text-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                    @endif
                </a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                        class="hidden rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100 sm:inline-flex">{{ __('Admin panel') }}</a>
                @endif
                @if(auth()->user()->profile)
                    <a href="{{ route('profiles.show', auth()->user()->profile) }}"
                        class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900">
                        @if(auth()->user()->profile->avatarUrl())
                            <img src="{{ auth()->user()->profile->avatarUrl() }}" alt="" class="h-6 w-6 rounded-full object-cover">
                        @endif
                        {{ __('Profile') }}
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-md px-3 py-2 text-sm text-zinc-500 hover:text-zinc-900">{{ __('Log out') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm text-zinc-600 hover:text-zinc-900">{{ __('Sign in') }}</a>
                <a href="{{ route('register') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">{{ __('Join') }}</a>
            @endauth
        </nav>
    </div>
    {{-- Mobile nav --}}
    <div class="flex border-t border-zinc-200 px-4 py-2 md:hidden" data-compact-my>
        <a href="{{ route('courses.index') }}" class="flex-1 rounded-md py-2 text-center text-xs font-medium {{ request()->routeIs('courses.*') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600' }}">{{ __('Browse') }}</a>
        <a href="{{ route('forums.index') }}" class="flex-1 rounded-md py-2 text-center text-xs font-medium {{ request()->routeIs('forums.*') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600' }}">{{ __('Forum') }}</a>
        @auth
            <a href="{{ route('teams.index') }}" class="flex-1 rounded-md py-2 text-center text-xs font-medium {{ request()->routeIs('teams.*') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600' }}">{{ __('Teams') }}</a>
            <a href="{{ route('dashboard') }}" class="flex-1 rounded-md py-2 text-center text-xs font-medium {{ request()->routeIs('dashboard') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600' }}">{{ __('Home') }}</a>
        @endauth
    </div>
</header>
