{{-- Full-bleed team workspace under the global learn header --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full {{ app()->getLocale() === 'my' ? 'locale-my' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $team->name) — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col bg-canvas text-zinc-900 antialiased">
    @include('partials.learn-header')

    @if(session('status'))
        <div class="border-b border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800 sm:px-6">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="border-b border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800 sm:px-6">
            <ul class="list-inside list-disc">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="flex min-h-0 flex-1 flex-col md:flex-row">
        @include('teams._sidebar')
        <main class="min-w-0 flex-1 bg-canvas px-4 py-6 sm:px-6 lg:px-8">
            @yield('content')
        </main>
    </div>
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
