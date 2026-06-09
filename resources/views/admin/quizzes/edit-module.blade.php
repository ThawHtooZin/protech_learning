@extends('layouts.admin')

@section('title', __('Edit module quiz'))

@section('heading')
    {{ __('Edit module quiz') }}: {{ $module->title }}
@endsection

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <nav class="text-sm text-zinc-500">
            <a href="{{ route('admin.courses.edit', $course) }}" class="text-emerald-400 hover:underline">{{ $course->title }}</a>
            <span class="text-zinc-600">/</span>
            <span class="text-white">{{ $module->title }}</span>
        </nav>

        @include('admin.quizzes.partials.lesson-quiz-builder', [
            'formAction' => route('admin.quizzes.module.update', [$course, $module]),
            'httpMethod' => 'PUT',
            'submitLabel' => __('Save changes'),
            'course' => $course,
            'module' => $module,
            'lesson' => null,
            'quiz' => $quiz,
            'questionBank' => $questionBank,
            'technologies' => $technologies,
            'topics' => $topics,
            'initialQuestionIds' => $initialQuestionIds,
            'questionReturnQuery' => '',
        ])

        <section class="border-t border-red-900/40 pt-8">
            <h2 class="text-sm font-semibold text-red-400">{{ __('Remove module quiz') }}</h2>
            <form method="POST" action="{{ route('admin.quizzes.module.destroy', [$course, $module]) }}" class="mt-4"
                onsubmit="return confirm({{ json_encode(__('Remove this module quiz and all attempts?')) }})">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-md border border-red-800 bg-red-950/30 px-4 py-2 text-sm text-red-300 hover:bg-red-950/50">{{ __('Delete module quiz') }}</button>
            </form>
        </section>
    </div>
@endsection
