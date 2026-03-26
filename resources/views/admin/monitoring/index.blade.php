@extends('layouts.admin')

@section('title', __('Monitoring'))
@section('heading', __('Study monitoring'))

@section('content')
    @include('admin.monitoring._tabs')
    <form method="GET" action="{{ route('admin.monitoring.index') }}" class="mb-6 grid gap-3 rounded-xl border border-zinc-800 bg-zinc-900/40 p-4 md:grid-cols-5">
        <div>
            <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('User') }}</label>
            <select name="user_id" class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                <option value="">{{ __('All') }}</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('Course') }}</label>
            <select name="course_id" class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                <option value="">{{ __('All') }}</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" @selected((string) request('course_id') === (string) $c->id)>{{ $c->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('Event') }}</label>
            <select name="event_type" class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                <option value="">{{ __('All') }}</option>
                @foreach($eventTypes as $t)
                    <option value="{{ $t }}" @selected((string) request('event_type') === (string) $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('From') }}</label>
            <input type="date" name="from" value="{{ request('from') }}" class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white" />
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('To') }}</label>
            <input type="date" name="to" value="{{ request('to') }}" class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white" />
        </div>
        <div class="md:col-span-5 flex flex-wrap items-center justify-between gap-3">
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('Filter') }}
            </button>
            <a href="{{ route('admin.monitoring.index') }}" class="text-sm text-zinc-400 hover:text-white">{{ __('Reset') }}</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/40">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-800 text-xs uppercase tracking-wider text-zinc-500">
                <tr>
                    <th class="px-5 py-3">{{ __('When') }}</th>
                    <th class="px-5 py-3">{{ __('User') }}</th>
                    <th class="px-5 py-3">{{ __('Event') }}</th>
                    <th class="px-5 py-3">{{ __('Context') }}</th>
                    <th class="px-5 py-3">{{ __('Details') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @foreach($events as $e)
                    <tr class="hover:bg-zinc-800/30">
                        <td class="px-5 py-3 text-zinc-400">
                            {{ $e->occurred_at?->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-5 py-3">
                            @if($e->user)
                                <a href="{{ route('admin.monitoring.user', $e->user) }}" class="font-medium text-white hover:text-emerald-300">
                                    {{ $e->user->profile->display_name ?? $e->user->name }}
                                </a>
                                <div class="text-xs text-zinc-500">{{ $e->user->email }}</div>
                            @else
                                <div class="text-zinc-500">{{ __('Unknown user') }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-zinc-300">{{ $e->event_type }}</td>
                        <td class="px-5 py-3 text-zinc-400">
                            @if($e->course)
                                <div class="text-white">{{ $e->course->title }}</div>
                            @endif
                            @if($e->lesson)
                                <div class="text-xs text-zinc-500">{{ __('Lesson:') }} {{ $e->lesson->title }}</div>
                            @endif
                            @if($e->quiz)
                                <div class="text-xs text-zinc-500">{{ __('Quiz:') }} {{ $e->quiz->title }}</div>
                            @endif
                            @if(is_array($e->meta) && isset($e->meta['forum_category_name']))
                                <div class="text-xs text-zinc-500">{{ __('Forum:') }} {{ $e->meta['forum_category_name'] }}</div>
                            @endif
                            @if(is_array($e->meta) && isset($e->meta['thread_title']))
                                <div class="text-xs text-zinc-500">{{ __('Thread:') }} {{ $e->meta['thread_title'] }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-zinc-500">
                            @if(is_array($e->meta))
                                @if(isset($e->meta['parent_post_id']) && $e->meta['parent_post_id'])
                                    <div>{{ __('Reply to post:') }} <span class="text-zinc-300">#{{ $e->meta['parent_post_id'] }}</span></div>
                                @endif
                                @if(isset($e->meta['score_percent']))
                                    <div>{{ __('Score:') }} <span class="text-zinc-300">{{ $e->meta['score_percent'] }}%</span></div>
                                @endif
                                @if(array_key_exists('passed', $e->meta))
                                    <div>{{ __('Passed:') }} <span class="text-zinc-300">{{ $e->meta['passed'] ? 'yes' : 'no' }}</span></div>
                                @endif
                                @if(isset($e->meta['duration_seconds']))
                                    <div>{{ __('Duration:') }} <span class="text-zinc-300">{{ $e->meta['duration_seconds'] }}s</span></div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $events->links() }}
    </div>
@endsection

