@extends('layouts.admin')

@section('title', $course->title)

@section('heading')
    {{ __('Edit course') }}: {{ $course->title }}
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.courses.index') }}" class="text-sm text-emerald-700 hover:underline">← {{ __('Back to all courses') }}</a>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" class="rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-50" onclick="ProtechModal.open('grant-access-dialog-{{ $course->id }}')">
                {{ __('Grant access') }}
            </button>
            <button type="submit" form="course-edit-form" class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Save') }}</button>
        </div>
    </div>
    <form id="course-edit-form" method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data" class="mt-2 max-w-2xl space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm text-zinc-600">{{ __('Title') }}</label>
            <input type="text" name="title" value="{{ old('title', $course->title) }}" required class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">
        </div>
        @include('admin.courses._description_editor', ['course' => $course])
        @include('admin.courses._cover_field', ['course' => $course])
        @include('admin.courses._access_fields', ['course' => $course])
        <label class="flex items-center gap-2 text-sm text-zinc-700">
            <input type="checkbox" name="is_published" value="1" class="rounded border-zinc-300" @checked(old('is_published', $course->is_published))>
            {{ __('Published') }}
        </label>
    </form>

    @include('admin.courses._grant_access_modal', [
        'course' => $course,
        'grantUsers' => $grantUsers,
        'grantTeams' => $grantTeams,
        'grantedUserIds' => $grantedUserIds,
        'grantedTeamIds' => $grantedTeamIds,
    ])

    @if(request()->boolean('grant'))
        <script>document.addEventListener('DOMContentLoaded',()=>window.ProtechModal?.open('grant-access-dialog-{{ $course->id }}'));</script>
    @endif

    <section class="mt-12 border-t border-zinc-200 pt-8"
        data-course-structure
        data-modules-reorder-url="{{ route('admin.modules.reorder', $course) }}">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-lg font-semibold text-zinc-900">{{ __('Modules') }}</h2>
            <p class="text-xs text-zinc-500">{{ __('Drag modules and lessons to reorder (YouTube-style curriculum).') }}</p>
        </div>
        <form method="POST" action="{{ route('admin.modules.store', $course) }}" class="mt-4 flex flex-wrap gap-2">
            @csrf
            <input type="text" name="title" placeholder="{{ __('Module title') }}" required class="flex-1 rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">
            <button type="submit" class="rounded-md bg-zinc-800 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700">{{ __('Add module') }}</button>
        </form>

        <div class="mt-6 space-y-4" data-module-sortable>
            @foreach($course->modules->sortBy('sort_order') as $module)
                <div class="rounded-lg border border-zinc-200 bg-white p-4" data-module-id="{{ $module->id }}">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" data-drag-handle class="cursor-grab rounded border border-zinc-300 px-2 py-1 text-zinc-500 hover:text-zinc-900 active:cursor-grabbing" title="{{ __('Drag to reorder module') }}">⋮⋮</button>
                        <h3 class="flex-1 font-medium text-zinc-900">{{ $module->title }}</h3>
                        <form method="POST" action="{{ route('admin.modules.destroy', [$course, $module]) }}" onsubmit="return confirm({{ json_encode(__('Delete this module and all its lessons?')) }})">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-400 hover:underline">{{ __('Delete module') }}</button>
                        </form>
                    </div>
                    <p class="mt-2 text-sm">
                        <a href="{{ route('admin.lessons.create', [$course, $module]) }}" class="text-emerald-700 hover:underline">{{ __('Add lesson') }}</a>
                    </p>
                    <ul class="mt-3 space-y-1" data-lesson-sortable data-lessons-reorder-url="{{ route('admin.lessons.reorder', [$course, $module]) }}">
                        @foreach($module->lessons->sortBy('sort_order') as $lesson)
                            <li class="flex flex-wrap items-center gap-2 rounded-md border border-zinc-200 bg-zinc-50/30 px-2 py-2 text-sm" data-lesson-id="{{ $lesson->id }}">
                                <button type="button" data-drag-handle class="cursor-grab text-zinc-600 hover:text-zinc-700 active:cursor-grabbing" title="{{ __('Drag to reorder lesson') }}">⋮⋮</button>
                                <span class="min-w-0 flex-1 font-medium text-zinc-800">{{ $lesson->title }}</span>
                                <span class="flex flex-wrap items-center gap-2 text-xs">
                                    <a href="{{ route('admin.lessons.edit', [$course, $module, $lesson]) }}" class="text-emerald-700 hover:underline">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.lessons.destroy', [$course, $module, $lesson]) }}" class="inline" onsubmit="return confirm({{ json_encode(__('Delete this lesson, its progress, and comments?')) }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>

    @vite('resources/js/course-structure.js')

    <section class="mt-12 border-t border-red-900/30 pt-8">
        <h2 class="text-sm font-semibold text-red-400">{{ __('Danger zone') }}</h2>
        <p class="mt-2 max-w-xl text-sm text-zinc-500">{{ __('Deleting removes this course, all modules, lessons, enrollments, and progress. This cannot be undone.') }}</p>
        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="mt-4" onsubmit="return confirm({{ json_encode(__('Delete this course and all its modules, lessons, and enrollments? This cannot be undone.')) }})">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-md border border-red-300 bg-red-50 px-4 py-2 text-sm text-red-600 hover:bg-red-100">{{ __('Delete course') }}</button>
        </form>
    </section>
@endsection
