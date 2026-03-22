@extends('layouts.admin')

@section('title', $course->title)

@section('heading')
    {{ __('Edit course') }}: {{ $course->title }}
@endsection

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.courses.index') }}" class="text-sm text-emerald-400 hover:underline">← {{ __('Back to all courses') }}</a>
    </div>
    <form method="POST" action="{{ route('admin.courses.update', $course) }}" class="mt-2 max-w-lg space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm text-zinc-400">{{ __('Title') }}</label>
            <input type="text" name="title" value="{{ old('title', $course->title) }}" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
        </div>
        <div>
            <label class="block text-sm text-zinc-400">{{ __('Description') }}</label>
            <textarea name="description" rows="4" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">{{ old('description', $course->description) }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-zinc-300">
            <input type="checkbox" name="is_published" value="1" class="rounded border-zinc-600" @checked(old('is_published', $course->is_published))>
            {{ __('Published') }}
        </label>
        <button type="submit" class="rounded-md bg-emerald-600 px-6 py-2 text-white">{{ __('Save') }}</button>
    </form>

    <section class="mt-12 border-t border-zinc-800 pt-8">
        <h2 class="text-lg font-semibold text-white">{{ __('Modules') }}</h2>
        <form method="POST" action="{{ route('admin.modules.store', $course) }}" class="mt-4 flex flex-wrap gap-2">
            @csrf
            <input type="text" name="title" placeholder="{{ __('Module title') }}" required class="flex-1 rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            <button type="submit" class="rounded-md bg-zinc-700 px-4 py-2 text-white">{{ __('Add module') }}</button>
        </form>

        @foreach($course->modules as $module)
            <div class="mt-8 rounded-lg border border-zinc-800 bg-zinc-900/40 p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="font-medium text-white">{{ $module->title }}</h3>
                    <form method="POST" action="{{ route('admin.modules.destroy', [$course, $module]) }}" onsubmit="return confirm('Delete module?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-400 hover:underline">{{ __('Delete') }}</button>
                    </form>
                </div>
                <p class="mt-2 text-sm">
                    <a href="{{ route('admin.lessons.create', [$course, $module]) }}" class="text-emerald-400 hover:underline">{{ __('Add lesson') }}</a>
                    ·
                    <a href="{{ route('admin.quizzes.module.create', [$course, $module]) }}" class="text-amber-400 hover:underline">{{ __('Module quiz') }}</a>
                </p>
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach($module->lessons as $lesson)
                        <li class="flex flex-wrap items-center gap-2">
                            <span class="text-zinc-300">{{ $lesson->title }}</span>
                            <a href="{{ route('admin.lessons.edit', [$course, $module, $lesson]) }}" class="text-emerald-400 hover:underline">{{ __('Edit') }}</a>
                            <a href="{{ route('admin.quizzes.lesson.create', [$course, $module, $lesson]) }}" class="text-xs text-amber-400 hover:underline">{{ __('Lesson quiz') }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </section>

    <section class="mt-12 border-t border-red-900/30 pt-8">
        <h2 class="text-sm font-semibold text-red-400">{{ __('Danger zone') }}</h2>
        <p class="mt-2 max-w-xl text-sm text-zinc-500">{{ __('Deleting removes this course, all modules, lessons, quizzes tied to them, enrollments, and progress. This cannot be undone.') }}</p>
        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="mt-4" onsubmit="return confirm({{ json_encode(__('Delete this course and all its modules, lessons, quizzes, and enrollments? This cannot be undone.')) }})">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-md border border-red-800 bg-red-950/30 px-4 py-2 text-sm text-red-300 hover:bg-red-950/50">{{ __('Delete course') }}</button>
        </form>
    </section>
@endsection
