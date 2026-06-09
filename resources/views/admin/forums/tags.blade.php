@extends('layouts.admin')

@section('title', __('Tags'))

@section('heading', __('Tags'))

@section('content')
    <form method="POST" action="{{ route('admin.forums.tags.store') }}" class="mb-8 flex flex-wrap gap-2">
        @csrf
        <input type="text" name="name" placeholder="{{ __('Name') }}" required class="flex-1 rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-white">{{ __('Add') }}</button>
    </form>
    <ul class="space-y-2">
        @foreach($tags as $t)
            <li class="flex flex-wrap items-center gap-3 rounded-lg border border-zinc-800 bg-zinc-900/40 px-3 py-2">
                <form method="POST" action="{{ route('admin.forums.tags.update', $t) }}" class="flex flex-1 items-center gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $t->name }}" required class="flex-1 rounded-md border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                    <button type="submit" class="text-sm text-emerald-400 hover:underline">{{ __('Save') }}</button>
                </form>
                <span class="text-xs text-zinc-500">{{ $t->threads_count }} {{ __('threads') }}</span>
                <form method="POST" action="{{ route('admin.forums.tags.destroy', $t) }}" onsubmit="return confirm({{ json_encode(__('Delete this tag?')) }})">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-400 hover:underline">{{ __('Delete') }}</button>
                </form>
            </li>
        @endforeach
    </ul>
    <div class="mt-6">{{ $tags->links() }}</div>
@endsection
