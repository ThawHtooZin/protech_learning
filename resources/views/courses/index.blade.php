@extends('layouts.learn')

@section('title', __('Browse'))

@section('content')
    <div class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight text-zinc-900">{{ __('Library') }}</h1>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($courses as $course)
            @php
                $lessonCount = $course->orderedLessons()->count();
                $minutes = (int) ceil($course->totalDurationSeconds() / 60);
                $coverUrl = $course->coverUrl();
                $excerpt = $course->excerpt();
            @endphp
            <a href="{{ route('courses.show', $course) }}" class="group flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-panel transition hover:border-zinc-300 hover:bg-rail">
                @if($coverUrl)
                    <div class="aspect-[2/1] overflow-hidden bg-zinc-100">
                        <img src="{{ $coverUrl }}" alt="" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                    </div>
                @endif
                <div class="flex flex-1 flex-col p-6">
                <div class="flex items-start justify-between gap-3">
                    @unless($coverUrl)
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-sm font-bold text-zinc-900">{{ \Illuminate\Support\Str::substr($course->title, 0, 1) }}</span>
                    @endunless
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        @if($course->isPrivate())
                            <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 ring-1 ring-zinc-300">{{ __('Private') }}</span>
                        @elseif($course->price_mmk !== null)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">{{ number_format($course->price_mmk) }} {{ __('MMK') }}</span>
                        @endif
                        @auth
                            @if(in_array($course->id, $accessIds, true))
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">{{ __('Access') }}</span>
                            @endif
                        @endauth
                    </div>
                </div>
                <h2 class="mt-4 text-lg font-semibold text-zinc-900 group-hover:text-emerald-700">{{ $course->title }}</h2>
                @if($excerpt)
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-zinc-500">{{ $excerpt }}</p>
                @endif
                <p class="mt-4 text-xs text-zinc-500">
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
@endsection
