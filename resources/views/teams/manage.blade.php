@extends('layouts.team')

@section('title', $team->name.' — '.__('Manage team'))

@section('content')
    @php
        $tileColors = ['bg-emerald-600', 'bg-sky-600', 'bg-amber-600', 'bg-violet-600', 'bg-rose-600', 'bg-teal-600'];
        $tile = $tileColors[(($team->id ?? 1) - 1) % count($tileColors)];
    @endphp

    <div class="mb-6 flex items-center gap-3">
        <span class="{{ $tile }} flex h-10 w-10 shrink-0 items-center justify-center rounded-md text-sm font-bold text-white">
            {{ strtoupper(mb_substr($team->name, 0, 1)) }}
        </span>
        <div class="min-w-0">
            <h1 class="truncate text-xl font-semibold text-zinc-900">{{ $team->name }}</h1>
            <p class="text-sm text-zinc-500">{{ __('Manage team') }}</p>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap items-center gap-4 border-b border-zinc-200" data-compact-my>
        @foreach(['channels' => __('Channels'), 'members' => __('Members')] as $key => $label)
            <a href="{{ route('teams.manage', ['team' => $team, 'tab' => $key]) }}"
                class="relative pb-3 text-sm font-medium transition {{ $tab === $key ? 'text-zinc-900' : 'text-zinc-500 hover:text-zinc-700' }}">
                {{ $label }}
                @if($tab === $key)
                    <span class="absolute inset-x-0 -bottom-px h-0.5 bg-emerald-500"></span>
                @endif
            </a>
        @endforeach
    </div>

    @if($tab === 'channels')
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-zinc-600">{{ __('Create and organize custom channels for this team.') }}</p>
            <button type="button" data-open-channel
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                + {{ __('Add channel') }}
            </button>
        </div>

        <ul class="overflow-hidden rounded-xl border border-zinc-200 bg-panel">
            @foreach($channels as $channel)
                <li class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 last:border-0">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-zinc-900">
                            @if($channel->is_general)
                                <span class="mr-1 text-zinc-400" aria-hidden="true">⌂</span>
                            @endif
                            {{ $channel->name }}
                            @if($channel->is_general)
                                <span class="ml-2 text-xs font-normal text-zinc-500">{{ __('Default') }}</span>
                            @endif
                        </p>
                        <a href="{{ route('teams.channels.show', [$team, $channel]) }}" class="mt-1 inline-block text-xs text-emerald-700 hover:underline">
                            {{ __('Open channel') }}
                        </a>
                    </div>
                    @unless($channel->is_general)
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" data-rename-channel data-id="{{ $channel->id }}" data-name="{{ $channel->name }}"
                                class="rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-800 hover:bg-zinc-100">
                                {{ __('Rename') }}
                            </button>
                            <form method="POST" action="{{ route('teams.channels.destroy', [$team, $channel]) }}"
                                onsubmit="return confirm(@json(__('Delete this channel and its posts?')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md border border-red-200 px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        </div>
                    @endunless
                </li>
            @endforeach
        </ul>

        <dialog id="channel-dialog" class="admin-dialog">
            <div class="p-5">
                <div class="flex items-start justify-between gap-4">
                    <h2 id="channel-dialog-title" class="text-lg font-semibold text-zinc-900">{{ __('Add channel') }}</h2>
                    <form method="dialog">
                        <button type="submit" class="rounded-md px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-100">{{ __('Close') }}</button>
                    </form>
                </div>
                <form id="channel-form" method="POST" action="{{ route('teams.channels.store', $team) }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" id="channel-method" value="POST">
                    <div>
                        <label for="channel-name" class="mb-1 block text-sm font-medium text-zinc-700">{{ __('Channel name') }}</label>
                        <input id="channel-name" name="name" type="text" required maxlength="80"
                            class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-900">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            {{ __('Save') }}
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
        <script>
            (() => {
                const dialog = document.getElementById('channel-dialog');
                const form = document.getElementById('channel-form');
                const title = document.getElementById('channel-dialog-title');
                const nameInput = document.getElementById('channel-name');
                const methodInput = document.getElementById('channel-method');
                const storeUrl = @json(route('teams.channels.store', $team));
                const updateTpl = @json(url('/teams/'.$team->id.'/channels/__ID__'));

                document.querySelectorAll('[data-open-channel]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        title.textContent = @json(__('Add channel'));
                        form.action = storeUrl;
                        methodInput.value = 'POST';
                        nameInput.value = '';
                        dialog?.showModal();
                    });
                });

                document.querySelectorAll('[data-rename-channel]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        title.textContent = @json(__('Rename channel'));
                        form.action = updateTpl.replace('__ID__', btn.dataset.id);
                        methodInput.value = 'PUT';
                        nameInput.value = btn.dataset.name || '';
                        dialog?.showModal();
                    });
                });

                dialog?.addEventListener('click', (event) => {
                    if (event.target === dialog) dialog.close();
                });
            })();
        </script>
    @else
        <div x-data="{ q: '' }">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <button type="button" data-open-students
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                    + {{ __('Add member') }}
                </button>
                <label class="relative block w-full max-w-xs sm:w-auto">
                    <span class="sr-only">{{ __('Search members') }}</span>
                    <input type="search" x-model="q" placeholder="{{ __('Search members') }}"
                        class="w-full rounded-md border border-zinc-300 bg-white py-2 pl-3 pr-3 text-sm text-zinc-900">
                </label>
            </div>

            <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-panel">
                <table class="min-w-full table-fixed text-left text-sm">
                    <thead class="border-b border-zinc-200 bg-rail text-xs font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th class="w-[32%] px-5 py-3 font-semibold">{{ __('Name') }}</th>
                            <th class="w-[36%] px-5 py-3 font-semibold">{{ __('Email') }}</th>
                            <th class="w-[18%] px-5 py-3 font-semibold">{{ __('Role') }}</th>
                            <th class="w-[14%] px-5 py-3 text-right font-semibold">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($members as $member)
                            @php
                                $display = $member->profile?->display_name ?? $member->email;
                                $roleLabel = $member->isAdmin() ? __('Admin') : ($member->isInstructor() ? __('Instructor') : __('Student'));
                                $searchBlob = strtolower($display.' '.$member->email.' '.$roleLabel);
                            @endphp
                            <tr x-show="!q || @js($searchBlob).includes(q.toLowerCase())" class="hover:bg-rail/60">
                                <td class="truncate px-5 py-3.5 font-medium text-zinc-900">
                                    <div class="flex items-center gap-2.5">
                                        @include('teams._avatar', ['user' => $member, 'size' => 'sm'])
                                        <span class="truncate">{{ $display }}</span>
                                    </div>
                                </td>
                                <td class="truncate px-5 py-3.5 text-zinc-500">{{ $member->email }}</td>
                                <td class="px-5 py-3.5 text-zinc-700">{{ $roleLabel }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    @if($member->isStudent() || auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('teams.members.detach', [$team, $member]) }}" class="inline"
                                            onsubmit="return confirm(@json(__('Remove this member from the team?')))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-red-600 hover:underline">{{ __('Remove') }}</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-zinc-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-zinc-500">{{ __('No members on this team yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <dialog id="students-dialog" class="admin-dialog">
            <div class="p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('Add member') }}</h2>
                        <p class="mt-1 text-sm text-zinc-600">{{ __('Add students to :team.', ['team' => $team->name]) }}</p>
                    </div>
                    <form method="dialog">
                        <button type="submit" class="rounded-md px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-100">{{ __('Close') }}</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('teams.members.attach', $team) }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="max-h-72 space-y-1 overflow-y-auto rounded-lg border border-zinc-200 p-2">
                        @forelse($availableStudents as $student)
                            <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-sm text-zinc-700 hover:bg-zinc-100">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                    class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-600">
                                <span class="min-w-0 flex-1 truncate">{{ $student->profile?->display_name ?? $student->email }}</span>
                            </label>
                        @empty
                            <p class="px-2 py-6 text-center text-sm text-zinc-500">{{ __('No student accounts left to add.') }}</p>
                        @endforelse
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            {{ __('Add selected') }}
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
        <script>
            const dialog = document.getElementById('students-dialog');
            document.querySelectorAll('[data-open-students]').forEach((btn) => {
                btn.addEventListener('click', () => dialog?.showModal());
            });
            dialog?.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        </script>
    @endif
@endsection
