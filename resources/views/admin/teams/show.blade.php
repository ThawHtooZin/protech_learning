@extends('layouts.admin')

@section('title', $team->name)
@section('heading', $team->name)

@section('content')
    @php
        $tileColors = ['bg-emerald-600', 'bg-sky-600', 'bg-amber-600', 'bg-violet-600', 'bg-rose-600', 'bg-teal-600'];
        $tile = $tileColors[($team->id - 1) % count($tileColors)];
        $memberIds = $team->users->pluck('id');
        $availableInstructors = $instructors->reject(fn ($user) => $memberIds->contains($user->id))->values();
        $availableStudents = $students->reject(fn ($user) => $memberIds->contains($user->id))->values();
        $teamInstructors = $team->users->filter(fn ($user) => $user->isInstructor())->sortBy(fn ($m) => $m->profile?->display_name ?? $m->email);
        $teamStudents = $team->users->filter(fn ($user) => $user->isStudent())->sortBy(fn ($m) => $m->profile?->display_name ?? $m->email);
    @endphp

    <div class="mb-6">
        <a href="{{ route('admin.users.index', ['tab' => 'teams']) }}" class="text-sm text-emerald-400 hover:underline">
            ← {{ __('Back to teams') }}
        </a>
    </div>

    <header class="mb-8 flex flex-wrap items-start justify-between gap-4 border-b border-zinc-800 pb-6">
        <div class="flex min-w-0 items-start gap-4">
            <span class="{{ $tile }} flex h-16 w-16 shrink-0 items-center justify-center rounded-xl text-2xl font-bold text-white">
                {{ strtoupper(mb_substr($team->name, 0, 1)) }}
            </span>
            <div class="min-w-0">
                <h2 class="truncate text-2xl font-semibold text-white">{{ $team->name }}</h2>
                <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-zinc-400">
                    <span>{{ trans_choice(':count member|:count members', $team->users_count, ['count' => $team->users_count]) }}</span>
                    <span>{{ __(':count instructors', ['count' => $instructorCount]) }}</span>
                    <span>{{ __(':count students', ['count' => $studentCount]) }}</span>
                </p>
                <p class="mt-2 text-xs text-zinc-500">{{ __('Roles come from each account. Here you assign people to this team.') }}</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" data-open-assign="instructors"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('Add instructors') }}
            </button>
            <button type="button" data-open-assign="students"
                class="rounded-lg border border-zinc-600 px-4 py-2 text-sm font-semibold text-zinc-100 hover:bg-zinc-800">
                {{ __('Add students') }}
            </button>
        </div>
    </header>

    <section class="mb-8 rounded-xl border border-white/5 bg-panel">
        <div class="flex flex-col gap-3 border-b border-zinc-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-sm font-semibold text-white">{{ __('Members') }}</h3>
            <label class="sr-only" for="team-member-filter">{{ __('Filter members') }}</label>
            <input id="team-member-filter" type="search" placeholder="{{ __('Filter members...') }}"
                class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-1.5 text-sm text-white placeholder:text-zinc-500 sm:max-w-xs">
        </div>

        @if($team->users->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-sm text-zinc-400">{{ __('No members yet.') }}</p>
                <div class="mt-4 flex flex-wrap justify-center gap-3">
                    <button type="button" data-open-assign="instructors" class="text-sm font-medium text-emerald-400 hover:text-emerald-300">
                        {{ __('Add instructors') }}
                    </button>
                    <span class="text-zinc-600">·</span>
                    <button type="button" data-open-assign="students" class="text-sm font-medium text-emerald-400 hover:text-emerald-300">
                        {{ __('Add students') }}
                    </button>
                </div>
            </div>
        @else
            <div class="px-5 py-2" id="team-member-list">
                <div data-member-group class="py-3">
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">{{ __('Instructors') }}</p>
                    @forelse($teamInstructors as $member)
                        <div data-member-row class="flex items-center gap-3 border-b border-zinc-800/80 py-3 last:border-0"
                            data-search-text="{{ strtolower(($member->profile?->display_name ?? '').' '.$member->email.' instructor') }}">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-sm font-semibold text-zinc-200">
                                {{ strtoupper(mb_substr($member->profile?->display_name ?? $member->email, 0, 1)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-white">{{ $member->profile?->display_name ?? $member->email }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $member->email }}</p>
                            </div>
                            <span class="rounded-md bg-sky-950 px-2 py-1 text-xs text-sky-300">{{ __('Instructor') }}</span>
                            <form method="POST" action="{{ route('admin.teams.members.detach', [$team, $member]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md px-2 py-1.5 text-xs text-zinc-400 hover:bg-zinc-800 hover:text-red-300"
                                    aria-label="{{ __('Remove :name', ['name' => $member->profile?->display_name ?? $member->email]) }}">
                                    {{ __('Remove') }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="py-3 text-sm text-zinc-500">{{ __('No instructors on this team yet.') }}</p>
                    @endforelse
                </div>

                <div data-member-group class="border-t border-zinc-800 py-3">
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">{{ __('Students') }}</p>
                    @forelse($teamStudents as $member)
                        <div data-member-row class="flex items-center gap-3 border-b border-zinc-800/80 py-3 last:border-0"
                            data-search-text="{{ strtolower(($member->profile?->display_name ?? '').' '.$member->email.' student') }}">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-sm font-semibold text-zinc-200">
                                {{ strtoupper(mb_substr($member->profile?->display_name ?? $member->email, 0, 1)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-white">{{ $member->profile?->display_name ?? $member->email }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $member->email }}</p>
                            </div>
                            <span class="rounded-md bg-zinc-800 px-2 py-1 text-xs text-zinc-300">{{ __('Student') }}</span>
                            <form method="POST" action="{{ route('admin.teams.members.detach', [$team, $member]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md px-2 py-1.5 text-xs text-zinc-400 hover:bg-zinc-800 hover:text-red-300"
                                    aria-label="{{ __('Remove :name', ['name' => $member->profile?->display_name ?? $member->email]) }}">
                                    {{ __('Remove') }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="py-3 text-sm text-zinc-500">{{ __('No students on this team yet.') }}</p>
                    @endforelse
                </div>
            </div>
            <p id="no-member-filter-matches" hidden class="px-5 py-8 text-center text-sm text-zinc-500">{{ __('No members match your filter.') }}</p>
        @endif
    </section>

    <section class="mb-8 rounded-xl border border-white/5 bg-panel p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-white">{{ __('Assignments') }}</h3>
                <p class="mt-1 text-xs text-zinc-500">{{ __('Create and review file assignments for this team.') }}</p>
            </div>
            <a href="{{ route('teams.show', $team) }}"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('Open team workspace') }}
            </a>
        </div>
    </section>

    <section class="rounded-xl border border-white/5 bg-panel p-5">
        <h3 class="text-sm font-semibold text-white">{{ __('Settings') }}</h3>
        <p class="mt-1 text-xs text-zinc-500">{{ __('Rename or delete this team. Members stay in the system if you delete it.') }}</p>

        <form method="POST" action="{{ route('admin.teams.update', $team) }}" class="mt-5 max-w-md space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label for="rename-team" class="block text-sm font-medium text-zinc-300">{{ __('Team name') }}</label>
                <input id="rename-team" type="text" name="name" value="{{ old('name', $team->name) }}" required maxlength="120"
                    class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="rounded-lg border border-zinc-600 px-4 py-2 text-sm font-semibold text-zinc-100 hover:bg-zinc-800">
                {{ __('Save name') }}
            </button>
        </form>

        <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" class="mt-8 border-t border-zinc-800 pt-6"
            onsubmit="return confirm(@json(__('Delete this team? Members will remain in the system.')));">
            @csrf
            @method('DELETE')
            <p class="text-sm font-medium text-red-300">{{ __('Delete team') }}</p>
            <p class="mt-1 text-xs text-zinc-500">{{ __('This cannot be undone.') }}</p>
            <button type="submit" class="mt-3 rounded-lg border border-red-900/60 px-4 py-2 text-sm text-red-300 hover:bg-red-950/40">
                {{ __('Delete team') }}
            </button>
        </form>
    </section>

    <dialog id="assign-dialog" aria-labelledby="assign-dialog-title" class="admin-dialog">
        <div class="p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="assign-dialog-title" class="text-lg font-semibold text-white">{{ __('Assign people') }}</h2>
                    <p class="mt-1 text-sm text-zinc-400">{{ __('Pick instructors or students to add to :team.', ['team' => $team->name]) }}</p>
                </div>
                <form method="dialog">
                    <button type="submit" class="rounded-md px-3 py-1.5 text-sm text-zinc-300 hover:bg-zinc-800">{{ __('Close') }}</button>
                </form>
            </div>

            <div class="mt-5 flex gap-2 border-b border-zinc-800" role="tablist">
                <button type="button" data-assign-tab="instructors"
                    class="border-b-2 border-emerald-500 px-3 pb-2 text-sm font-semibold text-white">
                    {{ __('Instructors') }}
                </button>
                <button type="button" data-assign-tab="students"
                    class="border-b-2 border-transparent px-3 pb-2 text-sm font-semibold text-zinc-500 hover:text-zinc-200">
                    {{ __('Students') }}
                </button>
            </div>

            <form method="POST" action="{{ route('admin.teams.members.attach', $team) }}" class="mt-4 space-y-4">
                @csrf
                <label class="sr-only" for="assign-search">{{ __('Search people') }}</label>
                <input id="assign-search" type="search" data-member-search placeholder="{{ __('Search by name or email...') }}"
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white placeholder:text-zinc-500">

                <div data-assign-panel="instructors" class="max-h-72 space-y-1 overflow-y-auto rounded-lg border border-zinc-800 p-2">
                    @forelse($availableInstructors as $instructor)
                        <label data-member-option class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-sm text-zinc-300 hover:bg-zinc-800">
                            <input type="checkbox" name="instructor_ids[]" value="{{ $instructor->id }}"
                                class="rounded border-zinc-600 text-emerald-600 focus:ring-emerald-600">
                            <span class="min-w-0 flex-1 truncate">{{ $instructor->profile?->display_name ?? $instructor->email }}</span>
                            <span class="hidden text-xs text-zinc-500 sm:inline">{{ $instructor->email }}</span>
                        </label>
                    @empty
                        <p class="px-2 py-6 text-center text-sm text-zinc-500">
                            {{ __('No instructor accounts left to add.') }}
                            <a href="{{ route('admin.users.index', ['tab' => 'users']) }}" class="mt-2 block text-emerald-400 hover:underline">
                                {{ __('Create an instructor under All users') }}
                            </a>
                        </p>
                    @endforelse
                    <p data-no-member-matches hidden class="px-2 py-4 text-center text-xs text-zinc-500">{{ __('No matches found.') }}</p>
                </div>

                <div data-assign-panel="students" hidden class="max-h-72 space-y-1 overflow-y-auto rounded-lg border border-zinc-800 p-2">
                    @forelse($availableStudents as $student)
                        <label data-member-option class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-sm text-zinc-300 hover:bg-zinc-800">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                class="rounded border-zinc-600 text-emerald-600 focus:ring-emerald-600">
                            <span class="min-w-0 flex-1 truncate">{{ $student->profile?->display_name ?? $student->email }}</span>
                            <span class="hidden text-xs text-zinc-500 sm:inline">{{ $student->email }}</span>
                        </label>
                    @empty
                        <p class="px-2 py-6 text-center text-sm text-zinc-500">
                            {{ __('No student accounts left to add.') }}
                            <a href="{{ route('admin.users.index', ['tab' => 'users']) }}" class="mt-2 block text-emerald-400 hover:underline">
                                {{ __('Create a student under All users') }}
                            </a>
                        </p>
                    @endforelse
                    <p data-no-member-matches hidden class="px-2 py-4 text-center text-xs text-zinc-500">{{ __('No matches found.') }}</p>
                </div>

                @error('instructor_ids.*')<p class="text-sm text-red-400">{{ $message }}</p>@enderror
                @error('student_ids.*')<p class="text-sm text-red-400">{{ $message }}</p>@enderror

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        {{ __('Add selected') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        const assignDialog = document.getElementById('assign-dialog');
        const tabButtons = [...document.querySelectorAll('[data-assign-tab]')];
        const panels = {
            instructors: document.querySelector('[data-assign-panel="instructors"]'),
            students: document.querySelector('[data-assign-panel="students"]'),
        };

        const setAssignTab = (tab) => {
            tabButtons.forEach((btn) => {
                const active = btn.dataset.assignTab === tab;
                btn.classList.toggle('border-emerald-500', active);
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('border-transparent', !active);
                btn.classList.toggle('text-zinc-500', !active);
            });
            Object.entries(panels).forEach(([key, panel]) => {
                if (panel) panel.hidden = key !== tab;
            });
            const search = document.getElementById('assign-search');
            if (search) {
                search.value = '';
                search.dispatchEvent(new Event('input'));
            }
        };

        const openAssign = (tab) => {
            setAssignTab(tab);
            assignDialog?.showModal();
        };

        document.querySelectorAll('[data-open-assign]').forEach((btn) => {
            btn.addEventListener('click', () => openAssign(btn.dataset.openAssign));
        });
        tabButtons.forEach((btn) => btn.addEventListener('click', () => setAssignTab(btn.dataset.assignTab)));

        assignDialog?.addEventListener('click', (event) => {
            if (event.target === assignDialog) assignDialog.close();
        });

        @if($errors->has('instructor_ids.0'))
            openAssign('instructors');
        @elseif($errors->has('student_ids.0'))
            openAssign('students');
        @endif

        const memberFilter = document.getElementById('team-member-filter');
        const memberRows = [...document.querySelectorAll('[data-member-row]')];
        const noFilterMatches = document.getElementById('no-member-filter-matches');
        memberFilter?.addEventListener('input', () => {
            const query = memberFilter.value.trim().toLowerCase();
            let visible = 0;
            memberRows.forEach((row) => {
                const matches = (row.dataset.searchText || '').includes(query);
                row.hidden = !matches;
                visible += matches ? 1 : 0;
            });
            if (noFilterMatches) noFilterMatches.hidden = visible > 0 || memberRows.length === 0;
        });

        const search = document.getElementById('assign-search');
        search?.addEventListener('input', () => {
            const query = search.value.trim().toLowerCase();
            const activePanel = Object.values(panels).find((panel) => panel && !panel.hidden);
            if (!activePanel) return;
            const options = [...activePanel.querySelectorAll('[data-member-option]')];
            const noMatches = activePanel.querySelector('[data-no-member-matches]');
            let visibleCount = 0;
            options.forEach((option) => {
                const matches = option.textContent.toLowerCase().includes(query);
                option.hidden = !matches;
                visibleCount += matches ? 1 : 0;
            });
            if (noMatches) noMatches.hidden = visibleCount > 0 || options.length === 0;
        });
    </script>
@endsection
