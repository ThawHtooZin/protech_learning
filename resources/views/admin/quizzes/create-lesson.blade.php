@extends('layouts.admin')

@section('title', __('Lesson quiz'))

@section('heading')
    {{ __('New lesson quiz') }}: {{ $lesson->title }}
@endsection

@section('content')
    <div class="mx-auto max-w-5xl space-y-4">
        <nav class="text-sm text-zinc-500">
            <a href="{{ route('admin.courses.edit', $course) }}" class="text-emerald-400 hover:underline">{{ $course->title }}</a>
            <span class="text-zinc-600">/</span>
            <span class="text-zinc-400">{{ $module->title }}</span>
            <span class="text-zinc-600">/</span>
            <span class="text-white">{{ $lesson->title }}</span>
        </nav>
        <p class="text-sm text-zinc-500">{{ __('One quiz per lesson. Learners see questions in the order you set here.') }}</p>

        @include('admin.quizzes.partials.lesson-quiz-builder', [
            'formAction' => route('admin.quizzes.lesson.store', [$course, $module, $lesson]),
            'httpMethod' => 'POST',
            'submitLabel' => __('Create lesson quiz'),
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
            'quiz' => null,
            'questionBank' => $questionBank,
            'technologies' => $technologies,
            'topics' => $topics,
            'initialQuestionIds' => $initialQuestionIds,
            'questionReturnQuery' => http_build_query([
                'return_context' => 'lesson_quiz',
                'return_course_id' => $course->id,
                'return_module_id' => $module->id,
                'return_lesson_id' => $lesson->id,
            ]),
        ])
    </div>
@endsection
