@extends('layouts.team')

@section('title', $team->name.' — '.$channel->name)

@section('content')
    @php
        $me = auth()->user();
        $meName = $me->profile?->display_name ?? $me->email;
        $bannerThemes = \App\Support\TeamPostBannerThemes::PRESETS;
        $renderer = app(\App\Services\MentionRenderer::class);
    @endphp

    <div class="mb-5 border-b border-zinc-200 pb-3">
        <h1 class="text-xl font-semibold text-zinc-900">{{ $channel->name }}</h1>
        <div class="mt-2 flex items-center gap-4">
            <span class="relative pb-2 text-sm font-semibold text-zinc-900">
                {{ __('Posts') }}
                <span class="absolute inset-x-0 -bottom-px h-0.5 bg-emerald-500"></span>
            </span>
        </div>
    </div>

    @if($canPostOnChannel)
        <div class="mb-5" x-data="{
            open: {{ $errors->has('body') || $errors->has('subject') ? 'true' : 'false' }},
            theme: @js(old('banner_theme', 'auto'))
        }">
            <button type="button" x-show="!open" @click="open = true"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-panel px-4 py-2 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-rail">
                <svg class="h-4 w-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                {{ __('Start a post') }}
            </button>

            <div x-show="open" x-cloak class="overflow-hidden rounded-xl border border-zinc-200 bg-panel shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-zinc-200 px-3 py-2.5">
                    <div class="flex min-w-0 items-center gap-2">
                        @include('teams._avatar', ['user' => $me, 'size' => 'sm'])
                        <p class="truncate text-sm font-semibold text-zinc-900">{{ $meName }}</p>
                    </div>
                    <button type="button" @click="open = false" class="rounded-md p-1.5 text-zinc-500 hover:bg-zinc-100" aria-label="{{ __('Close') }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('teams.channels.posts.store', [$team, $channel]) }}" id="channel-post-form" class="p-3">
                    @csrf
                    <input type="hidden" name="banner_theme" :value="theme">

                    <label for="post-subject" class="sr-only">{{ __('Add a subject') }}</label>
                    <input id="post-subject" name="subject" type="text" maxlength="200" value="{{ old('subject') }}"
                        placeholder="{{ __('Add a subject') }}"
                        class="w-full border-0 border-b border-emerald-500 bg-transparent px-0 pb-2 text-base font-semibold text-zinc-900 placeholder:font-normal placeholder:text-zinc-400 focus:outline-none focus:ring-0">

                    <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                        <span class="text-xs font-medium text-zinc-500">{{ __('Header theme') }}</span>
                        <button type="button" @click="theme = 'auto'"
                            class="rounded-full border px-2 py-0.5 text-xs font-medium"
                            :class="theme === 'auto' ? 'border-emerald-500 bg-emerald-50 text-emerald-800' : 'border-zinc-200 text-zinc-600 hover:bg-zinc-50'">
                            {{ __('Auto') }}
                        </button>
                        <button type="button" @click="theme = 'none'"
                            class="rounded-full border px-2 py-0.5 text-xs font-medium"
                            :class="theme === 'none' ? 'border-emerald-500 bg-emerald-50 text-emerald-800' : 'border-zinc-200 text-zinc-600 hover:bg-zinc-50'">
                            {{ __('No banner') }}
                        </button>
                        @foreach($bannerThemes as $key => $preset)
                            <button type="button" @click="theme = @js($key)"
                                class="h-5 w-5 rounded-full border-2"
                                :class="theme === @js($key) ? 'border-zinc-900 ring-1 ring-emerald-400' : 'border-white shadow'"
                                style="background: {{ $preset['bg'] }}"
                                title="{{ $preset['label'] }}"
                                aria-label="{{ $preset['label'] }}"></button>
                        @endforeach
                    </div>

                    <div class="mt-2.5 overflow-hidden rounded-lg border border-zinc-200">
                        <div id="channel-post-editor" class="min-h-[110px] bg-white text-sm text-zinc-900"></div>
                        <input type="hidden" name="body" id="channel-post-body" value="{{ old('body') }}">
                    </div>
                    <p class="mt-1 text-xs text-zinc-500">{{ __('Bold, lists, links, and @mentions are supported.') }}</p>

                    <div class="mt-3 flex justify-end border-t border-zinc-100 pt-2.5">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-500">
                            {{ __('Post') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <section class="space-y-3">
        @forelse($posts as $post)
            @php
                $author = $post->user->profile?->display_name ?? $post->user->email;
                $when = $post->created_at->timezone(config('app.timezone'))->format('d/m g:i A');
            @endphp
            <article id="team-post-{{ $post->id }}" class="overflow-visible rounded-xl border border-zinc-200 bg-panel">
                {{-- Header: avatar + name flush left; reactions compact top-right --}}
                <div class="flex items-center justify-between gap-2 px-3 pt-3">
                    <div class="flex min-w-0 items-center gap-2">
                        @include('teams._avatar', ['user' => $post->user, 'size' => 'sm'])
                        <div class="flex min-w-0 flex-wrap items-baseline gap-x-2">
                            <span class="truncate text-sm font-semibold text-zinc-900">{{ $author }}</span>
                            <span class="text-xs text-zinc-500">{{ $when }}</span>
                        </div>
                    </div>
                    @include('teams._reactions', ['post' => $post, 'placement' => 'toolbar'])
                </div>

                {{-- Banner + body: full card width (no left gutter under avatar) --}}
                <div class="px-3 pb-3 pt-2">
                    @if($post->hasBanner())
                        <div class="rounded-lg px-4 py-5 text-center"
                            style="background-color: {{ $post->banner_bg }}; color: {{ $post->banner_text }};">
                            <p class="text-lg font-bold leading-snug sm:text-xl">{{ $post->subject }}</p>
                        </div>
                    @elseif($post->subject)
                        <h2 class="text-base font-semibold text-zinc-900">{{ $post->subject }}</h2>
                    @endif

                    <div class="team-post-body mt-2 text-sm leading-snug text-zinc-800">
                        @if($post->body_is_html)
                            {!! $renderer->enrichHtml($post->body, 'team_post', $post->id, true) !!}
                        @else
                            {!! $renderer->toHtml($post->body, 'team_post', $post->id, true) !!}
                        @endif
                    </div>
                </div>

                @if($post->replies->isNotEmpty())
                    <div class="border-t border-zinc-200">
                        @foreach($post->replies as $reply)
                            @php
                                $replyAuthor = $reply->user->profile?->display_name ?? $reply->user->email;
                                $replyWhen = $reply->created_at->timezone(config('app.timezone'))->format('d/m g:i A');
                                $replyBody = trim(preg_replace("/[ \t]+/u", ' ', $reply->body) ?? $reply->body);
                            @endphp
                            <div id="team-post-{{ $reply->id }}" class="flex gap-2 border-b border-zinc-100 px-3 py-2 last:border-0">
                                <div class="w-0.5 shrink-0 self-stretch rounded-full bg-amber-500" aria-hidden="true"></div>
                                @include('teams._avatar', ['user' => $reply->user, 'size' => 'sm'])
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="min-w-0 truncate text-sm leading-none">
                                            <span class="font-semibold text-zinc-900">{{ $replyAuthor }}</span>
                                            <span class="ml-1.5 text-xs font-normal text-zinc-500">{{ $replyWhen }}</span>
                                        </p>
                                        @include('teams._reactions', ['post' => $reply])
                                    </div>
                                    <div class="mt-0.5 text-sm leading-snug text-zinc-800">
                                        {!! $renderer->toHtml($replyBody, 'team_post', $reply->id, true) !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($canReplyOnChannel)
                    <form method="POST" action="{{ route('teams.channels.posts.replies.store', [$team, $channel, $post]) }}"
                        class="flex items-center gap-2 border-t border-zinc-200 px-3 py-2">
                        @csrf
                        @include('teams._avatar', ['user' => $me, 'size' => 'sm'])
                        <div class="flex min-w-0 flex-1 items-center gap-1 rounded-md border border-zinc-200 bg-white px-2.5 focus-within:border-emerald-500">
                            <label for="reply-{{ $post->id }}" class="sr-only">{{ __('Reply') }}</label>
                            <input id="reply-{{ $post->id }}" name="body" type="text" required maxlength="20000"
                                placeholder="{{ __('Reply') }}"
                                class="min-w-0 flex-1 border-0 bg-transparent py-1.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-0">
                            <button type="submit" class="shrink-0 p-1 text-emerald-700 hover:text-emerald-600" aria-label="{{ __('Send') }}" title="{{ __('Send') }}">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                @endif
            </article>
        @empty
            <p class="rounded-xl border border-zinc-200 bg-panel px-4 py-10 text-center text-sm text-zinc-500">
                {{ __('No posts yet.') }}
                @if($canPostOnChannel)
                    <span class="mt-1 block">{{ __('Start a post to begin the conversation.') }}</span>
                @endif
            </p>
        @endforelse
    </section>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <style>
        .team-post-body p { margin: 0.15em 0; line-height: 1.45; }
        .team-post-body p:empty { display: none; }
        .team-post-body ul { list-style: disc; padding-left: 1.1rem; margin: 0.2em 0; }
        .team-post-body ol { list-style: decimal; padding-left: 1.1rem; margin: 0.2em 0; }
        .team-post-body li { margin: 0.1em 0; }
        .team-post-body a { color: #047857; text-decoration: underline; }
        .team-post-body h2, .team-post-body h3 { font-weight: 600; margin: 0.35em 0 0.15em; font-size: 1em; }
        #channel-post-editor .ql-editor { min-height: 110px; }
        #channel-post-editor .ql-container.ql-snow { border: 0; }
        .ql-toolbar.ql-snow { border: 0; border-bottom: 1px solid #e4e4e7; background: #fafafa; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('channel-post-body');
            var mount = document.getElementById('channel-post-editor');
            var form = document.getElementById('channel-post-form');
            if (!input || !mount || !form || typeof Quill === 'undefined') return;

            var quill = new Quill('#channel-post-editor', {
                theme: 'snow',
                placeholder: @json(__('Type a message')),
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'link'],
                        ['clean']
                    ]
                }
            });

            if (input.value) {
                quill.root.innerHTML = input.value;
            }

            form.addEventListener('submit', function (event) {
                var html = quill.root.innerHTML;
                if (html === '<p><br></p>' || html === '<p></p>') html = '';
                input.value = html;
                if (!html.trim()) {
                    event.preventDefault();
                    quill.focus();
                }
            });
        });
    </script>
@endpush
