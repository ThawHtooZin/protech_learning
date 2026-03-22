@extends('layouts.learn')

@section('title', $forumThread->title)

@section('content')
    <div class="mb-4">
        <a href="{{ route('forums.category', $forumCategory) }}" class="text-sm text-zinc-500 hover:text-white">← {{ $forumCategory->name }}</a>
    </div>
    <h1 class="text-2xl font-bold text-white">{{ $forumThread->title }}</h1>
    <div class="mt-6 space-y-6">
        @foreach($posts as $post)
            <article class="rounded-lg border border-zinc-800 bg-zinc-900/50 p-4">
                <p class="text-xs text-zinc-500">
                    <a href="{{ route('profiles.show', $post->user->profile) }}" class="text-emerald-400 hover:underline">{{ $post->user->profile->display_name ?? $post->user->name }}</a>
                    · {{ $post->created_at->diffForHumans() }}
                </p>
                <div class="prose prose-invert mt-2 max-w-none text-zinc-200 prose-a:text-emerald-400">{!! app(\App\Services\MentionRenderer::class)->toHtml($post->body, 'forum_post', $post->id) !!}</div>
            </article>
        @endforeach
    </div>

    @auth
        <form method="POST" action="{{ route('forums.posts.store', [$forumCategory, $forumThread]) }}" class="mt-8 space-y-2">
            @csrf
            <textarea name="body" rows="4" required placeholder="{{ __('Reply…') }}"
                class="w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white"></textarea>
            <button type="submit" class="rounded-md bg-zinc-700 px-4 py-2 text-sm text-white hover:bg-zinc-600">{{ __('Post reply') }}</button>
        </form>
    @endauth
@endsection
