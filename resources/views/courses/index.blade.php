@extends('layouts.learn')

@section('title', __('Browse'))

@section('content')
    @php
        // First course ever created (lowest id), not alphabetical.
        $featured = $courses->sortBy('id')->first();
        $featuredLessons = $featured
            ? $featured->modules->flatMap(fn ($m) => $m->lessons)->values()
            : collect();
        $featuredFirstLesson = $featuredLessons->first();
        $featuredStartUrl = $featured
            ? ($featuredFirstLesson && in_array($featured->id, $accessIds, true)
                ? route('lessons.show', $featuredFirstLesson)
                : route('courses.show', $featured))
            : route('courses.index').'#library';
        $featuredCta = $featured?->title ?? __('Browse the library');
    @endphp

    {{-- Hero — mockup layout, our light theme colors --}}
    <section class="grid items-center gap-10 pb-16 pt-6 md:grid-cols-2 md:gap-12 md:pb-24 md:pt-10">
        <div class="max-w-xl">
            <p class="inline-flex items-center gap-2 rounded-full border border-zinc-300 bg-white px-3.5 py-1.5 font-mono text-[11px] font-medium text-zinc-600">
                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"></span>
                {{ __('Learn at your pace.') }}
                <span class="text-zinc-300">·</span>
                <span class="tracking-wide text-zinc-500">EN / မြန်မာ</span>
            </p>

            <h1 class="mt-6 text-[2.75rem] font-bold leading-[1.08] tracking-tight text-zinc-900 sm:text-5xl lg:text-[3.25rem]">
                {{ __('Build the web. One episode at a time.') }}
            </h1>

            <p class="mt-5 text-base leading-relaxed text-zinc-600 sm:text-lg">
                {{ __('Short video lessons, clear progress, and a forum when you get stuck.') }}
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ $featuredStartUrl }}"
                    class="inline-flex items-center gap-2.5 whitespace-nowrap rounded-lg bg-emerald-600 px-5 py-3 text-[15px] font-semibold leading-none text-white shadow-sm hover:bg-emerald-500">
                    <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    <span>{{ $featuredCta }}</span>
                </a>
                <a href="#library"
                    class="inline-flex items-center whitespace-nowrap rounded-lg border border-zinc-300 bg-white px-5 py-3 text-[15px] font-semibold leading-none text-zinc-800 hover:bg-zinc-50">
                    {{ __('Browse the library') }}
                </a>
            </div>
        </div>

        {{-- Code + Preview window (stacked, light mode) --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl shadow-zinc-900/8">
            <div class="relative flex h-10 items-center border-b border-zinc-200 bg-zinc-50 px-4">
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-[#ff5f57]"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-[#febc2e]"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-[#28c840]"></span>
                </div>
                <span class="pointer-events-none absolute inset-x-0 text-center font-mono text-[11px] text-zinc-500">index.html</span>
            </div>
            <pre class="m-0 overflow-x-auto bg-white p-5 font-mono text-[13px] leading-7"><code><span class="text-zinc-400">&lt;!DOCTYPE html&gt;</span>
<span class="text-sky-600">&lt;html&gt;</span>
  <span class="text-sky-600">&lt;body&gt;</span>
    <span class="text-sky-600">&lt;h1&gt;</span><span class="text-zinc-900">Hello, world!</span><span class="text-sky-600">&lt;/h1&gt;</span>
    <span class="text-sky-600">&lt;p&gt;</span><span class="text-zinc-900">My first page.</span><span class="text-sky-600">&lt;/p&gt;</span>
  <span class="text-sky-600">&lt;/body&gt;</span>
<span class="text-sky-600">&lt;/html&gt;</span></code></pre>
            <div class="border-t border-zinc-200 bg-white p-5">
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-400">{{ __('Preview') }}</p>
                <h2 class="mt-4 text-[1.75rem] font-bold leading-tight tracking-tight text-zinc-900">Hello, world!</h2>
                <p class="mt-2 text-[15px] text-zinc-500">My first page.</p>
            </div>
        </div>
    </section>

    {{-- Library --}}
    <section id="library" class="scroll-mt-24 border-t border-zinc-200 pt-14 pb-16">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-700">{{ __('The library') }}</p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-zinc-900 sm:text-3xl">
                    {{ __('Pick a course, follow the path') }}
                </h2>
            </div>
            <a href="#library" class="text-sm font-medium text-emerald-700 hover:text-emerald-600">
                {{ __('See all courses') }} →
            </a>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($courses as $index => $course)
                @php
                    $lessonCount = $course->modules->sum(fn ($m) => $m->lessons->count());
                    $minutes = (int) ceil($course->modules->flatMap->lessons->sum('duration_seconds') / 60);
                    $coverUrl = $course->coverUrl();
                    $stepLabel = $course->id === ($featured?->id) ? __('Start here') : __('Step :n', ['n' => $index + 1]);
                @endphp
                <a href="{{ route('courses.show', $course) }}"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-panel transition hover:border-emerald-300 hover:shadow-md hover:shadow-emerald-900/5">
                    <div class="aspect-[16/10] overflow-hidden bg-zinc-100">
                        @if($coverUrl)
                            <img src="{{ $coverUrl }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                        @else
                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-zinc-100 to-emerald-50 px-5 py-6">
                                <span class="max-w-full text-center text-lg font-semibold leading-snug text-emerald-800 sm:text-xl">
                                    {{ $course->title }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700">{{ $stepLabel }}</span>
                            @if($course->isPrivate())
                                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-600">{{ __('Private') }}</span>
                            @elseif($course->price_mmk !== null)
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">{{ number_format($course->price_mmk) }} {{ __('MMK') }}</span>
                            @endif
                            @auth
                                @if(in_array($course->id, $accessIds, true))
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">{{ __('Access') }}</span>
                                @endif
                            @endauth
                        </div>
                        <h3 class="mt-2 text-lg font-semibold text-zinc-900 group-hover:text-emerald-700">{{ $course->title }}</h3>
                        <p class="mt-auto pt-4 text-xs text-zinc-500">
                            {{ trans_choice(':count episode|:count episodes', $lessonCount, ['count' => $lessonCount]) }}
                            @if($minutes > 0)
                                · {{ $minutes }} {{ __('min') }}
                            @endif
                        </p>
                    </div>
                </a>
            @empty
                <p class="col-span-full text-zinc-500">{{ __('No courses published yet.') }}</p>
            @endforelse
        </div>
    </section>

    {{-- How it works --}}
    <section class="border-t border-zinc-200 py-16">
        <h2 class="text-2xl font-bold tracking-tight text-zinc-900 sm:text-3xl">{{ __('How a lesson works') }}</h2>
        <div class="mt-10 grid gap-8 sm:grid-cols-3">
            <div>
                <p class="font-mono text-sm font-bold text-emerald-600">01</p>
                <h3 class="mt-2 text-lg font-semibold text-zinc-900">{{ __('Watch') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-zinc-600">{{ __('Short video lessons you can finish in a few minutes.') }}</p>
            </div>
            <div>
                <p class="font-mono text-sm font-bold text-emerald-600">02</p>
                <h3 class="mt-2 text-lg font-semibold text-zinc-900">{{ __('Track progress') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-zinc-600">{{ __('Mark lessons complete as you go and see how far you have come.') }}</p>
            </div>
            <div>
                <p class="font-mono text-sm font-bold text-emerald-600">03</p>
                <h3 class="mt-2 text-lg font-semibold text-zinc-900">{{ __('Ask the forum') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-zinc-600">{{ __('Stuck? Post a question and learn with the community.') }}</p>
            </div>
        </div>
    </section>

@endsection
