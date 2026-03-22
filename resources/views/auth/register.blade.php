@extends('layouts.learn')

@section('title', __('Join'))

@section('content')
    <div class="mx-auto max-w-md py-8">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-8 shadow-xl shadow-black/20">
            <h1 class="text-center text-2xl font-bold text-white">{{ __('Create account') }}</h1>
            <p class="mt-2 text-center text-sm text-zinc-500">{{ __('Join') }} {{ config('app.name') }}</p>
            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Name') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-white focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-white focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Handle') }} <span class="text-xs font-normal text-zinc-600">(@mention)</span></label>
                    <input type="text" name="handle" value="{{ old('handle') }}" required pattern="[a-zA-Z0-9_]{2,32}"
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-white focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Display name') }}</label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" required
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-white focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Password') }}</label>
                    <input type="password" name="password" required
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-white focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Confirm password') }}</label>
                    <input type="password" name="password_confirmation" required
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-white focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <button type="submit" class="w-full rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Create account') }}</button>
            </form>
            <p class="mt-6 text-center text-sm text-zinc-500">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" class="font-medium text-emerald-400 hover:text-emerald-300">{{ __('Sign in') }}</a>
            </p>
        </div>
    </div>
@endsection
