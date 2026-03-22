@extends('layouts.admin')

@section('title', __('Lesson quiz'))

@section('heading')
    {{ __('Lesson quiz') }}: {{ $lesson->title }}
@endsection

@section('content')
    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('admin.quizzes.lesson.store', [$course, $module, $lesson]) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Title') }}</label>
                <input type="text" name="title" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Pass threshold %') }}</label>
                <input type="number" name="pass_threshold_percent" value="70" min="1" max="100" required class="mt-1 w-32 rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <span class="block text-sm text-zinc-400">{{ __('Questions') }}</span>
                <div class="mt-2 max-h-64 space-y-1 overflow-y-auto rounded-md border border-zinc-800 p-2">
                    @foreach($questions as $q)
                        <label class="flex items-start gap-2 text-sm text-zinc-300">
                            <input type="checkbox" name="question_ids[]" value="{{ $q->id }}">
                            <span>{{ $q->technology }} / {{ $q->topic }} — {{ \Illuminate\Support\Str::limit($q->body, 80) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="rounded-md bg-emerald-600 px-6 py-2 text-white">{{ __('Save quiz') }}</button>
        </form>
    </div>
@endsection
