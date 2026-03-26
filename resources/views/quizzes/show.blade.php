@extends('layouts.learn')

@section('title', $quiz->title)

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-white">{{ $quiz->title }}</h1>
        @if($quiz->lesson_id)
            <p class="mt-2 text-sm text-zinc-500">{{ __('Answer the questions and submit to unlock your progress and the next lesson. You’ll see how many you got right; there’s no pass/fail grade.') }}</p>
        @else
            <p class="mt-2 text-sm text-zinc-500">{{ __('Module recap: you need at least :percent% correct to unlock the next module.', ['percent' => $quiz->pass_threshold_percent]) }}</p>
        @endif

        <form method="POST" action="{{ route('quizzes.store', $quiz) }}" class="mt-8 space-y-8">
            @csrf
            @foreach($quiz->questions as $q)
                <fieldset class="rounded-lg border border-zinc-800 bg-zinc-900/50 p-4">
                    <legend class="px-1 text-sm font-medium text-white">{{ __('Question') }} {{ $loop->iteration }}</legend>
                    <p class="mt-2 text-zinc-200">{{ $q->body }}</p>
                    <ul class="mt-4 space-y-2">
                        @foreach($q->options as $opt)
                            <li>
                                <label class="flex cursor-pointer items-start gap-2 text-sm text-zinc-300">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt->id }}" class="mt-1" @if($loop->first) required @endif>
                                    <span>{{ $opt->body }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </fieldset>
            @endforeach
            <button type="submit" class="rounded-md bg-emerald-600 px-6 py-2 font-medium text-white hover:bg-emerald-500">{{ __('Submit answers') }}</button>
        </form>
    </div>
@endsection
