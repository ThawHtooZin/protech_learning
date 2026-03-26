<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('admin.monitoring.index') }}"
        class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.monitoring.index') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
        {{ __('All activity') }}
    </a>
    <a href="{{ route('admin.monitoring.lessons') }}"
        class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.monitoring.lessons') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
        {{ __('Lessons') }}
    </a>
    <a href="{{ route('admin.monitoring.quizzes') }}"
        class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.monitoring.quizzes') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
        {{ __('Quizzes') }}
    </a>
    <a href="{{ route('admin.monitoring.forums') }}"
        class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.monitoring.forums') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
        {{ __('Forums') }}
    </a>
    <a href="{{ route('admin.monitoring.courses') }}"
        class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.monitoring.courses') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}">
        {{ __('Courses') }}
    </a>
</div>

