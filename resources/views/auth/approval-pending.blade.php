@extends('layouts.learn')

@section('title', __('Pending approval'))

@section('content')
    <div class="mx-auto max-w-md py-8">
        <div class="rounded-2xl border border-zinc-200 bg-panel p-8">
            <h1 class="text-center text-2xl font-bold text-zinc-900">{{ __('Account pending approval') }}</h1>
            <p class="mt-2 text-center text-sm text-zinc-500">
                {{ __('Your account was created successfully, but an admin must approve it before you can use Protech LMS.') }}
            </p>

            <div class="mt-8 text-center">
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-zinc-500 hover:text-zinc-900">
                        {{ __('Log out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

