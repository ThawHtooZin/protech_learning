@extends('layouts.admin')

@section('title', __('Edit question'))

@section('heading')
    {{ __('Edit question #:id', ['id' => $question->id]) }}
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-4">
        @if($lessonQuizReturn)
            <a href="{{ route('admin.quizzes.lesson.edit', [$lessonQuizReturn['course'], $lessonQuizReturn['module'], $lessonQuizReturn['lesson']]) }}" class="text-sm text-emerald-400 hover:underline">← {{ __('Back to lesson quiz') }}</a>
        @else
            <a href="{{ route('admin.questions.index') }}" class="text-sm text-emerald-400 hover:underline">← {{ __('Back to question bank') }}</a>
        @endif
    </div>

    @if($lessonQuizReturn)
        <div class="mb-6 rounded-lg border border-amber-800/50 bg-amber-950/30 px-4 py-3 text-sm text-amber-100/90">
            {{ __('You opened this question from a lesson quiz. Saving or deleting returns you to that quiz.') }}
        </div>
    @endif

    <div class="mx-auto max-w-2xl space-y-8">
        @include('admin.questions._form', ['question' => $question, 'lessonQuizReturn' => $lessonQuizReturn ?? null])

        <section class="border-t border-red-900/40 pt-8">
            <h2 class="text-sm font-semibold text-red-400">{{ __('Delete question') }}</h2>
            <p class="mt-2 max-w-xl text-sm text-zinc-500">{{ __('Removes this item from the bank and detaches it from all quizzes. Existing learner attempts may reference old option IDs.') }}</p>
            <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" class="mt-4"
                onsubmit="return confirm({{ json_encode(__('Remove this question permanently?')) }})">
                @csrf
                @method('DELETE')
                @if($lessonQuizReturn)
                    <input type="hidden" name="return_context" value="lesson_quiz">
                    <input type="hidden" name="return_course_id" value="{{ $lessonQuizReturn['course']->id }}">
                    <input type="hidden" name="return_module_id" value="{{ $lessonQuizReturn['module']->id }}">
                    <input type="hidden" name="return_lesson_id" value="{{ $lessonQuizReturn['lesson']->id }}">
                @endif
                <button type="submit" class="rounded-md border border-red-800 bg-red-950/30 px-4 py-2 text-sm text-red-300 hover:bg-red-950/50">{{ __('Delete question') }}</button>
            </form>
        </section>
    </div>
@endsection
