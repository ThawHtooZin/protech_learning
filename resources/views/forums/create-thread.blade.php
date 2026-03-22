@extends('layouts.learn')

@section('title', __('New thread'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="mb-6 text-2xl font-bold text-white">{{ __('New thread in') }} {{ $forumCategory->name }}</h1>
        <form method="POST" action="{{ route('forums.threads.store', $forumCategory) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Body') }}</label>
                <textarea name="body" rows="8" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">{{ old('body') }}</textarea>
            </div>
            @if($tags->isNotEmpty())
                <div>
                    <span class="block text-sm text-zinc-400">{{ __('Tags') }}</span>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <label class="flex items-center gap-1 text-sm text-zinc-300">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}">
                                {{ $tag->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
            <button type="submit" class="rounded-md bg-emerald-600 px-6 py-2 font-medium text-white hover:bg-emerald-500">{{ __('Create thread') }}</button>
        </form>
    </div>
@endsection
