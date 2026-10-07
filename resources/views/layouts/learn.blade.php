{{-- Learner / public shell — Laracasts-style: focused learning, not admin CMS --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full {{ app()->getLocale() === 'my' ? 'locale-my' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="min-h-full bg-zinc-50 text-zinc-900 antialiased">
    <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_120%_80%_at_50%_-20%,rgba(16,185,129,0.08),transparent)]"></div>

    @include('partials.learn-header')

    @if(session('status'))
        <div id="protech-flash" data-message="{{ session('status') }}" data-type="success" class="hidden"></div>
    @endif
    @if(session('error'))
        <div id="protech-flash-error" class="hidden" data-message="{{ session('error') }}" data-type="warning"></div>
        <script>document.addEventListener('DOMContentLoaded',()=>{const e=document.getElementById('protech-flash-error');if(e&&window.ProtechAlert)ProtechAlert.toast(e.dataset.message,'warning');});</script>
    @endif

    @if($errors->any())
        <div class="relative z-10 mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <ul class="list-inside list-disc rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <main class="relative z-10 mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <footer class="relative z-10 border-t border-zinc-200 py-8 text-center text-xs text-zinc-600">
        {{ config('app.name') }} — {{ __('Learn at your pace.') }}
    </footer>

    @if(!empty($notiCatchup))
        @php
            $catchupData = $notiCatchup->data ?? [];
            $catchupMsg = $catchupData['message'] ?? __('You have an unread notification.');
            $catchupUrl = $catchupData['url'] ?? route('notifications.index');
        @endphp
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (!window.ProtechAlert) return;
                ProtechAlert.toast(@json($catchupMsg), 'info', 7000);
                var bar = document.createElement('a');
                bar.href = @json($catchupUrl);
                bar.className = 'fixed bottom-4 left-1/2 z-[90] max-w-sm -translate-x-1/2 rounded-full border border-emerald-200 bg-white px-4 py-2 text-sm font-medium text-emerald-800 shadow-lg shadow-zinc-900/10 ring-1 ring-black/5 hover:bg-emerald-50';
                bar.textContent = @json(__('View notification'));
                document.body.appendChild(bar);
                setTimeout(function () { bar.remove(); }, 9000);
            });
        </script>
    @endif

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
