@extends('layouts.team')

@section('title', $team->name.' — '.__('General'))

@section('content')
    @php
        $memberIds = $team->users->pluck('id');
        $availableStudents = $students->reject(fn ($user) => $memberIds->contains($user->id))->values();
        $teamInstructors = $team->users->filter(fn ($user) => $user->isInstructor())->sortBy(fn ($m) => $m->profile?->display_name ?? $m->email);
        $teamStudents = $team->users->filter(fn ($user) => $user->isStudent())->sortBy(fn ($m) => $m->profile?->display_name ?? $m->email);
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-white">{{ __('General') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ trans_choice(':count member|:count members', $team->users_count, ['count' => $team->users_count]) }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($canManageStudents)
                <button type="button" data-open-students
                    class="rounded-lg border border-zinc-600 px-4 py-2 text-sm font-semibold text-zinc-100 hover:bg-zinc-800">
                    {{ __('Add students') }}
                </button>
            @endif
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.teams.show', $team) }}"
                    class="rounded-lg border border-amber-700/50 px-4 py-2 text-sm font-medium text-amber-200 hover:bg-amber-950/40">
                    {{ __('Admin settings') }}
                </a>
            @endif
        </div>
    </div>

    <section class="mb-8 space-y-4">
        <h2 class="text-sm font-semibold text-white">{{ __('Posts') }}</h2>

        @if($canPostOnGeneral)
            <form method="POST" action="{{ route('teams.posts.store', $team) }}" class="rounded-xl border border-white/5 bg-panel p-4">
                @csrf
                <label for="post-body" class="sr-only">{{ __('Write a post') }}</label>
                <textarea id="post-body" name="body" rows="3" required maxlength="20000"
                    placeholder="{{ __('Share an update. Use @handle or @all to notify people.') }}"
                    class="w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white">{{ old('body') }}</textarea>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        {{ __('Post') }}
                    </button>
                </div>
            </form>
        @endif

        @forelse($posts as $post)
            <article id="team-post-{{ $post->id }}" class="rounded-xl border border-white/5 bg-panel p-4">
                <p class="text-xs text-zinc-500">
                    <span class="font-medium text-zinc-300">{{ $post->user->profile?->display_name ?? $post->user->email }}</span>
                    · {{ $post->created_at->diffForHumans() }}
                </p>
                <div class="mt-2 text-sm text-zinc-200">{!! app(\App\Services\MentionRenderer::class)->toHtml($post->body, 'team_post', $post->id, true) !!}</div>

                @if($post->replies->isNotEmpty())
                    <ul class="mt-4 space-y-3 border-t border-zinc-800 pt-3">
                        @foreach($post->replies as $reply)
                            <li id="team-post-{{ $reply->id }}">
                                <p class="text-xs text-zinc-500">
                                    <span class="font-medium text-zinc-300">{{ $reply->user->profile?->display_name ?? $reply->user->email }}</span>
                                    · {{ $reply->created_at->diffForHumans() }}
                                </p>
                                <div class="mt-1 text-sm text-zinc-200">{!! app(\App\Services\MentionRenderer::class)->toHtml($reply->body, 'team_post', $reply->id, true) !!}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if($canReplyOnGeneral)
                    <form method="POST" action="{{ route('teams.posts.replies.store', [$team, $post]) }}" class="mt-4">
                        @csrf
                        <label for="reply-{{ $post->id }}" class="sr-only">{{ __('Reply') }}</label>
                        <textarea id="reply-{{ $post->id }}" name="body" rows="2" required maxlength="20000"
                            placeholder="{{ __('Reply. Use @handle or @all.') }}"
                            class="w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white"></textarea>
                        <div class="mt-2 flex justify-end">
                            <button type="submit" class="rounded-md bg-zinc-700 px-3 py-1.5 text-sm text-white hover:bg-zinc-600">
                                {{ __('Reply') }}
                            </button>
                        </div>
                    </form>
                @endif
            </article>
        @empty
            <p class="rounded-xl border border-white/5 bg-panel px-4 py-8 text-center text-sm text-zinc-500">
                {{ __('No posts yet.') }}
            </p>
        @endforelse
    </section>

    <section class="rounded-xl border border-white/5 bg-panel">
        <div class="border-b border-zinc-800 px-5 py-4">
            <h2 class="text-sm font-semibold text-white">{{ __('Members') }}</h2>
        </div>
        <div class="px-5 py-2">
            <div class="py-3">
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">{{ __('Instructors') }}</p>
                @forelse($teamInstructors as $member)
                    <div class="flex items-center gap-3 border-b border-zinc-800/80 py-3 last:border-0">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-white">{{ $member->profile?->display_name ?? $member->email }}</p>
                            <p class="truncate text-xs text-zinc-500">{{ $member->email }}</p>
                        </div>
                        <span class="rounded-md bg-sky-950 px-2 py-1 text-xs text-sky-300">{{ __('Instructor') }}</span>
                    </div>
                @empty
                    <p class="py-3 text-sm text-zinc-500">{{ __('No instructors on this team yet.') }}</p>
                @endforelse
            </div>
            <div class="border-t border-zinc-800 py-3">
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">{{ __('Students') }}</p>
                @forelse($teamStudents as $member)
                    <div class="flex items-center gap-3 border-b border-zinc-800/80 py-3 last:border-0">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-white">{{ $member->profile?->display_name ?? $member->email }}</p>
                            <p class="truncate text-xs text-zinc-500">{{ $member->email }}</p>
                        </div>
                        <span class="rounded-md bg-zinc-800 px-2 py-1 text-xs text-zinc-300">{{ __('Student') }}</span>
                        @if($canManageStudents)
                            <form method="POST" action="{{ route('teams.members.detach', [$team, $member]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md px-2 py-1.5 text-xs text-zinc-400 hover:bg-zinc-800 hover:text-red-300">
                                    {{ __('Remove') }}
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="py-3 text-sm text-zinc-500">{{ __('No students on this team yet.') }}</p>
                @endforelse
            </div>
        </div>
    </section>

    @if($canManageStudents)
        <dialog id="students-dialog" class="admin-dialog">
            <div class="p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ __('Add students') }}</h2>
                        <p class="mt-1 text-sm text-zinc-400">{{ __('Add students to :team.', ['team' => $team->name]) }}</p>
                    </div>
                    <form method="dialog">
                        <button type="submit" class="rounded-md px-3 py-1.5 text-sm text-zinc-300 hover:bg-zinc-800">{{ __('Close') }}</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('teams.members.attach', $team) }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="max-h-72 space-y-1 overflow-y-auto rounded-lg border border-zinc-800 p-2">
                        @forelse($availableStudents as $student)
                            <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-sm text-zinc-300 hover:bg-zinc-800">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                    class="rounded border-zinc-600 text-emerald-600 focus:ring-emerald-600">
                                <span class="min-w-0 flex-1 truncate">{{ $student->profile?->display_name ?? $student->email }}</span>
                            </label>
                        @empty
                            <p class="px-2 py-6 text-center text-sm text-zinc-500">{{ __('No student accounts left to add.') }}</p>
                        @endforelse
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            {{ __('Add selected') }}
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
        <script>
            const dialog = document.getElementById('students-dialog');
            document.querySelectorAll('[data-open-students]').forEach((btn) => {
                btn.addEventListener('click', () => dialog?.showModal());
            });
            dialog?.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        </script>
    @endif
@endsection
