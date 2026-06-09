@extends('layouts.admin')

@section('title', __('Forum categories'))

@section('heading', __('Forum categories'))

@section('content')
    <form method="POST" action="{{ route('admin.forums.categories.store') }}" class="mb-8 flex flex-wrap gap-2">
        @csrf
        <input type="text" name="name" placeholder="{{ __('Name') }}" required class="flex-1 rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-white">{{ __('Add') }}</button>
    </form>

    @error('category')
        <p class="mb-4 text-sm text-red-400">{{ $message }}</p>
    @enderror

    <ul class="space-y-3">
        @foreach($categories as $c)
            <li class="flex flex-wrap items-center gap-3 rounded-lg border border-zinc-800 bg-zinc-900/40 p-3">
                <form method="POST" action="{{ route('admin.forums.categories.update', $c) }}" class="flex flex-1 flex-wrap items-center gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $c->name }}" required class="min-w-[12rem] flex-1 rounded-md border border-zinc-700 bg-zinc-950 px-3 py-2 text-white">
                    <button type="submit" class="text-sm text-emerald-400 hover:underline">{{ __('Save') }}</button>
                </form>
                <span class="text-xs text-zinc-500">{{ $c->threads_count }} {{ __('threads') }}</span>
                <form method="POST" action="{{ route('admin.forums.categories.destroy', $c) }}" onsubmit="return confirm({{ json_encode(__('Delete this category?')) }})">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-400 hover:underline" @disabled($c->threads_count > 0)>{{ __('Delete') }}</button>
                </form>
            </li>
        @endforeach
    </ul>
@endsection
