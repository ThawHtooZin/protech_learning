@extends('layouts.learn')

@section('title', __('Notifications'))

@section('content')
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ __('Notifications') }}</h1>
            @if($unreadCount > 0)
                <p class="mt-1 text-sm text-zinc-400">{{ $unreadCount }} {{ __('unread') }}</p>
            @else
                <p class="mt-1 text-sm text-zinc-500">{{ __('You’re all caught up.') }}</p>
            @endif
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-zinc-600 px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-800">{{ __('Mark all as read') }}</button>
            </form>
        @endif
    </div>

    <ul class="space-y-2">
        @forelse($notifications as $n)
            @php $data = is_array($n->data) ? $n->data : []; @endphp
            <li class="rounded-xl border px-4 py-4 transition {{ $n->read_at ? 'border-zinc-800/80 bg-zinc-900/30' : 'border-emerald-800/40 bg-emerald-950/20 ring-1 ring-emerald-900/30' }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        @if(!$n->read_at)
                            <span class="mb-2 inline-block rounded-full bg-emerald-600/90 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">{{ __('Unread') }}</span>
                        @endif
                        <p class="text-sm text-zinc-200">
                            @if(!empty($data['actor_name']))
                                <span class="font-medium text-white">{{ $data['actor_name'] }}</span>
                                <span class="text-zinc-500"> — </span>
                            @endif
                            {{ $data['message'] ?? __('Notification') }}
                        </p>
                        <p class="mt-2 text-xs text-zinc-500">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-2 sm:flex-row sm:items-center">
                        @if(isset($data['url']))
                            <a href="{{ route('notifications.read', $n->id) }}" class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-500">{{ __('Open') }}</a>
                        @endif
                    </div>
                </div>
            </li>
        @empty
            <li class="rounded-xl border border-zinc-800 bg-zinc-900/40 px-6 py-12 text-center text-zinc-500">{{ __('No notifications yet.') }}</li>
        @endforelse
    </ul>
    <div class="mt-8">{{ $notifications->links() }}</div>
@endsection
