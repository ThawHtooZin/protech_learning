@extends('layouts.learn')

@section('title', __('All courses'))

@section('content')
    @php
        $ordered = $courses->sortBy('id')->values();
        $featuredId = $ordered->first()?->id;
        $filter = request('filter', 'all');
        $filters = collect(['all' => __('All')]);
        // Lightweight topic chips from distinct title tokens (no separate tags table).
        foreach ($ordered as $course) {
            if (preg_match('/\b(HTML|CSS|JS|JavaScript|PHP|Laravel)\b/i', $course->title, $m)) {
                $key = strtolower($m[1] === 'JavaScript' ? 'js' : $m[1]);
                $label = strtoupper($key === 'js' ? 'JS' : $key);
                $filters[$key] = $label;
            }
        }
        $filtered = $ordered->when($filter !== 'all', function ($collection) use ($filter) {
            return $collection->filter(function ($course) use ($filter) {
                $needle = $filter === 'js' ? 'javascript|js' : preg_quote($filter, '/');

                return preg_match('/\b('.$needle.')\b/i', $course->title) === 1;
            })->values();
        });
    @endphp

    <header class="mb-10">
        <p class="font-mono text-xs text-zinc-500">
            <a href="{{ route('home') }}" class="hover:text-emerald-700">{{ __('Home') }}</a>
            <span class="mx-1.5 text-zinc-300">/</span>
            <span class="text-zinc-700">{{ __('Courses') }}</span>
        </p>
        <h1 class="mt-4 text-4xl font-bold tracking-tight text-zinc-900 sm:text-[2.75rem]">{{ __('All courses') }}</h1>
        <p class="mt-3 max-w-2xl text-[15px] leading-relaxed text-zinc-600">
            {{ __('Pick a course and follow the steps in order. Each lesson is a short video.') }}
        </p>
    </header>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach($filters as $key => $label)
                <a href="{{ route('courses.index', $key === 'all' ? [] : ['filter' => $key]) }}"
                    class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $filter === $key ? 'bg-emerald-600 text-white' : 'border border-zinc-300 bg-white text-zinc-700 hover:border-emerald-300 hover:text-emerald-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <p class="text-sm text-zinc-500">
            {{ trans_choice(':count course|:count courses', $filtered->count(), ['count' => $filtered->count()]) }}
        </p>
    </div>

    <div class="space-y-3">
        @forelse($filtered as $course)
            @php
                $lessonCount = $course->modules->sum(fn ($m) => $m->lessons->count());
                $minutes = (int) ceil($course->modules->flatMap->lessons->sum('duration_seconds') / 60);
                $coverUrl = $course->coverUrl();
                $stepNumber = $ordered->search(fn ($c) => $c->id === $course->id) + 1;
                $stepLabel = $course->id === $featuredId ? __('Start here') : __('Step :n', ['n' => $stepNumber]);
                $hasAccess = in_array($course->id, $accessIds, true);
                $cta = $hasAccess ? __('Start course') : __('View course');
                $excerpt = $course->excerpt(120);
            @endphp
            <a href="{{ route('courses.show', $course) }}"
                class="group flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white transition hover:border-emerald-300 hover:shadow-md hover:shadow-emerald-900/5 sm:flex-row sm:items-stretch">
                <div class="flex aspect-[16/10] w-full shrink-0 items-center justify-center overflow-hidden bg-zinc-100 sm:aspect-auto sm:w-40 lg:w-44">
                    @if($coverUrl)
                        <img src="{{ $coverUrl }}" alt="" class="h-full w-full object-cover">
                    @else
                        <span class="px-4 text-center text-base font-semibold leading-snug text-emerald-800 sm:text-sm lg:text-base">
                            {{ $course->title }}
                        </span>
                    @endif
                </div>

                <div class="flex min-w-0 flex-1 flex-col justify-center gap-2 px-5 py-5 sm:px-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">{{ $stepLabel }}</span>
                        @if($course->isPrivate())
                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-600">{{ __('Private') }}</span>
                        @elseif($course->price_mmk !== null)
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">{{ number_format($course->price_mmk) }} {{ __('MMK') }}</span>
                        @endif
                        @auth
                            @if($hasAccess)
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">{{ __('Access') }}</span>
                            @endif
                        @endauth
                    </div>
                    <h2 class="text-lg font-semibold tracking-tight text-zinc-900 group-hover:text-emerald-700 sm:text-xl">{{ $course->title }}</h2>
                    @if($excerpt)
                        <p class="line-clamp-2 text-sm leading-relaxed text-zinc-600">{{ $excerpt }}</p>
                    @endif
                    <p class="text-xs text-zinc-500">
                        {{ trans_choice(':count episode|:count episodes', $lessonCount, ['count' => $lessonCount]) }}
                        @if($minutes > 0)
                            · {{ $minutes }} {{ __('min') }}
                        @endif
                    </p>
                </div>

                <div class="flex items-center px-5 pb-5 sm:px-6 sm:pb-0 sm:pr-6">
                    <span class="inline-flex w-full items-center justify-center gap-1.5 whitespace-nowrap rounded-full border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-800 transition group-hover:border-emerald-600 group-hover:bg-emerald-600 group-hover:text-white sm:w-auto">
                        {{ $cta }}
                        <span aria-hidden="true">→</span>
                    </span>
                </div>
            </a>
        @empty
            <p class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-10 text-center text-sm text-zinc-500">
                {{ __('No courses published yet.') }}
            </p>
        @endforelse
    </div>
@endsection
