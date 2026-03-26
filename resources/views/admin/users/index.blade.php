@extends('layouts.admin')

@section('title', __('Users'))
@section('heading', __('Users'))

@section('content')
    <div class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/40">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-800 text-xs uppercase tracking-wider text-zinc-500">
                <tr>
                    <th class="px-5 py-3">{{ __('Name') }}</th>
                    <th class="px-5 py-3">{{ __('Email') }}</th>
                    <th class="px-5 py-3">{{ __('Role') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @foreach($users as $user)
                    <tr class="hover:bg-zinc-800/30">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-white hover:text-emerald-300">
                                {{ $user->name }}
                            </a>
                            @if($user->profile)
                                <div class="text-xs text-zinc-500">/u/{{ $user->profile->handle }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-zinc-400">{{ $user->email }}</td>
                        <td class="px-5 py-3 text-zinc-400">{{ $user->role->label() }}</td>
                        <td class="px-5 py-3">
                            @if($user->approved_at)
                                <span class="inline-flex items-center rounded-full bg-emerald-950 px-2.5 py-0.5 text-xs font-medium text-emerald-300 ring-1 ring-emerald-800/60">
                                    {{ __('Approved') }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-950 px-2.5 py-0.5 text-xs font-medium text-amber-300 ring-1 ring-amber-800/60">
                                    {{ __('Pending') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="rounded-lg border border-zinc-700 px-3 py-1.5 text-xs text-zinc-300 hover:bg-zinc-800">
                                    {{ __('Manage') }}
                                </a>
                                @if(! $user->approved_at)
                                    <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">
                                            {{ __('Approve') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
@endsection

