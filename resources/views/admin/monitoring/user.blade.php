@extends('layouts.admin')

@section('title', __('Monitoring'))
@section('heading', __('Study monitoring'))

@section('content')
    @include('admin.monitoring._tabs')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-zinc-500">{{ __('User') }}</p>
            <p class="text-lg font-semibold text-white">{{ $user->name }} <span class="text-sm text-zinc-500">({{ $user->email }})</span></p>
        </div>
        <a href="{{ route('admin.users.show', $user) }}" class="rounded-lg border border-zinc-700 px-4 py-2 text-sm text-zinc-200 hover:bg-zinc-800">
            {{ __('Back to user') }}
        </a>
    </div>

    <div class="space-y-3">
        @foreach($events as $e)
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/40 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="text-sm text-zinc-400">{{ $e->occurred_at?->format('Y-m-d H:i:s') }}</div>
                    <div class="font-mono text-xs text-zinc-300">{{ $e->event_type }}</div>
                </div>
                <div class="mt-2 text-sm text-zinc-300">
                    @if($e->course)
                        <div><span class="text-zinc-500">{{ __('Course:') }}</span> {{ $e->course->title }}</div>
                    @endif
                    @if($e->lesson)
                        <div><span class="text-zinc-500">{{ __('Lesson:') }}</span> {{ $e->lesson->title }}</div>
                    @endif
                    @if($e->quiz)
                        <div><span class="text-zinc-500">{{ __('Quiz:') }}</span> {{ $e->quiz->title }}</div>
                    @endif
                    @if(is_array($e->meta) && isset($e->meta['forum_category_name']))
                        <div><span class="text-zinc-500">{{ __('Forum:') }}</span> {{ $e->meta['forum_category_name'] }}</div>
                    @endif
                    @if(is_array($e->meta) && isset($e->meta['thread_title']))
                        <div><span class="text-zinc-500">{{ __('Thread:') }}</span> {{ $e->meta['thread_title'] }}</div>
                    @endif
                </div>
                @if(is_array($e->meta) && ! empty($e->meta))
                    <div class="mt-3 grid gap-2 sm:grid-cols-3">
                        @foreach($e->meta as $k => $v)
                            <div class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">{{ $k }}</div>
                                <div class="mt-1 text-sm text-zinc-200">
                                    {{ is_bool($v) ? ($v ? 'true' : 'false') : (is_scalar($v) ? $v : json_encode($v)) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $events->links() }}
    </div>
@endsection

