@extends('layouts.admin')

@section('title', __('Admin'))

@section('heading', __('Overview'))

@section('content')
    <p class="mb-8 text-sm text-zinc-400">{{ __('Manage courses, the question bank, and forum structure. Learners use the main site — switch with “Back to learning site”.') }}</p>

    <div class="mb-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-800 bg-zinc-900/60 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Courses') }}</p>
            <p class="mt-2 text-3xl font-semibold text-white">{{ $courseCount }}</p>
            <a href="{{ route('admin.courses.index') }}" class="mt-3 inline-block text-sm text-emerald-400 hover:underline">{{ __('Open course list') }} →</a>
        </div>
        <div class="rounded-xl border border-zinc-800 bg-zinc-900/60 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Question bank') }}</p>
            <p class="mt-2 text-3xl font-semibold text-white">{{ $questionCount }}</p>
            <a href="{{ route('admin.questions.index') }}" class="mt-3 inline-block text-sm text-emerald-400 hover:underline">{{ __('Open question bank') }} →</a>
        </div>
        <a href="{{ route('admin.forums.categories') }}" class="rounded-xl border border-zinc-800 bg-zinc-900/60 p-5 transition hover:border-emerald-700/50 hover:bg-zinc-900">
            <p class="text-sm font-medium text-white">{{ __('Forum categories') }}</p>
            <p class="mt-1 text-xs text-zinc-500">{{ __('Community boards') }}</p>
        </a>
        <a href="{{ route('admin.forums.tags') }}" class="rounded-xl border border-zinc-800 bg-zinc-900/60 p-5 transition hover:border-emerald-700/50 hover:bg-zinc-900">
            <p class="text-sm font-medium text-white">{{ __('Tags') }}</p>
            <p class="mt-1 text-xs text-zinc-500">{{ __('Thread labels') }}</p>
        </a>
    </div>

    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">{{ __('Shortcuts') }}</h2>
    <ul class="mt-3 flex flex-wrap gap-3 text-sm">
        <li><a href="{{ route('admin.courses.create') }}" class="text-emerald-400 hover:underline">{{ __('New course') }}</a></li>
        <li class="text-zinc-600">·</li>
        <li><a href="{{ route('admin.questions.create') }}" class="text-emerald-400 hover:underline">{{ __('Add question') }}</a></li>
    </ul>
@endsection
