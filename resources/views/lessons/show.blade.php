@extends('layouts.learn')

@section('title', $lesson->title)

@section('content')
    <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('courses.index') }}" class="hover:text-emerald-400">{{ __('Library') }}</a>
        <span class="text-zinc-700">/</span>
        <a href="{{ route('courses.show', $course) }}" class="hover:text-emerald-400">{{ $course->title }}</a>
        <span class="text-zinc-700">/</span>
        <span class="text-zinc-300">{{ $lesson->title }}</span>
    </nav>
    <div class="lg:grid lg:grid-cols-[minmax(0,260px)_1fr] lg:gap-10">
        <aside class="mb-8 lg:mb-0 lg:sticky lg:top-24 lg:self-start">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Series') }}</p>
            <p class="mt-1 text-sm font-medium text-white">{{ $course->title }}</p>
            <nav class="mt-4 max-h-[calc(100vh-12rem)] space-y-4 overflow-y-auto text-sm">
                @foreach($course->modules as $module)
                    <div>
                        <div class="flex flex-wrap items-center gap-2 text-xs font-medium uppercase tracking-wide text-zinc-600">
                            <span>{{ $module->title }}</span>
                            @foreach($module->quizzes as $mq)
                                <a href="{{ route('quizzes.show', $mq) }}" class="normal-case font-semibold tracking-normal text-amber-500/90 hover:text-amber-400" title="{{ __('Module recap quiz') }}">{{ __('Recap') }}</a>
                            @endforeach
                        </div>
                        <ul class="mt-2 space-y-1 border-l border-zinc-800 pl-3">
                            @foreach($module->lessons as $navLesson)
                                <li>
                                    <a href="{{ route('lessons.show', $navLesson) }}"
                                        class="flex items-start gap-2 py-0.5 {{ $navLesson->id === $lesson->id ? 'font-medium text-emerald-400' : 'text-zinc-400 hover:text-white' }}">
                                        @if($completedLessonIds->contains($navLesson->id))
                                            <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded border border-emerald-600 bg-emerald-950 text-[10px] text-emerald-400" title="{{ __('Completed') }}">✓</span>
                                        @else
                                            <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 rounded border border-zinc-700"></span>
                                        @endif
                                        <span class="min-w-0 flex-1">{{ $navLesson->title }}</span>
                                        @if($navLesson->quizzes->isNotEmpty())
                                            <span class="mt-0.5 shrink-0 text-[10px] font-bold text-amber-500/90" title="{{ __('Has a lesson quiz') }}">Q</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </aside>
        <div class="min-w-0">
            <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $lesson->title }}</h1>

            @if(! $recordProgress)
                <div class="mt-4 rounded-lg border border-amber-800/60 bg-amber-950/40 px-4 py-3 text-sm text-amber-100">
                    <p class="font-medium">{{ __('You opened this lesson before completing earlier steps.') }}</p>
                    <p class="mt-1 text-amber-200/90">{{ __('Complete earlier lessons in order to use “Mark as complete”, lesson quizzes, and course progress. You can still watch, read, and discuss below.') }}</p>
                </div>
            @endif

            <div class="mt-6 overflow-hidden rounded-xl border border-zinc-800 bg-black shadow-2xl shadow-black/50">
                @if($playable->kind === 'embed')
                    {!! $playable->embedHtml !!}
                @else
                    <video src="{{ $playable->signedUrl }}" controls class="w-full" playsinline></video>
                @endif
            </div>

            @if($playable->externalWatchUrl)
                <p class="mt-3 text-sm text-zinc-500">
                    <a href="{{ $playable->externalWatchUrl }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-400 hover:underline">{{ __('Open video on YouTube') }}</a>
                    <span class="text-zinc-600"> — {{ __('Use this if the embedded player asks you to sign in or shows an error (common on localhost or some networks).') }}</span>
                </p>
            @endif

            @if($recordProgress)
                <form method="POST" action="{{ route('lessons.complete', $lesson) }}" class="mt-6">
                    @csrf
                    <input type="hidden" name="completed" id="lesson-completed-value" value="{{ $progress->watched ? '1' : '0' }}">
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-700 bg-zinc-900/60 px-4 py-4 transition hover:border-zinc-600">
                        <input type="checkbox"
                            class="mt-0.5 h-5 w-5 shrink-0 rounded border-zinc-600 bg-zinc-900 text-emerald-600 focus:ring-emerald-600"
                            @checked($progress->watched)
                            onchange="document.getElementById('lesson-completed-value').value = this.checked ? '1' : '0'; this.form.submit();">
                        <span>
                            <span class="block text-sm font-semibold text-white">{{ __('Mark as complete') }}</span>
                            <span class="mt-0.5 block text-xs text-zinc-500">{{ __('Check this when you have finished the video and notes for this lesson (like Udemy).') }}</span>
                        </span>
                    </label>
                </form>
                @if($recordProgress && $nextLesson)
                    <p class="mt-2 text-xs text-zinc-500">{{ __('When you mark this lesson complete, you’ll jump to the next lesson automatically.') }}</p>
                @elseif($recordProgress && ! $nextLesson)
                    <p class="mt-2 text-xs text-zinc-500">{{ __('This is the last lesson in the course — you’ll stay here after marking complete.') }}</p>
                @endif
            @endif

            <section class="mt-8 rounded-xl border border-zinc-800 bg-zinc-900/40 p-5" aria-labelledby="lesson-quizzes-heading">
                <h2 id="lesson-quizzes-heading" class="text-lg font-semibold text-white">{{ __('Quizzes & checks') }}</h2>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Lesson quizzes belong to this video. Module recaps cover the whole module and unlock after every lesson in that module is complete.') }}</p>

                <div class="mt-5 space-y-4">
                    <div class="rounded-lg border border-zinc-700/80 bg-zinc-950/40 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('This lesson') }}</p>
                        @if($lessonQuiz)
                            <p class="mt-2 text-sm font-medium text-white">{{ $lessonQuiz->title }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                @if($recordProgress)
                                    @if($progress->quiz_passed)
                                        <span class="text-sm text-emerald-400">{{ __('Quiz passed') }}</span>
                                    @else
                                        <a href="{{ route('quizzes.show', $lessonQuiz) }}" class="inline-flex rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">{{ __('Take lesson quiz') }}</a>
                                        @if(! $progress->watched)
                                            <span class="text-xs text-zinc-500">{{ __('Tip: mark the lesson complete when you’re done, then pass the quiz to lock in progress.') }}</span>
                                        @endif
                                    @endif
                                @else
                                    <p class="text-sm text-zinc-500">{{ __('Lesson quiz unlocks when you reach this step in order.') }}</p>
                                @endif
                            </div>
                        @else
                            <p class="mt-2 text-sm text-zinc-400">{{ __('No quiz on this lesson — move on when you’re ready.') }}</p>
                        @endif
                    </div>

                    @if($moduleQuiz)
                        <div class="rounded-lg border border-zinc-700/80 bg-zinc-950/40 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('This module') }}</p>
                            <p class="mt-2 text-sm font-medium text-white">{{ $moduleQuiz->title }}</p>
                            <p class="mt-1 text-xs text-zinc-500">{{ __('Covers all lessons in “:module”.', ['module' => $lesson->module->title]) }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                @if($moduleQuizPassed)
                                    <span class="text-sm text-emerald-400">{{ __('Module quiz passed') }}</span>
                                @elseif($canTakeModuleQuiz)
                                    <a href="{{ route('quizzes.show', $moduleQuiz) }}" class="inline-flex rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Take module recap') }}</a>
                                @else
                                    <span class="text-sm text-zinc-500">{{ __('Complete every lesson in this module (and pass any lesson quizzes) to unlock.') }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            @if($docHtml)
                <article id="lesson-docs" class="lesson-doc prose prose-invert mt-8 max-w-none prose-pre:bg-zinc-900">
                    {!! $docHtml !!}
                </article>
            @endif

            <section class="mt-10 border-t border-zinc-800 pt-6">
                <h2 class="text-lg font-semibold text-white">{{ __('Discussion') }}</h2>
                <form method="POST" action="{{ route('lessons.comments.store', $lesson) }}" class="mt-4 space-y-2">
                    @csrf
                    <textarea name="body" rows="3" required placeholder="@mention someone…"
                        class="w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white"></textarea>
                    <button type="submit" class="rounded-md bg-zinc-700 px-3 py-1.5 text-sm text-white hover:bg-zinc-600">{{ __('Post comment') }}</button>
                </form>
                <ul class="mt-6 space-y-4">
                    @foreach($lesson->lessonComments->where('parent_id', null)->sortBy('created_at') as $comment)
                        <li id="comment-{{ $comment->id }}" class="rounded-md border border-zinc-800 bg-zinc-900/50 p-3">
                            <p class="text-xs text-zinc-500">
                                <a href="{{ route('profiles.show', $comment->user->profile) }}" class="text-emerald-400 hover:underline">{{ $comment->user->profile->display_name ?? $comment->user->name }}</a>
                                · {{ $comment->created_at->diffForHumans() }}
                            </p>
                            <div class="mt-2 text-sm text-zinc-200">{!! app(\App\Services\MentionRenderer::class)->toHtml($comment->body, 'lesson_comment', $comment->id) !!}</div>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.hljs) {
                window.hljs.highlightAll();
            }
        });
    </script>
@endsection
