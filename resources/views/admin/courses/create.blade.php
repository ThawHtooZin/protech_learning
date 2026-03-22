@extends('layouts.admin')

@section('title', __('New course'))

@section('heading', __('New course'))

@section('content')
    <div class="mx-auto max-w-lg">
        <div class="mb-6">
            <a href="{{ route('admin.courses.index') }}" class="text-sm text-emerald-400 hover:underline">← {{ __('Back to all courses') }}</a>
        </div>
        <form method="POST" action="{{ route('admin.courses.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Title') }}</label>
                <input type="text" name="title" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Description') }}</label>
                <textarea name="description" rows="4" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white"></textarea>
            </div>
            <label class="flex items-center gap-2 text-sm text-zinc-300">
                <input type="checkbox" name="is_published" value="1" class="rounded border-zinc-600">
                {{ __('Published') }}
            </label>
            <button type="submit" class="rounded-md bg-emerald-600 px-6 py-2 text-white">{{ __('Create') }}</button>
        </form>
    </div>
@endsection
