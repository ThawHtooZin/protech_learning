@extends('layouts.admin')

@section('title', __('User'))
@section('heading', __('User management'))

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-zinc-800 bg-zinc-900/40 p-5 lg:col-span-1">
            <h2 class="text-sm font-semibold text-white">{{ __('Account') }}</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">{{ __('Name') }}</dt>
                    <dd class="text-zinc-200">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">{{ __('Email') }}</dt>
                    <dd class="text-zinc-200">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-zinc-500">{{ __('Status') }}</dt>
                    <dd>
                        @if($user->approved_at)
                            <span class="inline-flex items-center rounded-full bg-emerald-950 px-2.5 py-0.5 text-xs font-medium text-emerald-300 ring-1 ring-emerald-800/60">
                                {{ __('Approved') }}
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-950 px-2.5 py-0.5 text-xs font-medium text-amber-300 ring-1 ring-amber-800/60">
                                {{ __('Pending approval') }}
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('admin.monitoring.user', $user) }}" class="rounded-lg border border-zinc-700 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
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
                        <button type="submit" class="rounded-lg border border-amber-700/60 bg-amber-950/30 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-950/50">
                            {{ __('Revoke approval') }}
                        </button>
                    </form>
                @endif
            </div>

            <hr class="my-6 border-zinc-800" />

            <h2 class="text-sm font-semibold text-white">{{ __('Set password') }}</h2>
            <p class="mt-1 text-xs text-zinc-500">{{ __('Sets a new password for this account. The user is not notified automatically.') }}</p>
            <form method="POST" action="{{ route('admin.users.password', $user) }}" class="mt-4 space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('New password') }}</label>
                    <input type="password" name="password" required autocomplete="new-password"
                        class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('Confirm new password') }}</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                        class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                </div>
                <button type="submit" class="rounded-lg border border-zinc-700 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                    {{ __('Update password') }}
                </button>
            </form>
        </section>

        <section class="rounded-xl border border-zinc-800 bg-zinc-900/40 p-5 lg:col-span-2">
            <h2 class="text-sm font-semibold text-white">{{ __('Role') }}</h2>
            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                @method('PUT')
                <div class="min-w-[16rem]">
                    <label class="block text-xs uppercase tracking-wider text-zinc-500">{{ __('Role') }}</label>
                    <select name="role" class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                        @foreach(\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg border border-zinc-700 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                    {{ __('Update role') }}
                </button>
            </form>

            <hr class="my-6 border-zinc-800" />

            <h2 class="text-sm font-semibold text-white">{{ __('Course access') }}</h2>
            <p class="mt-2 text-sm text-zinc-500">{{ __('Approved users can only access courses assigned here.') }}</p>

            <form method="POST" action="{{ route('admin.users.courses', $user) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($courses as $course)
                        <label class="flex items-start gap-3 rounded-lg border border-zinc-800 bg-zinc-950/40 p-3 hover:bg-zinc-950/60">
                            <input type="checkbox" name="course_ids[]" value="{{ $course->id }}"
                                @checked(in_array($course->id, $assignedCourseIds, true))
                                class="mt-1 rounded border-zinc-600 text-emerald-600 focus:ring-emerald-600" />
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium text-white">{{ $course->title }}</span>
                                @if($course->is_published)
                                    <span class="text-xs text-zinc-500">{{ __('Published') }}</span>
                                @else
                                    <span class="text-xs text-amber-400">{{ __('Draft') }}</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                    {{ __('Save course access') }}
                </button>
            </form>
        </section>
    </div>
@endsection

