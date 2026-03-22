@extends('layouts.admin')

@section('title', $lesson->title)

@section('heading', __('Edit lesson'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('admin.lessons.update', [$course, $module, $lesson]) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title', $lesson->title) }}" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Video driver') }}</label>
                <select name="video_driver" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
                    <option value="youtube" @selected($lesson->video_driver === 'youtube')>YouTube</option>
                    <option value="r2" @selected($lesson->video_driver === 'r2')>R2 / S3</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Video ref') }}</label>
                <input type="text" name="video_ref" value="{{ old('video_ref', $lesson->video_ref) }}" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Duration (seconds)') }}</label>
                <input type="number" name="duration_seconds" value="{{ old('duration_seconds', $lesson->duration_seconds) }}" min="1" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Documentation (Markdown)') }}</label>
                <textarea name="documentation_markdown" rows="12" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 font-mono text-sm text-white">{{ old('documentation_markdown', $lesson->documentation_markdown) }}</textarea>
            </div>
            <button type="submit" class="rounded-md bg-emerald-600 px-6 py-2 text-white">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
