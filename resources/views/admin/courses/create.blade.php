@extends('layouts.admin')

@section('title', __('New course'))

@section('heading', __('New course'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.courses.index') }}" class="text-sm text-emerald-700 hover:underline">← {{ __('Back to all courses') }}</a>
            <button type="submit" form="course-create-form" class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Create') }}</button>
        </div>
        <form id="course-create-form" method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-zinc-600">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">
            </div>
            @include('admin.courses._description_editor')
            @include('admin.courses._cover_field')
            @include('admin.courses._access_fields')
            <p class="text-xs text-zinc-500">{{ __('After creating, use Grant access to add people or teams.') }}</p>
            <label class="flex items-center gap-2 text-sm text-zinc-700">
                <input type="checkbox" name="is_published" value="1" class="rounded border-zinc-300" @checked(old('is_published'))>
                {{ __('Published') }}
            </label>
        </form>
    </div>
@endsection
