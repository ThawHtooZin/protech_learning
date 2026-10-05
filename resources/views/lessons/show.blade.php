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
    <div class="lg:grid lg:grid-cols-[minmax(0,260px)_1fr] lg:items-start lg:gap-10">
        <aside class="mb-8 lg:mb-0 lg:sticky lg:top-24 lg:self-start">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Series') }}</p>
            <p class="mt-1 text-sm font-medium text-white">{{ $course->title }}</p>
            <nav class="mt-4 space-y-4 text-sm">
                @foreach($course->modules as $module)
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-zinc-600">
                            <span>{{ $module->title }}</span>
                        </div>
                        <ul class="mt-2 space-y-1 border-l border-zinc-800 pl-3">
                            @foreach($module->lessons as $navLesson)
                                <li>
                                    <a href="{{ route('lessons.show', $navLesson) }}"
                                        class="flex items-start gap-2 py-0.5 {{ $navLesson->id === $lesson->id ? 'font-medium text-emerald-400' : 'text-zinc-400 hover:text-white' }}">
                                        @if($completedLessonIds->contains($navLesson->id))
                                            <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded border border-emerald-600 bg-emerald-950 text-[10px] text-emerald-400" title="{{ __('Watched') }}">✓</span>
                                        @else
                                            <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 rounded border border-zinc-700"></span>
                                        @endif
                                        <span class="min-w-0 flex-1">{{ $navLesson->title }}</span>
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

            @if($playable)
                @if($recordProgress && $playerKind)
                    <div class="mt-6 overflow-hidden rounded-xl border border-zinc-800 bg-black shadow-2xl shadow-black/50"
                        data-lesson-player
                        data-player-kind="{{ $playerKind }}"
                        data-progress-url="{{ route('lessons.progress', $lesson) }}"
                        data-start-position="{{ (int) $progress->last_position_seconds }}"
                        data-save-interval="{{ config('lms.watch.progress_save_interval_seconds') }}"
                        @if($playerKind === 'youtube' && $youtubeVideoId) data-youtube-id="{{ $youtubeVideoId }}" @endif>
                        @if($playerKind === 'youtube' && $youtubeVideoId)
                            <div id="lesson-youtube-player" data-youtube-target class="aspect-video w-full"></div>
                        @elseif($playerKind === 'html5')
                            <video src="{{ $playable->signedUrl }}" controls class="w-full aspect-video" playsinline></video>
                        @endif
                    </div>
                    @if($progress->last_position_seconds > 0 && ! $progress->watched)
                        <p class="mt-2 text-sm text-emerald-400/90">{{ __('Resume from :time', ['time' => gmdate($progress->last_position_seconds >= 3600 ? 'H:i:s' : 'i:s', $progress->last_position_seconds)]) }}</p>
                    @endif
                    @vite('resources/js/lesson-player.js')
                @else
                    <div class="mt-6 overflow-hidden rounded-xl border border-zinc-800 bg-black shadow-2xl shadow-black/50">
                        @if($playable->kind === 'embed')
                            {!! $playable->embedHtml !!}
                        @else
                            <video src="{{ $playable->signedUrl }}" controls class="w-full" playsinline></video>
                        @endif
                    </div>
                @endif

                @if($playable->externalWatchUrl)
                    <p class="mt-3 text-sm text-zinc-500">
                        <a href="{{ $playable->externalWatchUrl }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-400 hover:underline">{{ __('Open video on YouTube') }}</a>
                    </p>
                @endif
            @else
                <div class="mt-6 flex min-h-[200px] flex-col items-center justify-center rounded-xl border border-zinc-700 bg-zinc-950/80 px-6 py-12 text-center shadow-inner">
                    <p class="text-sm font-medium text-zinc-200">{{ __('Video unavailable.') }}</p>
                    <p class="mt-2 max-w-md text-xs text-zinc-500">{{ __('Check the lesson video settings in admin.') }}</p>
                </div>
            @endif

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
@endsection
