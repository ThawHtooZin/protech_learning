{{-- Admin CMS shell — sidebar navigation, separate from learner UI --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Admin')) — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-zinc-950 text-zinc-100 antialiased">
    <div class="flex min-h-screen flex-col md:flex-row">
        {{-- Sidebar --}}
        <aside class="flex w-full shrink-0 flex-col border-b border-zinc-800 bg-zinc-950 md:w-64 md:border-b-0 md:border-r">
            <div class="border-b border-zinc-800 px-4 py-5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-600 text-xs font-bold text-zinc-950">A</span>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ __('Admin') }}</p>
                        <p class="text-xs text-zinc-500">{{ __('Content & settings') }}</p>
                    </div>
                </a>
            </div>
            <nav class="flex max-h-[50vh] flex-1 flex-col gap-0.5 overflow-y-auto p-3 md:max-h-none">
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">{{ __('Manage') }}</p>
                @php
                    $adminContent = request()->routeIs('admin.dashboard', 'admin.courses.*', 'admin.modules.*', 'admin.lessons.*', 'admin.quizzes.*');
                @endphp
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    {{ __('Overview') }}
                </a>
                <a href="{{ route('admin.courses.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.courses.index') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    {{ __('Courses') }}
                </a>
                <a href="{{ route('admin.courses.create') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.courses.create') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('New course') }}
                </a>
                <a href="{{ route('admin.questions.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.questions.*') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('Question bank') }}
                </a>
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H2v-2a4 4 0 014-4h5m4-6a4 4 0 11-8 0 4 4 0 018 0zm6 2a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ __('Users') }}
                </a>
                <a href="{{ route('admin.monitoring.lessons') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.monitoring.*') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-4M4 19h16"/></svg>
                    {{ __('Monitoring') }}
                </a>
                <p class="mb-2 mt-4 px-3 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">{{ __('Community') }}</p>
                <a href="{{ route('admin.forums.categories') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.forums.categories') || request()->routeIs('admin.forums.categories.*') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    {{ __('Forum categories') }}
                </a>
                <a href="{{ route('admin.forums.tags') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.forums.tags') || request()->routeIs('admin.forums.tags.*') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    {{ __('Tags') }}
                </a>
            </nav>
            <div class="border-t border-zinc-800 p-3">
                <a href="{{ route('courses.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-emerald-400 hover:bg-zinc-900 hover:text-emerald-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    {{ __('Back to learning site') }}
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm text-zinc-500 hover:bg-zinc-900 hover:text-white">{{ __('Log out') }}</button>
                </form>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between border-b border-zinc-800 bg-zinc-900/50 px-6 py-4">
                <h1 class="truncate text-lg font-semibold text-white">@yield('heading', __('Admin'))</h1>
                <div class="flex items-center gap-3 text-sm text-zinc-400">
                    <span>{{ auth()->user()->name }}</span>
                </div>
            </header>
            <div class="flex-1 overflow-auto p-6 lg:p-8">
                @if(session('status'))
                    <p class="mb-6 rounded-lg border border-emerald-800/60 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</p>
                @endif
                @if($errors->any())
                    <ul class="mb-6 list-inside list-disc rounded-lg border border-red-900/60 bg-red-950/40 px-4 py-3 text-sm text-red-100">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
