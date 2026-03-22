@extends('layouts.admin')

@section('title', __('New lesson'))

@section('heading')
    {{ __('New lesson') }} — {{ $module->title }}
@endsection

@section('content')
    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('admin.lessons.store', [$course, $module]) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Title') }}</label>
                <input type="text" name="title" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Video driver') }}</label>
                <select name="video_driver" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
                    <option value="youtube">YouTube</option>
                    <option value="r2">R2 / S3</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Video ref (YouTube ID or URL)') }}</label>
                <input type="text" name="video_ref" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Duration (seconds)') }}</label>
                <input type="number" name="duration_seconds" min="1" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Documentation (Markdown)') }}</label>
                <textarea name="documentation_markdown" rows="12" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 font-mono text-sm text-white"></textarea>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="rounded-md bg-emerald-600 px-6 py-2 text-white">{{ __('Create') }}</button>
                <a href="{{ route('admin.courses.edit', $course) }}" class="text-zinc-400 hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
