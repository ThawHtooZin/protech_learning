@extends('layouts.learn')

@section('title', $lesson->title)

@section('content')
    {{-- Breadcrumb — same language as course detail --}}
    <p class="mb-8 font-mono text-xs text-zinc-500">
        <a href="{{ route('courses.index') }}" class="hover:text-emerald-700">{{ __('Library') }}</a>
        <span class="mx-1.5 text-zinc-300">/</span>
        <a href="{{ route('courses.show', $course) }}" class="hover:text-emerald-700">{{ $course->title }}</a>
        <span class="mx-1.5 text-zinc-300">/</span>
        <span class="text-zinc-700">{{ $lesson->title }}</span>
    </p>

    <div class="grid items-start gap-10 md:grid-cols-[minmax(0,16rem)_1fr] md:gap-12 lg:grid-cols-[minmax(0,18rem)_1fr]">
        {{-- Series nav --}}
        <aside class="md:sticky md:top-24">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-zinc-400">{{ __('Series') }}</p>
            <a href="{{ route('courses.show', $course) }}" class="mt-1.5 block text-sm font-semibold text-zinc-900 hover:text-emerald-700">
                {{ $course->title }}
            </a>

            <nav class="mt-6 space-y-6">
                @foreach($course->modules as $moduleIndex => $module)
                    <div>
                        <p class="flex items-baseline gap-2 text-xs font-medium text-zinc-500">
                            <span class="font-mono text-sm font-bold text-emerald-600">{{ str_pad((string) ($moduleIndex + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="min-w-0">{{ $module->title }}</span>
                        </p>
                        <ul class="mt-2 space-y-0.5">
                            @foreach($module->lessons as $navLesson)
                                @php $isCurrent = $navLesson->id === $lesson->id; @endphp
                                <li>
                                    <a href="{{ route('lessons.show', $navLesson) }}"
                                        class="flex items-start gap-2.5 rounded-lg px-2 py-1.5 text-sm transition {{ $isCurrent ? 'bg-emerald-50 font-medium text-emerald-700' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' }}">
                                        @if($completedLessonIds->contains($navLesson->id))
                                            <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded border border-emerald-500 bg-emerald-50 text-[10px] font-bold text-emerald-700" title="{{ __('Watched') }}">✓</span>
                                        @else
                                            <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 rounded border {{ $isCurrent ? 'border-emerald-400' : 'border-zinc-300' }}"></span>
                                        @endif
                                        <span class="min-w-0 flex-1 leading-snug">{{ $navLesson->title }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </aside>

        {{-- Player + discussion --}}
        <div class="min-w-0">
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-[2rem]">{{ $lesson->title }}</h1>

            @if($playable)
                @if($recordProgress && $playerKind)
                    <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-black shadow-sm"
                        data-lesson-player
                        data-player-kind="{{ $playerKind }}"
                        data-progress-url="{{ route('lessons.progress', $lesson) }}"
                        data-start-position="{{ (int) $progress->last_position_seconds }}"
                        data-save-interval="{{ config('lms.watch.progress_save_interval_seconds') }}"
                        @if($playerKind === 'youtube' && $youtubeVideoId) data-youtube-id="{{ $youtubeVideoId }}" @endif>
                        @if($playerKind === 'youtube' && $youtubeVideoId)
                            <div id="lesson-youtube-player" data-youtube-target class="aspect-video w-full"></div>
                        @elseif($playerKind === 'html5')
                            <video src="{{ $playable->signedUrl }}" controls class="aspect-video w-full" playsinline></video>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                        @if($progress->last_position_seconds > 0 && ! $progress->watched)
                            <button type="button"
                                data-resume-playback
                                data-resume-at="{{ (int) $progress->last_position_seconds }}"
                                class="font-medium text-emerald-700 hover:text-emerald-600">
                                {{ __('Resume from :time', ['time' => gmdate($progress->last_position_seconds >= 3600 ? 'H:i:s' : 'i:s', $progress->last_position_seconds)]) }}
                            </button>
                        @endif
                        @if($playable->externalWatchUrl)
                            <a href="{{ $playable->externalWatchUrl }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 hover:text-emerald-600">
                                {{ __('Open video on YouTube') }}
                            </a>
                        @endif
                    </div>
                    @vite('resources/js/lesson-player.js')
                @else
                    <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-black shadow-sm">
                        @if($playable->kind === 'embed')
                            {!! $playable->embedHtml !!}
                        @else
                            <video src="{{ $playable->signedUrl }}" controls class="w-full" playsinline></video>
                        @endif
                    </div>
                    @if($playable->externalWatchUrl)
                        <p class="mt-3 text-sm">
                            <a href="{{ $playable->externalWatchUrl }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 hover:text-emerald-600">
                                {{ __('Open video on YouTube') }}
                            </a>
                        </p>
                    @endif
                @endif
            @else
                <div class="mt-6 flex min-h-[200px] flex-col items-center justify-center rounded-2xl border border-zinc-200 bg-white px-6 py-12 text-center">
                    <p class="text-sm font-medium text-zinc-800">{{ __('Video unavailable.') }}</p>
                    <p class="mt-2 max-w-md text-xs text-zinc-500">{{ __('Check the lesson video settings in admin.') }}</p>
                </div>
            @endif

            @if($docHtml)
                <article id="lesson-docs" class="lesson-doc course-prose mt-10 max-w-none text-zinc-700">
                    {!! $docHtml !!}
                </article>
            @endif

            <section class="mt-12 border-t border-zinc-200 pt-8">
                <h2 class="text-lg font-semibold tracking-tight text-zinc-900">{{ __('Discussion') }}</h2>
                <form method="POST" action="{{ route('lessons.comments.store', $lesson) }}" class="mt-4 space-y-3">
                    @csrf
                    <textarea name="body" rows="3" required placeholder="@mention someone…"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        {{ __('Post comment') }}
                    </button>
                </form>
                <ul class="mt-6 space-y-3">
                    @foreach($lesson->lessonComments->where('parent_id', null)->sortBy('created_at') as $comment)
                        <li id="comment-{{ $comment->id }}" class="rounded-xl border border-zinc-200 bg-white p-4">
                            <p class="text-xs text-zinc-500">
                                <a href="{{ route('profiles.show', $comment->user->profile) }}" class="font-medium text-emerald-700 hover:text-emerald-600">{{ $comment->user->profile->display_name ?? $comment->user->name }}</a>
                                · {{ $comment->created_at->diffForHumans() }}
                            </p>
                            <div class="mt-2 text-sm leading-relaxed text-zinc-800">{!! app(\App\Services\MentionRenderer::class)->toHtml($comment->body, 'lesson_comment', $comment->id) !!}</div>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
@endsection
