@extends('layouts.learn')

@section('title', $forumCategory->name)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-white">{{ $forumCategory->name }}</h1>
        @auth
            <a href="{{ route('forums.threads.create', $forumCategory) }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">{{ __('New thread') }}</a>
        @endauth
    </div>
    <ul class="space-y-2">
        @foreach($threads as $thread)
            <li class="rounded-md border border-zinc-800 bg-zinc-900/50 px-4 py-3">
                <a href="{{ route('forums.thread', [$forumCategory, $thread]) }}" class="font-medium text-white hover:underline">{{ $thread->title }}</a>
                <p class="text-xs text-zinc-500">{{ $thread->author->profile->display_name ?? $thread->author->name }} · {{ $thread->posts_count }} {{ __('posts') }}</p>
            </li>
        @endforeach
    </ul>
    <div class="mt-6">{{ $threads->links() }}</div>
@endsection
