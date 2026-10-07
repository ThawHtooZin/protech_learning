<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('admin.monitoring.index') }}"
        class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.monitoring.index') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
        {{ __('All activity') }}
    </a>
    <a href="{{ route('admin.monitoring.lessons') }}"
        class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.monitoring.lessons') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
        {{ __('Lessons') }}
    </a>
    <a href="{{ route('admin.monitoring.forums') }}"
        class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.monitoring.forums') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
        {{ __('Forums') }}
    </a>
    <a href="{{ route('admin.monitoring.courses') }}"
        class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.monitoring.courses') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
        {{ __('Courses') }}
    </a>
</div>
