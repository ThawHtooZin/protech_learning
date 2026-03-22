@extends('layouts.learn')

@section('title', __('Sign in'))

@section('content')
    <div class="mx-auto max-w-md py-8">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-8 shadow-xl shadow-black/20">
            <h1 class="text-center text-2xl font-bold text-white">{{ __('Sign in') }}</h1>
            <p class="mt-2 text-center text-sm text-zinc-500">{{ __('Welcome back to') }} {{ config('app.name') }}</p>
            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-white placeholder-zinc-600 focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Password') }}</label>
                    <input type="password" name="password" required
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-white focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <label class="flex items-center gap-2 text-sm text-zinc-400">
                    <input type="checkbox" name="remember" class="rounded border-zinc-600 text-emerald-600 focus:ring-emerald-600">
                    {{ __('Remember me') }}
                </label>
                <button type="submit" class="w-full rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Sign in') }}</button>
            </form>
            <p class="mt-6 text-center text-sm text-zinc-500">
                {{ __('No account?') }}
                <a href="{{ route('register') }}" class="font-medium text-emerald-400 hover:text-emerald-300">{{ __('Join') }}</a>
            </p>
        </div>
    </div>
@endsection
