@extends('layouts.admin')

@section('title', __('Tags'))

@section('heading', __('Tags'))

@section('content')
    <form method="POST" action="{{ route('admin.forums.tags.store') }}" class="mb-8 flex flex-wrap gap-2">
        @csrf
        <input type="text" name="name" placeholder="{{ __('Name') }}" required class="flex-1 rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-white">{{ __('Add') }}</button>
    </form>
    <ul class="flex flex-wrap gap-2">
        @foreach($tags as $t)
            <li class="rounded-full bg-zinc-800 px-3 py-1 text-sm text-zinc-300">{{ $t->name }}</li>
        @endforeach
    </ul>
    <div class="mt-6">{{ $tags->links() }}</div>
@endsection
