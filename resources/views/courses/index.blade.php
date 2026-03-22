@extends('layouts.learn')

@section('title', __('Browse'))

@section('content')
    <div class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight text-white">{{ __('Library') }}</h1>
        <p class="mt-2 max-w-2xl text-zinc-400">{{ __('Pick a course and start learning. Your progress is saved automatically.') }}</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($courses as $course)
            <a href="{{ route('courses.show', $course) }}" class="group flex flex-col rounded-2xl border border-zinc-800/80 bg-zinc-900/40 p-6 shadow-sm transition hover:border-emerald-800/60 hover:bg-zinc-900/80">
                <div class="flex items-start justify-between gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-600/90 to-teal-700 text-sm font-bold text-white">{{ \Illuminate\Support\Str::substr($course->title, 0, 1) }}</span>
                    @auth
                        @if(in_array($course->id, $enrolledIds, true))
                            <span class="rounded-full bg-emerald-950 px-2.5 py-0.5 text-xs font-medium text-emerald-400 ring-1 ring-emerald-800/50">{{ __('Enrolled') }}</span>
                        @endif
                    @endauth
                </div>
                <h2 class="mt-4 text-lg font-semibold text-white group-hover:text-emerald-300">{{ $course->title }}</h2>
                @if($course->description)
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-zinc-500">{{ \Illuminate\Support\Str::limit($course->description, 140) }}</p>
                @endif
                <span class="mt-4 inline-flex items-center text-sm font-medium text-emerald-500 group-hover:text-emerald-400">
                    {{ __('View course') }}
                    <svg class="ml-1 h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            </a>
        @empty
            <p class="col-span-full text-zinc-500">{{ __('No courses published yet.') }}</p>
        @endforelse
    </div>
@endsection
