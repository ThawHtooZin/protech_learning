@extends('layouts.learn')

@section('title', __('Forums'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold text-white">{{ __('Forums') }}</h1>
    <ul class="space-y-3">
        @forelse($categories as $cat)
            <li class="rounded-lg border border-zinc-800 bg-zinc-900/50 p-4">
                <a href="{{ route('forums.category', $cat) }}" class="text-lg font-medium text-emerald-400 hover:underline">{{ $cat->name }}</a>
                <span class="ml-2 text-sm text-zinc-500">{{ $cat->threads_count }} {{ __('threads') }}</span>
            </li>
        @empty
            <li class="text-zinc-500">{{ __('No categories yet.') }}</li>
        @endforelse
    </ul>
@endsection
