@extends('layouts.learn')

@section('title', __('Edit profile'))

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="mb-6 text-2xl font-bold text-zinc-900">{{ __('Edit profile') }}</h1>
        <form method="POST" action="{{ route('profiles.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm text-zinc-600">{{ __('Avatar') }}</label>
                @if($profile->avatarUrl())
                    <img src="{{ $profile->avatarUrl() }}" alt="" class="mt-2 h-16 w-16 rounded-full object-cover">
                    <label class="mt-2 flex items-center gap-2 text-sm text-zinc-600">
                        <input type="checkbox" name="remove_avatar" value="1" class="rounded border-zinc-300">
                        {{ __('Remove avatar') }}
                    </label>
                @endif
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="mt-2 w-full text-sm text-zinc-600 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-700 file:px-3 file:py-2 file:text-zinc-900">
                @error('avatar')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm text-zinc-600">{{ __('Display name') }}</label>
                <input type="text" name="display_name" value="{{ old('display_name', $profile->display_name) }}" required
                    class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">
            </div>
            <div>
                <label class="block text-sm text-zinc-600">{{ __('Handle') }}</label>
                <input type="text" name="handle" value="{{ old('handle', $profile->handle) }}" required pattern="[a-zA-Z0-9_]{2,32}"
                    class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">
            </div>
            <div>
                <label class="block text-sm text-zinc-600">{{ __('Location') }}</label>
                <input type="text" name="location" value="{{ old('location', $profile->location) }}"
                    class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">
            </div>
            <div>
                <label class="block text-sm text-zinc-600">{{ __('Bio') }}</label>
                <textarea name="bio" rows="4" class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">{{ old('bio', $profile->bio) }}</textarea>
            </div>
            <fieldset class="space-y-3 rounded-lg border border-zinc-200 p-4">
                <legend class="px-1 text-sm font-medium text-zinc-600">{{ __('Social links') }}</legend>
                <div>
                    <label class="block text-xs text-zinc-500">GitHub</label>
                    <input type="url" name="social_github" value="{{ old('social_github', $social['github'] ?? '') }}" placeholder="https://github.com/username"
                        class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900">
                </div>
                <div>
                    <label class="block text-xs text-zinc-500">LinkedIn</label>
                    <input type="url" name="social_linkedin" value="{{ old('social_linkedin', $social['linkedin'] ?? '') }}"
                        class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900">
                </div>
                <div>
                    <label class="block text-xs text-zinc-500">X (Twitter)</label>
                    <input type="url" name="social_x" value="{{ old('social_x', $social['x'] ?? '') }}"
                        class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900">
                </div>
                <div>
                    <label class="block text-xs text-zinc-500">{{ __('Website') }}</label>
                    <input type="url" name="social_website" value="{{ old('social_website', $social['website'] ?? '') }}"
                        class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900">
                </div>
            </fieldset>
            <button type="submit" class="w-full rounded-md bg-emerald-600 py-2 font-medium text-white hover:bg-emerald-500">{{ __('Save') }}</button>
        </form>

        <div class="mt-10 border-t border-zinc-200 pt-10">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900">{{ __('Change password') }}</h2>
            <form method="POST" action="{{ route('profiles.password') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm text-zinc-600">{{ __('Current password') }}</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                        class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">
                </div>
                <div>
                    <label class="block text-sm text-zinc-600">{{ __('New password') }}</label>
                    <input type="password" name="password" required autocomplete="new-password"
                        class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">
                </div>
                <div>
                    <label class="block text-sm text-zinc-600">{{ __('Confirm new password') }}</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                        class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">
                </div>
                <button type="submit" class="w-full rounded-md border border-zinc-300 bg-zinc-100 py-2 font-medium text-zinc-900 hover:bg-zinc-700">{{ __('Update password') }}</button>
            </form>
        </div>
    </div>
@endsection
