@extends('layouts.admin')

@section('title', __('Module quiz'))

@section('heading')
    {{ __('New module quiz') }}: {{ $module->title }}
@endsection

@section('content')
    <div class="mx-auto max-w-5xl space-y-4">
        <nav class="text-sm text-zinc-500">
            <a href="{{ route('admin.courses.edit', $course) }}" class="text-emerald-400 hover:underline">{{ $course->title }}</a>
            <span class="text-zinc-600">/</span>
            <span class="text-white">{{ $module->title }}</span>
        </nav>
        <p class="text-sm text-zinc-500">{{ __('Module recap quiz — learners must pass to complete the module.') }}</p>

        @include('admin.quizzes.partials.lesson-quiz-builder', [
            'formAction' => route('admin.quizzes.module.store', [$course, $module]),
            'httpMethod' => 'POST',
            'submitLabel' => __('Create module quiz'),
            'course' => $course,
            'module' => $module,
            'lesson' => null,
            'quiz' => null,
            'questionBank' => $questionBank,
            'technologies' => $technologies,
            'topics' => $topics,
            'initialQuestionIds' => $initialQuestionIds,
            'questionReturnQuery' => '',
        ])
    </div>
@endsection
