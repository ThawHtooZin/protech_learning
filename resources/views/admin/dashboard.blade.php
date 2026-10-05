@extends('layouts.admin')

@section('title', __('Admin'))

@section('heading', __('Overview'))

@section('content')
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <p class="max-w-2xl text-sm text-zinc-400">{{ __('Counts, pending approvals, and recent activity across courses and teams.') }}</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.courses.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('New course') }}
            </a>
            <a href="{{ route('admin.users.index', ['tab' => 'teams']) }}" class="rounded-lg border border-zinc-700 px-4 py-2 text-sm font-semibold text-zinc-200 hover:bg-zinc-800">
                {{ __('Manage teams') }}
            </a>
        </div>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.courses.index') }}" class="rounded-xl border border-white/5 bg-panel p-5 transition hover:border-white/10">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Courses') }}</p>
            <p class="mt-2 text-3xl font-semibold text-white">{{ $courseCount }}</p>
            <p class="mt-1 text-xs text-zinc-500">{{ __(':count published', ['count' => $publishedCourseCount]) }}</p>
        </a>
        <a href="{{ route('admin.users.index', ['tab' => 'teams']) }}" class="rounded-xl border border-white/5 bg-panel p-5 transition hover:border-white/10">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Teams') }}</p>
            <p class="mt-2 text-3xl font-semibold text-white">{{ $teamCount }}</p>
            <p class="mt-1 text-xs text-zinc-500">{{ __('Organize instructors and students') }}</p>
        </a>
        <a href="{{ route('admin.users.index', ['tab' => 'users']) }}" class="rounded-xl border border-white/5 bg-panel p-5 transition hover:border-white/10">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Users') }}</p>
            <p class="mt-2 text-3xl font-semibold text-white">{{ $userCount }}</p>
            <p class="mt-1 text-xs text-zinc-500">{{ __('All accounts') }}</p>
        </a>
        <a href="{{ route('admin.users.index', ['tab' => 'users']) }}" class="rounded-xl border border-white/5 bg-panel p-5 transition hover:border-white/10">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Pending') }}</p>
            <p class="mt-2 text-3xl font-semibold {{ $pendingCount > 0 ? 'text-amber-300' : 'text-white' }}">{{ $pendingCount }}</p>
            <p class="mt-1 text-xs text-zinc-500">{{ __('Awaiting approval') }}</p>
        </a>
    </div>

    <section class="mb-8 rounded-xl border border-white/5 bg-panel">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
            <div>
                <h2 class="text-sm font-semibold text-white">{{ __('Needs attention') }}</h2>
                <p class="mt-0.5 text-xs text-zinc-500">{{ __('Users waiting for approval') }}</p>
            </div>
            @if($pendingCount > 0)
                <a href="{{ route('admin.users.index', ['tab' => 'users']) }}" class="text-sm text-emerald-400 hover:underline">{{ __('View all users') }} →</a>
            @endif
        </div>

        @if($pendingUsers->isEmpty())
            <p class="px-5 py-8 text-sm text-zinc-500">{{ __('No pending approvals. You’re caught up.') }}</p>
        @else
            <ul class="divide-y divide-zinc-800">
                @foreach($pendingUsers as $pending)
                    <li class="flex flex-wrap items-center gap-3 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-white">
                                {{ $pending->profile?->display_name ?? $pending->email }}
                            </p>
                            <p class="truncate text-xs text-zinc-500">
                                {{ $pending->email }}
                                · {{ $pending->role->label() }}
                                · {{ $pending->created_at?->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.users.show', $pending) }}" class="rounded-md border border-zinc-700 px-3 py-1.5 text-xs text-zinc-300 hover:bg-zinc-800">
                                {{ __('Manage') }}
                            </a>
                            <form method="POST" action="{{ route('admin.users.approve', $pending) }}">
                                @csrf
                                <button type="submit" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">
                                    {{ __('Approve') }}
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-white/5 bg-panel">
            <div class="flex items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
                <h2 class="text-sm font-semibold text-white">{{ __('Recent courses') }}</h2>
                <a href="{{ route('admin.courses.index') }}" class="text-sm text-emerald-400 hover:underline">{{ __('All courses') }} →</a>
            </div>
            @if($recentCourses->isEmpty())
                <p class="px-5 py-8 text-sm text-zinc-500">
                    {{ __('No courses yet.') }}
                    <a href="{{ route('admin.courses.create') }}" class="text-emerald-400 hover:underline">{{ __('Create one') }}</a>
                </p>
            @else
                <ul class="divide-y divide-zinc-800">
                    @foreach($recentCourses as $course)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-white">{{ $course->title }}</p>
                                <p class="text-xs text-zinc-500">
                                    @if($course->is_published)
                                        <span class="text-emerald-300">{{ __('Published') }}</span>
                                    @else
                                        <span class="text-zinc-400">{{ __('Draft') }}</span>
                                    @endif
                                    · {{ $course->updated_at?->diffForHumans() }}
                                </p>
                            </div>
                            <a href="{{ route('admin.courses.edit', $course) }}" class="rounded-md border border-zinc-700 px-3 py-1.5 text-xs text-zinc-300 hover:bg-zinc-800">
                                {{ __('Edit') }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded-xl border border-white/5 bg-panel">
            <div class="flex items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
                <h2 class="text-sm font-semibold text-white">{{ __('Recent activity') }}</h2>
                <a href="{{ route('admin.monitoring.index') }}" class="text-sm text-emerald-400 hover:underline">{{ __('Monitoring') }} →</a>
            </div>
            @if($recentActivity->isEmpty())
                <p class="px-5 py-8 text-sm text-zinc-500">{{ __('No learner activity logged yet.') }}</p>
            @else
                <ul class="divide-y divide-zinc-800">
                    @foreach($recentActivity as $event)
                        <li class="px-5 py-3">
                            <p class="text-sm text-zinc-200">
                                <span class="font-medium text-white">{{ $event->user_label }}</span>
                                <span class="text-zinc-500">·</span>
                                <span class="font-mono text-xs text-zinc-400">{{ $event->event_type }}</span>
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                @if($event->course_title)
                                    {{ $event->course_title }} ·
                                @endif
                                {{ $event->occurred_at?->diffForHumans() }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
