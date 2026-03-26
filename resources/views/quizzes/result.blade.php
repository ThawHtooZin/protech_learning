@extends('layouts.learn')

@section('title', __('Quiz result'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-white">{{ $quiz->title }}</h1>

        @if($isLessonQuiz)
            <p class="mt-4 text-lg text-zinc-200">
                {{ __('You got :correct of :total correct.', ['correct' => $correctCount, 'total' => $totalQuestions]) }}
            </p>
            <p class="mt-2 text-sm text-zinc-500">{{ __('Submitting unlocks your progress and the next lesson.') }}</p>
            @if(isset($nextLesson) && $nextLesson)
                <a href="{{ route('lessons.show', $nextLesson) }}" class="mt-6 inline-flex rounded-md bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                    {{ __('Continue to next lesson') }}
                </a>
                <p class="mt-2 text-xs text-zinc-500">{{ __('Watch the video and take the lesson quiz to keep going.') }}</p>
            @endif
        @elseif($isModuleQuiz)
            <p class="mt-4 text-lg text-zinc-200">
                {{ __('You got :correct of :total correct.', ['correct' => $correctCount, 'total' => $totalQuestions]) }}
            </p>
            <p class="mt-2 text-lg {{ $attempt->passed ? 'text-emerald-400' : 'text-amber-400' }}">
                @if($attempt->passed)
                    {{ __('Score :percent% — you can continue to the next module.', ['percent' => $attempt->score_percent]) }}
                @else
                    {{ __('Score :percent% — you need at least :need% to unlock the next module.', ['percent' => $attempt->score_percent, 'need' => $quiz->pass_threshold_percent]) }}
                @endif
            </p>
        @else
            <p class="mt-4 text-lg text-zinc-200">
                {{ __('You got :correct of :total correct.', ['correct' => $correctCount, 'total' => $totalQuestions]) }}
            </p>
        @endif

        <a href="{{ route('courses.show', $course) }}" class="{{ isset($nextLesson) && $nextLesson ? 'mt-4' : 'mt-6' }} inline-block text-sm text-emerald-400/90 hover:underline">{{ __('Back to course') }}</a>
    </div>
@endsection
