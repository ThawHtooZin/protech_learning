@extends('layouts.learn')

@section('title', __('Dashboard'))

@section('content')
    <div class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight text-white">{{ __('Continue learning') }}</h1>
        <p class="mt-2 text-zinc-400">{{ __('Jump back into a course you have started.') }}</p>
    </div>

    @if($courses->isEmpty())
        <div class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-900/30 px-8 py-16 text-center">
            <p class="text-zinc-400">{{ __('You are not enrolled in any course yet.') }}</p>
            <a href="{{ route('courses.index') }}" class="mt-4 inline-flex rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Browse the library') }}</a>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-2">
            @foreach($courses as $course)
                @php $pct = $progress[$course->id] ?? 0; @endphp
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">
                                <a href="{{ route('courses.show', $course) }}" class="hover:text-emerald-400">{{ $course->title }}</a>
                            </h2>
                            <p class="mt-1 text-sm text-zinc-500">{{ $pct }}% {{ __('complete') }}</p>
                        </div>
                        <a href="{{ route('courses.show', $course) }}" class="shrink-0 rounded-lg bg-zinc-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-zinc-700">{{ __('Open') }}</a>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-zinc-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 to-teal-500 transition-all" style="width: {{ min(100, $pct) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
