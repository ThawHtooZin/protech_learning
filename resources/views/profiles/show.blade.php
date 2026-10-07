@extends('layouts.learn')

@section('title', $profile->display_name)

@section('content')
    <div class="flex flex-col gap-6 md:flex-row md:items-start">
        <div class="shrink-0">
            @if($profile->avatarUrl())
                <img src="{{ $profile->avatarUrl() }}" alt="" class="h-24 w-24 rounded-full border border-zinc-300 object-cover">
            @else
                <div class="flex h-24 w-24 items-center justify-center rounded-full border border-zinc-300 bg-zinc-100 text-2xl font-bold text-zinc-600">
                    {{ strtoupper(substr($profile->display_name, 0, 1)) }}
                </div>
            @endif
        </div>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-zinc-900">{{ $profile->display_name }}</h1>
            <p class="text-sm text-zinc-500">{{ '@'.$profile->handle }}</p>
            @if($profile->location)
                <p class="mt-1 text-sm text-zinc-600">{{ $profile->location }}</p>
            @endif
            @if($profile->bio)
                <p class="mt-4 text-zinc-700">{{ $profile->bio }}</p>
            @endif
            @php($links = $profile->social_links ?? [])
            @if(!empty($links))
                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                    @foreach(['github' => 'GitHub', 'linkedin' => 'LinkedIn', 'x' => 'X', 'website' => 'Website'] as $key => $label)
                        @if(!empty($links[$key]))
                            <a href="{{ $links[$key] }}" target="_blank" rel="noopener noreferrer" class="text-emerald-700 hover:underline">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
        @auth
            @if(auth()->id() === $profile->user_id)
                <a href="{{ route('profiles.edit') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100">{{ __('Edit profile') }}</a>
            @endif
        @endauth
    </div>

    @if($courseProgress->isNotEmpty())
        <section class="mt-10 border-t border-zinc-200 pt-8">
            <h2 class="text-lg font-semibold text-zinc-900">{{ __('Learning progress') }}</h2>
            <ul class="mt-4 space-y-3">
                @foreach($courseProgress as $row)
                    <li class="rounded-lg border border-zinc-200 bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <a href="{{ route('courses.show', $row['course']) }}" class="font-medium text-zinc-900 hover:text-emerald-700">{{ $row['course']->title }}</a>
                            <span class="text-sm text-emerald-700">{{ $row['percent'] }}%</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-100">
                            <div class="h-full rounded-full bg-emerald-600" style="width: {{ $row['percent'] }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
