@extends('layouts.admin')

@section('title', __('Monitoring'))
@section('heading', __('Forum activity logs'))

@section('content')
    @include('admin.monitoring._tabs')

    <form method="GET" action="{{ route('admin.monitoring.forums') }}" class="mb-6 grid gap-3 rounded-xl border border-zinc-200 bg-panel p-4 md:grid-cols-4">
        <div>
            <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('User') }}</label>
            <select name="user_id" class="mt-2 w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900">
                <option value="">{{ __('All') }}</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('Event') }}</label>
            <select name="event_type" class="mt-2 w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900">
                <option value="">{{ __('All') }}</option>
                @foreach($eventTypes as $t)
                    <option value="{{ $t }}" @selected((string) request('event_type') === (string) $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('From') }}</label>
            <input type="date" name="from" value="{{ request('from') }}" class="mt-2 w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900" />
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('To') }}</label>
            <input type="date" name="to" value="{{ request('to') }}" class="mt-2 w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900" />
        </div>
        <div class="md:col-span-4 flex flex-wrap items-center justify-between gap-3">
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('Filter') }}
            </button>
            <a href="{{ route('admin.monitoring.forums') }}" class="text-sm text-zinc-600 hover:text-zinc-900">{{ __('Reset') }}</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-panel">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-200 text-xs uppercase tracking-wider text-zinc-500">
                <tr>
                    <th class="px-5 py-3">{{ __('When') }}</th>
                    <th class="px-5 py-3">{{ __('User') }}</th>
                    <th class="px-5 py-3">{{ __('Event') }}</th>
                    <th class="px-5 py-3">{{ __('Thread') }}</th>
                    <th class="px-5 py-3">{{ __('Reply') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @foreach($logs as $l)
                    <tr class="hover:bg-zinc-100/30">
                        <td class="px-5 py-3 text-zinc-600">{{ $l->occurred_at?->format('Y-m-d H:i:s') }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-zinc-900">{{ $l->user->profile->display_name ?? $l->user->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $l->user->email }}</div>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-zinc-700">{{ $l->event_type }}</td>
                        <td class="px-5 py-3 text-zinc-600">
                            <div class="text-zinc-900">{{ $l->category->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $l->thread->title }}</div>
                            @if($l->forum_post_id)
                                <div class="text-xs text-zinc-600">post #{{ $l->forum_post_id }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-zinc-500">
                            @if($l->parent_post_id)
                                <div>{{ __('Reply to:') }} <span class="text-zinc-700">#{{ $l->parent_post_id }}</span></div>
                            @else
                                <div class="text-zinc-600">—</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
@endsection

