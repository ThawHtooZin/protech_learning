@extends('layouts.admin')

@section('title', __('Forum categories'))

@section('heading', __('Forum categories'))

@section('content')
    <form method="POST" action="{{ route('admin.forums.categories.store') }}" class="mb-8 flex flex-wrap gap-2">
        @csrf
        <input type="text" name="name" placeholder="{{ __('Name') }}" required class="flex-1 rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-white">{{ __('Add') }}</button>
    </form>
    <ul class="space-y-2">
        @foreach($categories as $c)
            <li class="text-zinc-300">{{ $c->name }} <span class="text-zinc-600">({{ $c->slug }})</span></li>
        @endforeach
    </ul>
@endsection
