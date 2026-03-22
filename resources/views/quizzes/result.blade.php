@extends('layouts.learn')

@section('title', __('Quiz result'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-white">{{ $quiz->title }}</h1>
        <p class="mt-4 text-lg {{ $attempt->passed ? 'text-emerald-400' : 'text-amber-400' }}">
            {{ $attempt->passed ? __('Passed') : __('Not passed') }} — {{ $attempt->score_percent }}%
        </p>
        <a href="{{ route('courses.show', $course) }}" class="mt-6 inline-block text-emerald-400 hover:underline">{{ __('Back to course') }}</a>
    </div>
@endsection
