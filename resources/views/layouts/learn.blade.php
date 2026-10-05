{{-- Learner / public shell — Laracasts-style: focused learning, not admin CMS --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full {{ app()->getLocale() === 'my' ? 'locale-my' : '' }}">
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

    @include('partials.learn-header')

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
