@extends('layouts.learn')

@section('title', __('Pending approval'))

@section('content')
    <div class="mx-auto max-w-md py-8">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-8 shadow-xl shadow-black/20">
            <h1 class="text-center text-2xl font-bold text-white">{{ __('Account pending approval') }}</h1>
            <p class="mt-2 text-center text-sm text-zinc-500">
                {{ __('Your account was created successfully, but an admin must approve it before you can use Protech LMS.') }}
            </p>

            <div class="mt-8 text-center">
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-zinc-500 hover:text-white">
                        {{ __('Log out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

