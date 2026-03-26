@php
    /** @var \App\Models\ForumPost $post */
    /** @var \App\Models\ForumCategory $forumCategory */
    /** @var \App\Models\ForumThread $forumThread */
    /** @var int $depth */

    $depth = $depth ?? 0;
    $maxDepth = 6;
    $indent = min($depth, $maxDepth) * 16;
@endphp

<article class="rounded-lg border border-zinc-800 bg-zinc-900/50 p-4" style="margin-left: {{ $indent }}px">
    <p class="text-xs text-zinc-500">
        <a href="{{ route('profiles.show', $post->user->profile) }}" class="text-emerald-400 hover:underline">{{ $post->user->profile->display_name ?? $post->user->name }}</a>
        · {{ $post->created_at->diffForHumans() }}
        <a href="#post-{{ $post->id }}" id="post-{{ $post->id }}" class="ml-2 text-zinc-600 hover:text-zinc-400">#{{ $post->id }}</a>
    </p>
    <div class="prose prose-invert mt-2 max-w-none text-zinc-200 prose-a:text-emerald-400">{!! app(\App\Services\MentionRenderer::class)->toHtml($post->body, 'forum_post', $post->id) !!}</div>

    @auth
        <details class="mt-3">
            <summary class="cursor-pointer text-xs text-zinc-400 hover:text-white">{{ __('Reply') }}</summary>
            <form method="POST" action="{{ route('forums.posts.store', [$forumCategory, $forumThread]) }}" class="mt-2 space-y-2">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $post->id }}">
                <textarea name="body" rows="3" required placeholder="{{ __('Write a reply…') }}"
                    class="w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white"></textarea>
                <button type="submit" class="rounded-md bg-zinc-700 px-3 py-1.5 text-xs text-white hover:bg-zinc-600">{{ __('Post reply') }}</button>
            </form>
        </details>
    @endauth
</article>

@if($depth < $maxDepth)
    @foreach($post->replies as $reply)
        <div class="mt-3">
            @include('forums._post', ['post' => $reply, 'forumCategory' => $forumCategory, 'forumThread' => $forumThread, 'depth' => $depth + 1])
        </div>
    @endforeach
@endif

