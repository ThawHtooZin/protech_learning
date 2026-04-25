@extends('layouts.admin')

@section('title', __('Edit lesson quiz'))

@section('heading')
    {{ __('Edit lesson quiz') }}: {{ $lesson->title }}
@endsection

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <nav class="text-sm text-zinc-500">
            <a href="{{ route('admin.courses.edit', $course) }}" class="text-emerald-400 hover:underline">{{ $course->title }}</a>
            <span class="text-zinc-600">/</span>
            <span class="text-zinc-400">{{ $module->title }}</span>
            <span class="text-zinc-600">/</span>
            <span class="text-white">{{ $lesson->title }}</span>
        </nav>

        <dl class="grid gap-3 rounded-lg border border-zinc-800 bg-zinc-900/40 p-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-zinc-500">{{ __('Quiz ID') }}</dt>
                <dd class="mt-0.5 font-mono text-zinc-200">{{ $quiz->id }}</dd>
            </div>
            <div>
                <dt class="text-zinc-500">{{ __('Questions') }}</dt>
                <dd class="mt-0.5 text-zinc-200">{{ $quiz->questions->count() }}</dd>
            </div>
            <div>
                <dt class="text-zinc-500">{{ __('Learner attempts (all time)') }}</dt>
                <dd class="mt-0.5 text-zinc-200">{{ $quiz->attempts_count }}</dd>
            </div>
            <div>
                <dt class="text-zinc-500">{{ __('Last updated') }}</dt>
                <dd class="mt-0.5 text-zinc-200">{{ $quiz->updated_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</dd>
            </div>
        </dl>

        @include('admin.quizzes.partials.lesson-quiz-builder', [
            'formAction' => route('admin.quizzes.lesson.update', [$course, $module, $lesson]),
            'httpMethod' => 'PUT',
            'submitLabel' => __('Save changes'),
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
            'quiz' => $quiz,
            'questionBank' => $questionBank,
            'technologies' => $technologies,
            'topics' => $topics,
            'initialQuestionIds' => $initialQuestionIds,
        ])

        <section class="border-t border-red-900/40 pt-8">
            <h2 class="text-sm font-semibold text-red-400">{{ __('Remove lesson quiz') }}</h2>
            <p class="mt-2 max-w-2xl text-sm text-zinc-500">
                {{ __('Deletes this quiz and all learner attempts. Lesson completion flags for this video are reset so learners must take the new quiz if you add one again.') }}
            </p>
            <form method="POST" action="{{ route('admin.quizzes.lesson.destroy', [$course, $module, $lesson]) }}" class="mt-4"
                onsubmit="return confirm({{ json_encode(__('Remove this lesson quiz and all attempts? Completion for this lesson will be reset.')) }})">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-md border border-red-800 bg-red-950/30 px-4 py-2 text-sm text-red-300 hover:bg-red-950/50">{{ __('Delete lesson quiz') }}</button>
            </form>
        </section>
    </div>
@endsection
