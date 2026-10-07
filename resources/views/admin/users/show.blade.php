@extends('layouts.admin')

@section('title', __('User'))
@section('heading', __('User management'))

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-zinc-200 bg-panel p-5 lg:col-span-1">
            <h2 class="text-sm font-semibold text-zinc-900">{{ __('Account') }}</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">{{ __('Display name') }}</dt>
                    <dd class="text-zinc-800">{{ $user->profile?->display_name ?? $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">{{ __('Email') }}</dt>
                    <dd class="text-zinc-800">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">{{ __('Teams') }}</dt>
                    <dd class="text-zinc-800">{{ $user->teams->pluck('name')->join(', ') ?: __('None') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">{{ __('Status') }}</dt>
                    <dd>
                        @if($user->approved_at)
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">
                                {{ __('Approved') }}
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-amber-200">
                                {{ __('Pending approval') }}
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('admin.monitoring.user', $user) }}" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-900 hover:bg-zinc-100">
                    {{ __('Study monitoring') }}
                </a>
                @if(! $user->approved_at)
                    <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            {{ __('Approve') }}
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.revoke', $user) }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">
                            {{ __('Revoke approval') }}
                        </button>
                    </form>
                @endif
            </div>

            <hr class="my-6 border-zinc-200" />

            <h2 class="text-sm font-semibold text-zinc-900">{{ __('Set password') }}</h2>
            <p class="mt-1 text-xs text-zinc-500">{{ __('Sets a new password for this account. The user is not notified automatically.') }}</p>
            <form method="POST" action="{{ route('admin.users.password', $user) }}" class="mt-4 space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('New password') }}</label>
                    <input type="password" name="password" required autocomplete="new-password"
                        class="mt-2 w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('Confirm new password') }}</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                        class="mt-2 w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900">
                </div>
                <button type="submit" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-900 hover:bg-zinc-100">
                    {{ __('Update password') }}
                </button>
            </form>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-panel p-5 lg:col-span-2">
            <h2 class="text-sm font-semibold text-zinc-900">{{ __('Role') }}</h2>
            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                @method('PUT')
                <div class="min-w-[16rem]">
                    <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('Role') }}</label>
                    <select name="role" class="mt-2 w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900">
                        @foreach(\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-900 hover:bg-zinc-100">
                    {{ __('Update role') }}
                </button>
            </form>

        </section>
    </div>

    <section class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5">
        <h2 class="text-sm font-semibold text-red-800">{{ __('Delete account') }}</h2>
        <p class="mt-2 text-sm text-zinc-500">{{ __('Permanently remove this user, their enrollments, progress, and forum activity. This cannot be undone.') }}</p>
        @if($user->id === auth()->id())
            <p class="mt-4 text-sm text-amber-800">{{ __('You cannot delete your own account from here.') }}</p>
        @elseif($user->isAdmin() && \App\Models\User::query()->where('role', \App\Enums\UserRole::Admin)->count() <= 1)
            <p class="mt-4 text-sm text-amber-800">{{ __('This is the only administrator account and cannot be deleted.') }}</p>
        @else
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-4" onsubmit="return confirm(@json(__('Are you sure you want to delete this user? This cannot be undone.')));">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                    {{ __('Delete user') }}
                </button>
            </form>
        @endif
    </section>
@endsection

