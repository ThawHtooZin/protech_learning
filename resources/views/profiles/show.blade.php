@extends('layouts.learn')

@section('title', $profile->display_name)

@section('content')
    <div class="flex flex-col gap-6 md:flex-row md:items-start">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-white">{{ $profile->display_name }}</h1>
            <p class="text-sm text-zinc-500">{{ '@'.$profile->handle }}</p>
            @if($profile->bio)
                <p class="mt-4 text-zinc-300">{{ $profile->bio }}</p>
            @endif
        </div>
        @auth
            @if(auth()->id() === $profile->user_id)
                <a href="{{ route('profiles.edit') }}" class="rounded-md border border-zinc-700 px-4 py-2 text-sm text-white hover:bg-zinc-800">{{ __('Edit profile') }}</a>
            @endif
        @endauth
    </div>
@endsection
