@extends('layouts.learn')

@section('title', $course->title)

@section('content')
    <div
        x-data="{ buyOpen: {{ $errors->has('slip') ? 'true' : 'false' }} }"
        @keydown.escape.window="buyOpen = false"
    >
        @if($course->coverUrl())
            <div class="mb-8 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100">
                <img src="{{ $course->coverUrl() }}" alt="" class="max-h-80 w-full object-cover">
            </div>
        @endif

        <div class="mb-10 border-b border-zinc-200 pb-8">
            <a href="{{ route('courses.index') }}" class="text-sm text-zinc-500 hover:text-emerald-700">{{ __('Library') }}</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-900">{{ $course->title }}</h1>

            @if($course->description)
                <div class="course-prose mt-4 max-w-3xl text-zinc-700">
                    {!! $course->description !!}
                </div>
            @endif

            <p class="mt-4 text-sm text-zinc-500">
                {{ trans_choice(':count episode|:count episodes', $lessonCount, ['count' => $lessonCount]) }}
                @if($totalMinutes > 0)
                    · {{ $totalMinutes }} {{ __('min') }}
                @endif
                ·
                @if($course->isPrivate())
                    <span class="font-medium text-zinc-700">{{ __('Private') }}</span>
                @elseif($course->price_mmk !== null)
                    <span class="font-medium text-zinc-700">{{ number_format($course->price_mmk) }} {{ __('MMK') }}</span>
                @endif
            </p>

            <div class="mt-6 flex flex-wrap items-center gap-4">
                @if($hasAccess)
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-1.5 text-sm text-emerald-700 ring-1 ring-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        {{ __('Access') }}
                        @if(auth()->user() && ! auth()->user()->isAdmin())
                            · {{ $completion }}% {{ __('complete') }}
                        @endif
                    </span>
                @elseif($course->isPublic())
                    @auth
                        @if($pendingPurchase)
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-4 py-1.5 text-sm text-amber-800 ring-1 ring-amber-200">
                                {{ __('Purchase pending.') }}
                            </span>
                        @else
                            <button type="button" @click="buyOpen = true" class="inline-flex rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Buy') }}</button>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="inline-flex rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Sign in to buy') }}</a>
                    @endauth
                @endif
            </div>
        </div>

        @foreach($course->modules as $module)
            <section class="mb-10">
                <h2 class="mb-4 text-xs font-bold uppercase tracking-widest text-zinc-500">{{ $module->title }}</h2>
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                    @forelse($module->lessons as $lesson)
                        @if($hasAccess)
                            <a href="{{ route('lessons.show', $lesson) }}" class="flex items-center gap-4 border-b border-zinc-200 px-4 py-4 last:border-0 hover:bg-zinc-50 sm:px-5">
                                @if(isset($completedLessonIds) && $completedLessonIds->contains($lesson->id))
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-emerald-300 bg-emerald-50 text-emerald-700" title="{{ __('Completed') }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                @else
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-zinc-300 bg-zinc-100 text-zinc-600">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-zinc-900">{{ $lesson->title }}</p>
                                    @if($lesson->duration_seconds)
                                        <p class="text-xs text-zinc-500">{{ (int) ceil($lesson->duration_seconds / 60) }} {{ __('min') }}</p>
                                    @endif
                                </div>
                                <svg class="h-5 w-5 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @else
                            <div class="flex items-center gap-4 border-b border-zinc-200 px-4 py-4 last:border-0 sm:px-5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-zinc-200 bg-zinc-50 text-zinc-400" title="{{ __('Locked') }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-zinc-700">{{ $lesson->title }}</p>
                                    @if($lesson->duration_seconds)
                                        <p class="text-xs text-zinc-500">{{ (int) ceil($lesson->duration_seconds / 60) }} {{ __('min') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="px-4 py-6 text-sm text-zinc-500">{{ __('No lessons yet.') }}</p>
                    @endforelse
                </div>
            </section>
        @endforeach

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
