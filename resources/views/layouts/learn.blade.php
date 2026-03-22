{{-- Learner / public shell — Laracasts-style: focused learning, not admin CMS --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
</head>
<body class="min-h-full bg-zinc-950 text-zinc-100 antialiased">
    <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_120%_80%_at_50%_-20%,rgba(16,185,129,0.12),transparent)]"></div>

    <header class="sticky top-0 z-50 border-b border-zinc-800/80 bg-zinc-950/90 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <a href="{{ route('courses.index') }}" class="group flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-sm font-bold text-white shadow-lg shadow-emerald-900/40">P</span>
                    <span class="text-base font-semibold tracking-tight text-white group-hover:text-emerald-300">{{ config('app.name') }}</span>
                </a>
                <nav class="hidden items-center gap-1 md:flex">
                    <a href="{{ route('courses.index') }}"
                        class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('courses.index') || request()->routeIs('courses.show') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-white' }}">{{ __('Browse') }}</a>
                    <a href="{{ route('forums.index') }}"
                        class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('forums.*') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-white' }}">{{ __('Forum') }}</a>
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-white' }}">{{ __('Dashboard') }}</a>
                    @endauth
                </nav>
            </div>
            <nav class="flex flex-wrap items-center justify-end gap-2 sm:gap-3">
                @auth
                    <a href="{{ route('notifications.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white {{ request()->routeIs('notifications.*') ? 'bg-zinc-800 text-white' : '' }}">
                        {{ __('Notifications') }}
                        @if(($unreadNotificationCount ?? 0) > 0)
                            <span class="inline-flex min-h-[1.25rem] min-w-[1.25rem] items-center justify-center rounded-full bg-emerald-600 px-1 text-[10px] font-bold leading-none text-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                        @endif
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                            class="hidden rounded-md border border-amber-700/50 bg-amber-950/40 px-3 py-2 text-sm font-medium text-amber-200 hover:bg-amber-950/70 sm:inline-flex">{{ __('Admin panel') }}</a>
                    @endif
                    @if(auth()->user()->profile)
                        <a href="{{ route('profiles.show', auth()->user()->profile) }}"
                            class="rounded-md px-3 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white">{{ __('Profile') }}</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="rounded-md px-3 py-2 text-sm text-zinc-500 hover:text-white">{{ __('Log out') }}</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm text-zinc-400 hover:text-white">{{ __('Sign in') }}</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">{{ __('Join') }}</a>
                @endauth
            </nav>
        </div>
        {{-- Mobile nav --}}
        <div class="flex border-t border-zinc-800/80 px-4 py-2 md:hidden">
            <a href="{{ route('courses.index') }}" class="flex-1 rounded-md py-2 text-center text-xs font-medium {{ request()->routeIs('courses.*') ? 'bg-zinc-800 text-white' : 'text-zinc-400' }}">{{ __('Browse') }}</a>
            <a href="{{ route('forums.index') }}" class="flex-1 rounded-md py-2 text-center text-xs font-medium {{ request()->routeIs('forums.*') ? 'bg-zinc-800 text-white' : 'text-zinc-400' }}">{{ __('Forum') }}</a>
            @auth
                <a href="{{ route('dashboard') }}" class="flex-1 rounded-md py-2 text-center text-xs font-medium {{ request()->routeIs('dashboard') ? 'bg-zinc-800 text-white' : 'text-zinc-400' }}">{{ __('Home') }}</a>
            @endauth
        </div>
    </header>

    @if(session('status'))
        <div class="relative z-10 mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <p class="rounded-lg border border-emerald-800/60 bg-emerald-950/60 px-4 py-3 text-sm text-emerald-100 shadow-sm">{{ session('status') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="relative z-10 mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <p class="rounded-lg border border-amber-800/60 bg-amber-950/50 px-4 py-3 text-sm text-amber-100 shadow-sm">{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="relative z-10 mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <ul class="list-inside list-disc rounded-lg border border-red-900/60 bg-red-950/50 px-4 py-3 text-sm text-red-100">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <main class="relative z-10 mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <footer class="relative z-10 border-t border-zinc-800/60 py-8 text-center text-xs text-zinc-600">
        {{ config('app.name') }} — {{ __('Learn at your pace.') }}
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var h = location.hash;
            if (!h || h.length < 2) return;
            var id = decodeURIComponent(h.slice(1));
            var el = document.getElementById(id);
            if (!el) return;
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('mention-flash');
            setTimeout(function () { el.classList.remove('mention-flash'); }, 3000);
        });
    </script>
</body>
</html>
