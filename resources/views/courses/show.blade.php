@extends('layouts.learn')

@section('title', $course->title)

@section('content')
    @php
        $allLessons = $course->modules->flatMap(fn ($m) => $m->lessons)->values();
        $completedCount = $completedLessonIds->count();
        $nextLesson = $hasAccess
            ? ($allLessons->first(fn ($l) => ! $completedLessonIds->contains($l->id)) ?? $allLessons->first())
            : $allLessons->first();
        $moduleCount = $course->modules->count();
        $progressPct = $lessonCount > 0 ? (int) round(($completedCount / $lessonCount) * 100) : 0;
        $ctaLabel = $nextLesson
            ? ($completedCount > 0
                ? __('Continue :title', ['title' => $nextLesson->title])
                : __('Start :title', ['title' => $nextLesson->title]))
            : null;
    @endphp

    <div
        x-data="{ buyOpen: {{ $errors->has('slip') ? 'true' : 'false' }} }"
        @keydown.escape.window="buyOpen = false"
    >
        {{-- Hero --}}
        <header class="mb-12 pb-2">
            <p class="font-mono text-xs text-zinc-500">
                <a href="{{ route('courses.index') }}" class="hover:text-emerald-700">{{ __('Library') }}</a>
                <span class="mx-1.5 text-zinc-300">/</span>
                <span class="text-zinc-700">{{ $course->title }}</span>
            </p>
            <h1 class="mt-4 text-4xl font-bold tracking-tight text-zinc-900 sm:text-[2.75rem]">{{ $course->title }}</h1>

            @if($course->description)
                <div class="course-prose mt-4 max-w-2xl text-[15px] leading-relaxed text-zinc-600">
                    {!! $course->description !!}
                </div>
            @endif

            <div class="mt-6 flex flex-wrap gap-2">
                <span class="rounded-full border border-zinc-300 bg-white px-3.5 py-1 text-xs font-medium text-zinc-700">
                    {{ trans_choice(':count episode|:count episodes', $lessonCount, ['count' => $lessonCount]) }}
                </span>
                @if($totalMinutes > 0)
                    <span class="rounded-full border border-zinc-300 bg-white px-3.5 py-1 text-xs font-medium text-zinc-700">
                        {{ $totalMinutes }} {{ __('min') }}
                    </span>
                @endif
                <span class="rounded-full border border-zinc-300 bg-white px-3.5 py-1 text-xs font-medium text-zinc-700">
                    {{ trans_choice(':count module|:count modules', $moduleCount, ['count' => $moduleCount]) }}
                </span>
                @if($course->isPrivate())
                    <span class="rounded-full border border-zinc-300 bg-zinc-50 px-3.5 py-1 text-xs font-medium text-zinc-600">{{ __('Private') }}</span>
                @elseif($course->price_mmk !== null)
                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1 text-xs font-medium text-emerald-700">
                        {{ number_format($course->price_mmk) }} {{ __('MMK') }}
                    </span>
                @endif
            </div>
        </header>

        <div class="grid gap-10 md:grid-cols-[minmax(0,1fr)_18rem] md:items-start md:gap-10 lg:grid-cols-[minmax(0,1fr)_19rem] lg:gap-12">
            {{-- Modules / lessons --}}
            <div class="min-w-0 space-y-4">
                @forelse($course->modules as $moduleIndex => $module)
                    <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white">
                        <div class="px-5 pb-2 pt-5">
                            <h2 class="flex items-baseline gap-3 text-[15px] font-semibold text-zinc-900">
                                <span class="font-mono text-sm font-bold text-emerald-600">{{ str_pad((string) ($moduleIndex + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                {{ $module->title }}
                            </h2>
                        </div>
                        <ul class="pb-2">
                            @forelse($module->lessons as $lesson)
                                <li>
                                    @if($hasAccess)
                                        <a href="{{ route('lessons.show', $lesson) }}"
                                            class="flex items-center gap-3 px-5 py-2.5 transition hover:bg-zinc-50">
                                            @if($completedLessonIds->contains($lesson->id))
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-emerald-300 bg-emerald-50 text-emerald-700" title="{{ __('Completed') }}">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                            @else
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-zinc-200 bg-zinc-50 text-zinc-500">
                                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                </span>
                                            @endif
                                            <span class="min-w-0 flex-1 truncate text-sm text-zinc-800">{{ $lesson->title }}</span>
                                            @if($lesson->duration_seconds)
                                                <span class="shrink-0 text-xs text-zinc-400">{{ (int) ceil($lesson->duration_seconds / 60) }} {{ __('min') }}</span>
                                            @endif
                                        </a>
                                    @else
                                        <div class="flex items-center gap-3 px-5 py-2.5">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-zinc-200 bg-zinc-50 text-zinc-400" title="{{ __('Locked') }}">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </span>
                                            <span class="min-w-0 flex-1 truncate text-sm text-zinc-600">{{ $lesson->title }}</span>
                                            @if($lesson->duration_seconds)
                                                <span class="shrink-0 text-xs text-zinc-400">{{ (int) ceil($lesson->duration_seconds / 60) }} {{ __('min') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </li>
                            @empty
                                <li class="px-5 py-6 text-sm text-zinc-500">{{ __('No lessons yet.') }}</li>
                            @endforelse
                        </ul>
                    </section>
                @empty
                    <p class="text-sm text-zinc-500">{{ __('No modules yet.') }}</p>
                @endforelse
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-4 md:sticky md:top-24">
                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    @if($course->coverUrl())
                        <div class="aspect-[16/10] bg-zinc-100">
                            <img src="{{ $course->coverUrl() }}" alt="" class="h-full w-full object-cover">
                        </div>
                    @else
                        <div class="flex aspect-[16/10] items-center justify-center bg-zinc-100">
                            <span class="font-mono text-2xl font-semibold text-sky-600">&lt;html&gt;</span>
                        </div>
                    @endif

                    <div class="p-5">
                        @if($hasAccess)
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-zinc-700">{{ __('Your progress') }}</span>
                                <span class="font-mono text-zinc-500">{{ $completedCount }} / {{ $lessonCount }}</span>
                            </div>
                            <div class="mt-2.5 h-1 overflow-hidden rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ min(100, max($progressPct > 0 ? $progressPct : 0, $completedCount > 0 ? 2 : 0)) }}%"></div>
                            </div>

                            @if($nextLesson && $ctaLabel)
                                <a href="{{ route('lessons.show', $nextLesson) }}"
                                    class="mt-5 flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-emerald-600 px-4 py-3 text-[15px] font-semibold leading-none text-white hover:bg-emerald-500">
                                    <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                                    <span class="truncate">{{ $ctaLabel }}</span>
                                </a>
                            @endif
                        @elseif($course->isPublic())
                            @auth
                                @if($pendingPurchase)
                                    <p class="rounded-lg bg-amber-50 px-3 py-2 text-center text-sm text-amber-800 ring-1 ring-amber-200">
                                        {{ __('Purchase pending.') }}
                                    </p>
                                @else
                                    <button type="button" @click="buyOpen = true"
                                        class="flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-3 text-[15px] font-semibold text-white hover:bg-emerald-500">
                                        {{ __('Buy') }}
                                        @if($course->price_mmk !== null)
                                            · {{ number_format($course->price_mmk) }} {{ __('MMK') }}
                                        @endif
                                    </button>
                                @endif
                            @else
                                <a href="{{ route('login') }}"
                                    class="flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-3 text-[15px] font-semibold text-white hover:bg-emerald-500">
                                    {{ __('Sign in to buy') }}
                                </a>
                            @endauth
                        @else
                            <p class="text-center text-sm text-zinc-600">{{ __('Private') }}</p>
                            <p class="mt-2 text-center text-xs text-zinc-500">{{ __('Access is granted by an admin.') }}</p>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-zinc-900">{{ __('Stuck on a lesson?') }}</h3>
                    <a href="{{ route('forums.index') }}" class="mt-2 inline-flex text-sm font-medium text-emerald-700 hover:text-emerald-600">
                        {{ __('Ask in the forum') }} →
                    </a>
                </div>
            </aside>
        </div>

        @auth
            @if($course->isPublic() && ! $hasAccess && ! $pendingPurchase)
                <div
                    x-show="buyOpen"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-900/40 p-4 backdrop-blur-[2px]"
                    @click.self="buyOpen = false"
                >
                    <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl shadow-zinc-900/10" @click.stop>
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="text-lg font-semibold text-zinc-900">{{ __('Buy') }}</h2>
                            <button type="button" class="text-zinc-400 hover:text-zinc-700" @click="buyOpen = false" aria-label="{{ __('Close') }}">&times;</button>
                        </div>

                        <div class="mt-4 space-y-4">
                            @forelse($bankAccounts as $bank)
                                <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm">
                                    <p class="font-semibold text-zinc-900">{{ $bank->name }}</p>
                                    <dl class="mt-2 space-y-1 text-zinc-700">
                                        <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('Account name') }}</dt><dd>{{ $bank->account_name }}</dd></div>
                                        <div class="flex justify-between gap-4"><dt class="text-zinc-500">{{ __('Account number') }}</dt><dd class="font-mono">{{ $bank->account_number }}</dd></div>
                                    </dl>
                                    @if($bank->note)
                                        <p class="mt-2 text-xs text-zinc-500">{{ $bank->note }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-zinc-500">{{ __('Payment details will be provided soon.') }}</p>
                            @endforelse

                            @if($course->price_mmk !== null)
                                <div class="flex justify-between gap-4 text-sm">
                                    <span class="text-zinc-500">{{ __('Amount') }}</span>
                                    <span class="font-semibold text-zinc-900">{{ number_format($course->price_mmk) }} {{ __('MMK') }}</span>
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('courses.purchase', $course) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm text-zinc-600">{{ __('Bank slip') }}</label>
                                <input type="file" name="slip" accept=".jpg,.jpeg,.png,.webp,.pdf" required class="mt-1 block w-full text-sm text-zinc-700">
                                @error('slip')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Submit slip') }}</button>
                        </form>
                    </div>
                </div>
            @endif
        @endauth
    </div>
@endsection
