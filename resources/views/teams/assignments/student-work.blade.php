@extends('layouts.team')

@section('title', ($student->profile?->display_name ?? $student->email).' — '.$assignment->title)

@section('content')
    @php
        $statusLabel = match ($workStatus) {
            'returned' => __('Returned'),
            'turned_in' => __('Turned in'),
            'turned_in_late' => __('Turned in late'),
            'late' => __('Late'),
            'viewed' => __('Viewed'),
            default => __('Not turned in'),
        };
        $statusClass = match ($workStatus) {
            'returned', 'turned_in' => 'bg-emerald-50 text-emerald-700',
            'turned_in_late', 'late' => 'bg-rose-50 text-rose-700',
            'viewed' => 'bg-sky-50 text-sky-700',
            default => 'bg-zinc-100 text-zinc-600',
        };
    @endphp

    <div class="mb-6">
        <a href="{{ route('teams.assignments.show', [$team, $assignment]) }}" class="text-sm text-emerald-700 hover:underline">
            ← {{ __('Student work') }}
        </a>
    </div>

    <header class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm text-zinc-500">{{ $assignment->title }}</p>
            <h1 class="mt-1 text-2xl font-semibold text-zinc-900">{{ $student->profile?->display_name ?? $student->email }}</h1>
            <p class="mt-1 text-sm text-zinc-500">{{ $student->email }}</p>
        </div>
        <span class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section class="mb-8 rounded-xl border border-zinc-200 bg-panel p-5">
        <h2 class="text-sm font-semibold text-zinc-900">{{ __('Submitted work') }}</h2>

        @if($submission && $submission->files->isNotEmpty())
            <ul class="mt-4 space-y-2">
                @foreach($submission->files as $file)
                    <li>
                        <a href="{{ route('teams.assignments.files.download', [$team, $assignment, $submission, $file]) }}"
                            class="flex max-w-lg items-center gap-3 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-800 hover:border-zinc-500">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-zinc-100 text-[10px] font-bold uppercase text-zinc-700">
                                {{ strtoupper(\Illuminate\Support\Str::limit(pathinfo($file->original_name, PATHINFO_EXTENSION) ?: 'file', 4, '')) }}
                            </span>
                            <span class="min-w-0 flex-1 truncate">{{ $file->original_name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            @if($submission->submitted_at)
                <p class="mt-3 text-xs text-zinc-500">
                    {{ __('Submitted :date', ['date' => $submission->submitted_at->timezone(config('app.timezone'))->format('D, M j, Y g:i A')]) }}
                </p>
            @endif
        @else
            <p class="mt-3 text-sm text-zinc-500">{{ __('No files uploaded yet.') }}</p>
            @if($submission?->viewed_at)
                <p class="mt-1 text-xs text-zinc-500">
                    {{ __('Viewed :date', ['date' => $submission->viewed_at->timezone(config('app.timezone'))->format('D, M j, Y g:i A')]) }}
                </p>
            @endif
        @endif
    </section>

    @if($submission && in_array($workStatus, ['turned_in', 'turned_in_late', 'returned'], true))
        <section class="rounded-xl border border-zinc-200 bg-panel p-5">
            <h2 class="text-sm font-semibold text-zinc-900">{{ __('Feedback') }}</h2>
            <form method="POST" action="{{ route('teams.assignments.return', [$team, $assignment, $submission]) }}" class="mt-4 max-w-xl space-y-3">
                @csrf
                <label class="block text-xs font-medium text-zinc-600" for="feedback">{{ __('Feedback') }}</label>
                <textarea id="feedback" name="feedback" rows="4"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900">{{ old('feedback', $submission->feedback) }}</textarea>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                    {{ __('Return') }}
                </button>
            </form>
        </section>
    @endif
@endsection
