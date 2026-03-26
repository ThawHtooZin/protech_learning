@extends('layouts.learn')

@section('title', $course->title)

@section('content')
    <div class="mb-10 border-b border-zinc-800 pb-8">
        <a href="{{ route('courses.index') }}" class="text-sm text-zinc-500 hover:text-emerald-400">{{ __('Library') }}</a>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-white">{{ $course->title }}</h1>
        @if($course->description)
            <p class="mt-3 max-w-3xl text-lg text-zinc-400">{{ $course->description }}</p>
        @endif
        <div class="mt-6 flex flex-wrap items-center gap-4">
            @auth
                @if($enrolled)
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-950/80 px-4 py-1.5 text-sm text-emerald-300 ring-1 ring-emerald-800/50">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        {{ __('Enrolled') }} · {{ $completion }}% {{ __('complete') }}
                        @if($completion === 100 && $accuracyPercent !== null)
                            <span class="text-zinc-400">·</span>
                            <span title="{{ __('Correct answers / total answers across all quizzes in this course') }}">{{ __('Accuracy') }}: {{ $accuracyPercent }}%</span>
                        @endif
                    </span>
                @elseif(!empty($adminFullAccess))
                    <span class="inline-flex items-center gap-2 rounded-full bg-zinc-800/80 px-4 py-1.5 text-sm text-zinc-200 ring-1 ring-zinc-600/50">
                        {{ __('Staff') }} · {{ __('full access to lessons and quizzes') }}
                    </span>
                @else
                    <p class="text-zinc-500">
                        {{ __('You don’t have access to this course yet. Ask an admin to assign it to your account.') }}
                    </p>
                @endif
            @else
                <p class="text-zinc-500">
                    <a href="{{ route('login') }}" class="font-medium text-emerald-400 hover:underline">{{ __('Sign in') }}</a>
                    {{ __('to enroll and track progress.') }}
                </p>
            @endauth
        </div>
    </div>

    @auth
        @if(!empty($showCourseContent))
            @foreach($course->modules as $module)
                <section class="mb-10">
                    <h2 class="mb-4 text-xs font-bold uppercase tracking-widest text-zinc-500">{{ $module->title }}</h2>
                    <div class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/40">
                        @foreach($module->lessons as $lesson)
                            @php $canOpen = isset($accessibleLessonIds) && $accessibleLessonIds->contains($lesson->id); @endphp
                            @if($canOpen)
                            <a href="{{ route('lessons.show', $lesson) }}" class="flex items-center gap-4 border-b border-zinc-800/80 px-4 py-4 last:border-0 hover:bg-zinc-800/40 sm:px-5">
                            @else
                            <div class="flex items-center gap-4 border-b border-zinc-800/80 px-4 py-4 last:border-0 opacity-50 sm:px-5" title="{{ __('Complete previous lessons and quizzes in order to unlock.') }}">
                            @endif
                                @if(isset($completedLessonIds) && $completedLessonIds->contains($lesson->id))
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-emerald-700/60 bg-emerald-950/50 text-emerald-400" title="{{ __('Completed') }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                @else
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-zinc-700 bg-zinc-800 text-zinc-400">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-white">{{ $lesson->title }}</p>
                                    @if($lesson->duration_seconds)
                                        <p class="text-xs text-zinc-500">{{ (int) ceil($lesson->duration_seconds / 60) }} {{ __('min') }}</p>
                                    @endif
                                </div>
                                @if($canOpen)
                                <svg class="h-5 w-5 shrink-0 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                @else
                                <span class="shrink-0 text-xs font-semibold uppercase text-zinc-600">{{ __('Locked') }}</span>
                                @endif
                            @if($canOpen)
                            </a>
                            @else
                            </div>
                            @endif
                        @endforeach
                        @php
                            $modQuiz = $module->quizzes->first(fn ($q) => $q->lesson_id === null);
                        @endphp
                        @if($modQuiz)
                            <a href="{{ route('quizzes.show', $modQuiz) }}" class="flex items-center gap-4 border-t border-amber-900/40 bg-amber-950/20 px-4 py-4 hover:bg-amber-950/35 sm:px-5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-amber-700/50 bg-amber-950 text-amber-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-amber-200">{{ __('Module quiz') }}</p>
                                    <p class="text-xs text-amber-500/80">{{ $modQuiz->title }}</p>
                                </div>
                            </a>
                        @endif
                    </div>
                </section>
            @endforeach
        @endif
    @endauth
@endsection
