@extends('layouts.learn')

@section('title', __('Edit profile'))

@section('content')
    <div class="mx-auto max-w-md">
        <h1 class="mb-6 text-2xl font-bold text-white">{{ __('Edit profile') }}</h1>
        <form method="POST" action="{{ route('profiles.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Display name') }}</label>
                <input type="text" name="display_name" value="{{ old('display_name', $profile->display_name) }}" required
                    class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Handle') }}</label>
                <input type="text" name="handle" value="{{ old('handle', $profile->handle) }}" required pattern="[a-zA-Z0-9_]{2,32}"
                    class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Bio') }}</label>
                <textarea name="bio" rows="4" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">{{ old('bio', $profile->bio) }}</textarea>
            </div>
            <button type="submit" class="w-full rounded-md bg-emerald-600 py-2 font-medium text-white hover:bg-emerald-500">{{ __('Save') }}</button>
        </form>

        <div class="mt-10 border-t border-zinc-800 pt-10">
            <h2 class="mb-4 text-lg font-semibold text-white">{{ __('Change password') }}</h2>
            <form method="POST" action="{{ route('profiles.password') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm text-zinc-400">{{ __('Current password') }}</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                        class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400">{{ __('New password') }}</label>
                    <input type="password" name="password" required autocomplete="new-password"
                        class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400">{{ __('Confirm new password') }}</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                        class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
                </div>
                <button type="submit" class="w-full rounded-md border border-zinc-600 bg-zinc-800 py-2 font-medium text-white hover:bg-zinc-700">{{ __('Update password') }}</button>
            </form>
        </div>
    </div>
@endsection
