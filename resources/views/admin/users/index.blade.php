@extends('layouts.admin')

@section('title', __('Users'))
@section('heading', __('Users'))

@section('content')
    <nav class="flex gap-6 border-b border-zinc-800" aria-label="{{ __('User management sections') }}">
        <a href="{{ route('admin.users.index', ['tab' => 'teams']) }}"
            @class(['border-b-2 px-1 pb-3 text-sm font-semibold', 'border-emerald-500 text-white' => $activeTab === 'teams', 'border-transparent text-zinc-500 hover:text-zinc-200' => $activeTab !== 'teams'])>
            {{ __('Teams') }} <span class="ml-1 text-xs text-zinc-500">{{ $teamCount }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['tab' => 'users']) }}"
            @class(['border-b-2 px-1 pb-3 text-sm font-semibold', 'border-emerald-500 text-white' => $activeTab === 'users', 'border-transparent text-zinc-500 hover:text-zinc-200' => $activeTab !== 'users'])>
            {{ __('All users') }} <span class="ml-1 text-xs text-zinc-500">{{ $userCount }}</span>
        </a>
    </nav>

    @if($activeTab === 'teams')
        @php
            $tileColors = [
                'bg-emerald-600',
                'bg-sky-600',
                'bg-amber-600',
                'bg-violet-600',
                'bg-rose-600',
                'bg-teal-600',
            ];
        @endphp

        <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-white">{{ __('Your teams') }}</h2>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Open a team to manage members.') }}</p>
            </div>
            <button id="open-create-team-dialog" type="button"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('Create team') }}
            </button>
        </div>

        @if($teams->isEmpty())
            <div class="mt-10 rounded-xl border border-dashed border-zinc-700 px-6 py-16 text-center">
                <p class="text-sm font-medium text-zinc-300">{{ __('No teams yet') }}</p>
                <p class="mt-2 text-sm text-zinc-500">{{ __('Create a team, then add instructors and students.') }}</p>
                <button type="button" data-open-create-team
                    class="mt-5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                    {{ __('Create team') }}
                </button>
            </div>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($teams as $team)
                    @php($tile = $tileColors[($team->id - 1) % count($tileColors)])
                    <article class="group relative rounded-2xl border border-white/5 bg-panel transition hover:border-white/10 hover:bg-rail">
                        <button type="button"
                            class="absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-lg text-lg leading-none text-zinc-500 hover:bg-zinc-800 hover:text-white"
                            aria-label="{{ __('Team options') }}"
                            data-open-team-options
                            data-team-id="{{ $team->id }}"
                            data-team-name="{{ $team->name }}"
                            data-update-url="{{ route('admin.teams.update', $team) }}"
                            data-destroy-url="{{ route('admin.teams.destroy', $team) }}">
                            &hellip;
                        </button>

                        <a href="{{ route('admin.teams.show', $team) }}" class="flex items-start gap-4 p-5 pr-14">
                            <span class="{{ $tile }} flex h-16 w-16 shrink-0 items-center justify-center rounded-xl text-xl font-bold text-white shadow-lg shadow-black/30">
                                {{ strtoupper(mb_substr($team->name, 0, 1)) }}
                            </span>
                            <span class="min-w-0 pt-1">
                                <span class="block truncate text-lg font-semibold text-white group-hover:text-emerald-300">{{ $team->name }}</span>
                                <span class="mt-1.5 block text-sm text-zinc-500">
                                    {{ trans_choice(':count member|:count members', $team->users_count, ['count' => $team->users_count]) }}
                                </span>
                            </span>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif

        <dialog id="create-team-dialog" aria-labelledby="create-team-title" class="admin-dialog">
            <div class="p-6 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="create-team-title" class="text-lg font-semibold text-white">{{ __('Create team') }}</h2>
                        <p class="mt-1 text-sm text-zinc-400">{{ __('Give the team a clear name, then add members.') }}</p>
                    </div>
                    <form method="dialog">
                        <button type="submit" class="rounded-md px-3 py-1.5 text-sm text-zinc-300 hover:bg-zinc-800">{{ __('Close') }}</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('admin.teams.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="new-team-name" class="block text-sm font-medium text-zinc-300">{{ __('Team name') }}</label>
                        <input id="new-team-name" type="text" name="name" value="{{ old('name') }}" required maxlength="120" autofocus
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white">
                        @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                            {{ __('Create team') }}
                        </button>
                    </div>
                </form>
            </div>
        </dialog>

        <dialog id="team-options-dialog" aria-labelledby="team-options-title" class="admin-dialog">
            <div class="p-6 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="team-options-title" class="text-lg font-semibold text-white">{{ __('Team settings') }}</h2>
                        <p class="mt-1 text-sm text-zinc-400" data-team-options-subtitle></p>
                    </div>
                    <form method="dialog">
                        <button type="submit" class="rounded-md px-3 py-1.5 text-sm text-zinc-300 hover:bg-zinc-800">{{ __('Close') }}</button>
                    </form>
                </div>

                <form id="team-options-rename-form" method="POST" action="#" class="mt-6 space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="team-options-name" class="block text-sm font-medium text-zinc-300">{{ __('Team name') }}</label>
                        <input id="team-options-name" type="text" name="name" required maxlength="120"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white">
                    </div>
                    <button type="submit" class="rounded-lg border border-zinc-600 px-4 py-2.5 text-sm font-semibold text-zinc-100 hover:bg-zinc-800">
                        {{ __('Save name') }}
                    </button>
                </form>

                <form id="team-options-delete-form" method="POST" action="#" class="mt-8 border-t border-zinc-800 pt-6"
                    onsubmit="return confirm(@json(__('Delete this team? Members will remain in the system.')));">
                    @csrf
                    @method('DELETE')
                    <p class="text-sm font-medium text-red-300">{{ __('Delete team') }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ __('This cannot be undone.') }}</p>
                    <button type="submit" class="mt-3 rounded-lg border border-red-900/60 px-4 py-2.5 text-sm text-red-300 hover:bg-red-950/40">
                        {{ __('Delete team') }}
                    </button>
                </form>
            </div>
        </dialog>

        <script>
            const wireDialog = (dialog) => {
                dialog?.addEventListener('click', (event) => {
                    if (event.target === dialog) dialog.close();
                });
            };

            const createTeamDialog = document.getElementById('create-team-dialog');
            const openCreateTeam = () => createTeamDialog?.showModal();
            document.getElementById('open-create-team-dialog')?.addEventListener('click', openCreateTeam);
            document.querySelectorAll('[data-open-create-team]').forEach((btn) => btn.addEventListener('click', openCreateTeam));
            wireDialog(createTeamDialog);
            @if($errors->has('name'))
                createTeamDialog?.showModal();
            @endif

            const optionsDialog = document.getElementById('team-options-dialog');
            const renameForm = document.getElementById('team-options-rename-form');
            const deleteForm = document.getElementById('team-options-delete-form');
            const nameInput = document.getElementById('team-options-name');
            const subtitle = document.querySelector('[data-team-options-subtitle]');
            wireDialog(optionsDialog);

            document.querySelectorAll('[data-open-team-options]').forEach((btn) => {
                btn.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    renameForm.action = btn.dataset.updateUrl;
                    deleteForm.action = btn.dataset.destroyUrl;
                    nameInput.value = btn.dataset.teamName || '';
                    subtitle.textContent = btn.dataset.teamName || '';
                    optionsDialog?.showModal();
                });
            });
        </script>
    @else
        <div class="mt-6 flex justify-end">
            <button id="open-account-dialog" type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('Create account') }}
            </button>
        </div>

        <dialog id="create-account-dialog" aria-labelledby="create-account-title" class="admin-dialog admin-dialog--wide">
            <div class="p-6 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="create-account-title" class="text-lg font-semibold text-white">{{ __('Create account') }}</h2>
                        <p class="mt-1 text-sm text-zinc-400">{{ __('Set up the account details and role.') }}</p>
                    </div>
                    <form method="dialog">
                        <button type="submit" class="rounded-md px-3 py-1.5 text-sm text-zinc-300 hover:bg-zinc-800">{{ __('Close') }}</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('admin.users.store', ['tab' => 'users']) }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label for="account-display-name" class="block text-sm font-medium text-zinc-300">{{ __('Display name') }}</label>
                        <input id="account-display-name" type="text" name="display_name" value="{{ old('display_name') }}" required maxlength="255" autocomplete="name"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white">
                        @error('display_name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="account-username" class="block text-sm font-medium text-zinc-300">{{ __('Username') }}</label>
                        <input id="account-username" type="text" name="username" value="{{ old('username') }}" required pattern="[a-zA-Z0-9_]{2,32}" autocomplete="username"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white">
                        @error('username')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="account-email" class="block text-sm font-medium text-zinc-300">{{ __('Email') }}</label>
                        <input id="account-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white">
                        @error('email')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="account-role" class="block text-sm font-medium text-zinc-300">{{ __('Role') }}</label>
                        <select id="account-role" name="role" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white">
                            @foreach(\App\Enums\UserRole::cases() as $role)
                                <option value="{{ $role->value }}" @selected(old('role', \App\Enums\UserRole::Student->value) === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @error('role')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="account-password" class="block text-sm font-medium text-zinc-300">{{ __('Password') }}</label>
                        <input id="account-password" type="password" name="password" required autocomplete="new-password"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white">
                        @error('password')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="account-password-confirmation" class="block text-sm font-medium text-zinc-300">{{ __('Confirm password') }}</label>
                        <input id="account-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white">
                    </div>
                    <div class="flex justify-end gap-2 sm:col-span-2">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Create account') }}</button>
                    </div>
                </form>
            </div>
        </dialog>

        <script>
            const accountDialog = document.getElementById('create-account-dialog');
            document.getElementById('open-account-dialog')?.addEventListener('click', () => accountDialog?.showModal());
            accountDialog?.addEventListener('click', (event) => {
                if (event.target === accountDialog) accountDialog.close();
            });
            @if($errors->any())
                accountDialog?.showModal();
            @endif
        </script>

        <div class="mt-6 overflow-x-auto border-b border-zinc-800">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-800 text-xs uppercase tracking-wider text-zinc-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('Person') }}</th>
                        <th class="px-4 py-3">{{ __('Email') }}</th>
                        <th class="px-4 py-3">{{ __('Teams') }}</th>
                        <th class="px-4 py-3">{{ __('Role') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @foreach($users as $user)
                        <tr class="hover:bg-zinc-800/30">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-white hover:text-emerald-300">
                                    {{ $user->profile?->display_name ?? $user->email }}
                                </a>
                                @if($user->profile)<div class="text-xs text-zinc-500">{{ '@'.$user->profile->handle }}</div>@endif
                            </td>
                            <td class="px-4 py-3 text-zinc-400">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $user->teams->pluck('name')->join(', ') ?: __('None') }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $user->role->label() }}</td>
                            <td class="px-4 py-3">
                                @if($user->approved_at)
                                    <span class="text-emerald-300">{{ __('Approved') }}</span>
                                @else
                                    <span class="text-amber-300">{{ __('Pending') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" class="rounded-lg border border-zinc-700 px-3 py-1.5 text-xs text-zinc-300 hover:bg-zinc-800">{{ __('Manage') }}</a>
                                    @if(! $user->approved_at)
                                        <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">{{ __('Approve') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $users->links() }}</div>
    @endif
@endsection
