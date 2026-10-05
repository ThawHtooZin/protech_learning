@extends('layouts.team')

@section('title', $assignment->title)

@section('content')
    @php
        $status = $ownSubmission?->status->value ?? 'not_submitted';
        $isTurnedIn = in_array($status, ['submitted', 'returned'], true);
        $statusLabel = match ($status) {
            'returned' => __('Returned'),
            'submitted' => __('Turned in'),
            default => __('Not turned in'),
        };
    @endphp

    @if($canManage)
        <div class="mb-6">
            <a href="{{ route('teams.show', $team) }}" class="text-sm text-emerald-400 hover:underline">← {{ __('Assignments') }}</a>
        </div>

        <header class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $assignment->isOpen() ? 'bg-emerald-950 text-emerald-300' : 'bg-zinc-800 text-zinc-400' }}">
                    {{ $assignment->isOpen() ? __('Open') : __('Closed') }}
                </span>
                <h1 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">{{ $assignment->title }}</h1>
                <p class="mt-2 text-sm text-zinc-400">
                    @if($assignment->due_at)
                        {{ __('Due :date', ['date' => $assignment->due_at->timezone(config('app.timezone'))->format('D, M j, Y g:i A')]) }}
                    @else
                        {{ __('No due date') }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('teams.assignments.edit', [$team, $assignment]) }}"
                    class="rounded-lg border border-zinc-600 px-4 py-2 text-sm font-semibold text-zinc-100 hover:bg-zinc-800">
                    {{ __('Edit') }}
                </a>
                @if($assignment->isOpen())
                    <form method="POST" action="{{ route('teams.assignments.close', [$team, $assignment]) }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-amber-700/50 px-4 py-2 text-sm font-medium text-amber-200 hover:bg-amber-950/40"
                            onclick="return confirm(@json(__('Close this assignment? Students will not be able to upload.')))">
                            {{ __('Close') }}
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('teams.assignments.destroy', [$team, $assignment]) }}"
                    onsubmit="return confirm(@json(__('Delete this assignment and all submissions?')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-900/60 px-4 py-2 text-sm text-red-300 hover:bg-red-950/40">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </header>

        <section class="mb-8 rounded-xl border border-white/5 bg-panel p-5">
            <h2 class="text-sm font-semibold text-white">{{ __('Instructions') }}</h2>
            <div class="mt-3 whitespace-pre-wrap text-sm text-zinc-300">
                {{ $assignment->instructions ?: __('No instructions provided.') }}
            </div>

            @if($assignment->attachments->isNotEmpty())
                <h3 class="mt-6 text-sm font-semibold text-white">{{ __('Resources') }}</h3>
                <ul class="mt-3 space-y-2">
                    @foreach($assignment->attachments as $attachment)
                        <li>
                            <a href="{{ route('teams.assignments.attachments.download', [$team, $assignment, $attachment]) }}"
                                class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-950/60 px-3 py-2 text-sm text-emerald-400 hover:border-zinc-500 hover:bg-zinc-900">
                                {{ $attachment->original_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded-xl border border-white/5 bg-panel">
            <div class="border-b border-zinc-800 px-5 py-4">
                <h2 class="text-sm font-semibold text-white">{{ __('Student work') }}</h2>
            </div>
            @if($roster->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-zinc-500">{{ __('No students on this team yet.') }}</p>
            @else
                <div class="divide-y divide-zinc-800">
                    @foreach($roster as $student)
                        @php
                            $submission = $assignment->submissions->firstWhere('user_id', $student->id);
                            $rowStatus = $submission?->status->value ?? 'not_submitted';
                        @endphp
                        <div class="px-5 py-4">
                            <div>
                                <p class="text-sm font-medium text-white">{{ $student->profile?->display_name ?? $student->email }}</p>
                                <p class="text-xs text-zinc-500">
                                    @if($rowStatus === 'not_submitted')
                                        {{ __('Not turned in') }}
                                    @elseif($rowStatus === 'returned')
                                        {{ __('Returned') }}
                                        @if($submission?->returned_at) · {{ $submission->returned_at->diffForHumans() }} @endif
                                    @else
                                        {{ __('Turned in') }}
                                        @if($submission?->submitted_at) · {{ $submission->submitted_at->diffForHumans() }} @endif
                                    @endif
                                </p>
                            </div>

                            @if($submission && $submission->files->isNotEmpty())
                                <ul class="mt-3 flex flex-wrap gap-2">
                                    @foreach($submission->files as $file)
                                        <li>
                                            <a href="{{ route('teams.assignments.files.download', [$team, $assignment, $submission, $file]) }}"
                                                class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-950/60 px-3 py-2 text-sm text-emerald-400 hover:border-zinc-500">
                                                {{ $file->original_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if($submission && in_array($rowStatus, ['submitted', 'returned'], true))
                                <form method="POST" action="{{ route('teams.assignments.return', [$team, $assignment, $submission]) }}"
                                    class="mt-3 max-w-xl space-y-2">
                                    @csrf
                                    <label class="block text-xs font-medium text-zinc-400" for="feedback-{{ $submission->id }}">{{ __('Feedback') }}</label>
                                    <textarea id="feedback-{{ $submission->id }}" name="feedback" rows="2"
                                        class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">{{ old('feedback', $submission->feedback) }}</textarea>
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">
                                        {{ __('Return') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @elseif(auth()->user()->isStudent())
        {{-- Microsoft Teams Classwork layout --}}
        <form method="POST" action="{{ route('teams.assignments.submit', [$team, $assignment]) }}" enctype="multipart/form-data"
            id="assignment-turn-in-form" class="mx-auto max-w-5xl">
            @csrf

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('teams.show', $team) }}"
                    class="inline-flex items-center gap-1 rounded-md border border-emerald-700/50 px-3 py-1.5 text-sm font-medium text-emerald-300 hover:bg-emerald-950/40">
                    ← {{ __('Assignments') }}
                </a>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 text-sm text-zinc-400">
                        @if($isTurnedIn)
                            <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="h-4 w-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                        {{ $statusLabel }}
                    </span>
                    @if($assignment->isOpen() && $canSubmit)
                        <button type="submit" id="turn-in-btn"
                            class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50">
                            {{ $isTurnedIn ? __('Turn in again') : __('Turn in') }}
                        </button>
                    @endif
                </div>
            </div>

            <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">{{ $assignment->title }}</h1>
            <p class="mt-2 text-sm text-zinc-400">
                @if($assignment->due_at)
                    {{ __('Due :date', ['date' => $assignment->due_at->timezone(config('app.timezone'))->format('F j, Y g:i A')]) }}
                @else
                    {{ __('No due date') }}
                @endif
                · {{ __('Multiple submissions allowed') }}
            </p>

            <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_10rem]">
                <div class="min-w-0 space-y-10">
                    <section>
                        <h2 class="text-lg font-semibold text-white">{{ __('Instructions') }}</h2>
                        <div class="mt-3 whitespace-pre-wrap text-sm leading-relaxed {{ $assignment->instructions ? 'text-zinc-300' : 'italic text-zinc-500' }}">
                            {{ $assignment->instructions ?: __('None') }}
                        </div>
                    </section>

                    @if($assignment->attachments->isNotEmpty())
                        <section>
                            <h2 class="text-lg font-semibold text-white">{{ __('Resources') }}</h2>
                            <ul class="mt-4 space-y-2">
                                @foreach($assignment->attachments as $attachment)
                                    <li>
                                        <a href="{{ route('teams.assignments.attachments.download', [$team, $assignment, $attachment]) }}"
                                            class="inline-flex items-center gap-2 text-sm font-medium text-emerald-400 hover:underline">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            {{ $attachment->original_name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    <section>
                        <h2 class="text-lg font-semibold text-white">{{ __('My work') }}</h2>

                        @if($ownSubmission?->feedback)
                            <div class="mt-4 rounded-lg border border-sky-900/50 bg-sky-950/30 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-sky-400">{{ __('Feedback') }}</p>
                                <p class="mt-2 whitespace-pre-wrap text-sm text-zinc-200">{{ $ownSubmission->feedback }}</p>
                            </div>
                        @endif

                        @if($ownSubmission && $ownSubmission->files->isNotEmpty())
                            <ul class="mt-4 space-y-2" id="submitted-files">
                                @foreach($ownSubmission->files as $file)
                                    <li>
                                        <a href="{{ route('teams.assignments.files.download', [$team, $assignment, $ownSubmission, $file]) }}"
                                            class="flex max-w-md items-center gap-3 rounded-lg border border-zinc-700 bg-zinc-900/50 px-3 py-2.5 text-sm text-zinc-200 hover:border-zinc-500">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-zinc-800 text-[10px] font-bold uppercase text-zinc-300">
                                                {{ strtoupper(\Illuminate\Support\Str::limit(pathinfo($file->original_name, PATHINFO_EXTENSION) ?: 'file', 4, '')) }}
                                            </span>
                                            <span class="min-w-0 flex-1 truncate">{{ $file->original_name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if($assignment->isOpen() && $canSubmit)
                            <div class="mt-4 flex flex-wrap items-center gap-4">
                                <label for="files" class="inline-flex cursor-pointer items-center gap-1.5 text-sm font-medium text-emerald-400 hover:text-emerald-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    {{ __('Attach') }}
                                </label>
                                <input id="files" type="file" name="files[]" multiple required class="sr-only">
                            </div>
                            <ul id="pending-files" class="mt-3 hidden max-w-md space-y-2"></ul>
                            <p class="mt-2 text-xs text-zinc-500">
                                {{ __('Any file type · up to :max files · :size MB each', [
                                    'max' => config('lms.assignments.max_files'),
                                    'size' => (int) (config('lms.assignments.max_file_kb') / 1024),
                                ]) }}
                            </p>
                            @error('files')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                            @error('files.*')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                        @elseif($assignment->isClosed())
                            <p class="mt-4 text-sm text-zinc-500">{{ __('This assignment is closed. New uploads are not allowed.') }}</p>
                        @endif
                    </section>
                </div>

                <aside class="lg:pt-1">
                    <h2 class="text-lg font-semibold text-white">{{ __('Points') }}</h2>
                    <p class="mt-3 text-sm text-zinc-500">{{ __('No points') }}</p>
                </aside>
            </div>
        </form>

        @if(session('assignment_turned_in'))
            <div id="turn-in-celebration" class="turn-in-overlay" role="status" aria-live="polite">
                <div class="turn-in-card">
                    <div class="turn-in-burst" aria-hidden="true"></div>
                    <div class="turn-in-check">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="turn-in-label">{{ __('Turned in') }}</p>
                </div>
            </div>
        @endif

        <script>
            (() => {
                const input = document.getElementById('files');
                const pending = document.getElementById('pending-files');
                const turnInBtn = document.getElementById('turn-in-btn');
                const form = document.getElementById('assignment-turn-in-form');

                const renderPending = () => {
                    if (!input || !pending) return;
                    const files = [...(input.files || [])];
                    pending.innerHTML = '';
                    if (!files.length) {
                        pending.classList.add('hidden');
                        return;
                    }
                    pending.classList.remove('hidden');
                    files.forEach((file) => {
                        const ext = (file.name.split('.').pop() || 'file').slice(0, 4).toUpperCase();
                        const li = document.createElement('li');
                        li.className = 'flex items-center gap-3 rounded-lg border border-emerald-800/60 bg-emerald-950/20 px-3 py-2.5 text-sm text-zinc-200';
                        li.innerHTML = `<span class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-zinc-800 text-[10px] font-bold text-zinc-300">${ext}</span><span class="min-w-0 flex-1 truncate">${file.name}</span>`;
                        pending.appendChild(li);
                    });
                };

                input?.addEventListener('change', renderPending);

                form?.addEventListener('submit', (event) => {
                    if (!input?.files?.length) {
                        event.preventDefault();
                        input?.click();
                        return;
                    }
                    if (turnInBtn) {
                        turnInBtn.disabled = true;
                        turnInBtn.textContent = @json(__('Turning in…'));
                    }
                });

                const celebration = document.getElementById('turn-in-celebration');
                if (celebration) {
                    requestAnimationFrame(() => celebration.classList.add('is-visible'));
                    setTimeout(() => {
                        celebration.classList.add('is-leaving');
                        setTimeout(() => celebration.remove(), 400);
                    }, 1800);
                }
            })();
        </script>
    @endif
@endsection
