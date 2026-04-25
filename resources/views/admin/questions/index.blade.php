@extends('layouts.admin')

@section('title', __('Question bank'))

@section('heading', __('Question bank'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="max-w-2xl text-sm text-zinc-400">{{ __('Multiple-choice items you can attach to quizzes. Search, filter, and edit questions here. From a lesson quiz, use “Edit” on a question to return to the quiz after saving.') }}</p>
        <a href="{{ route('admin.questions.create') }}" class="shrink-0 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">{{ __('Add question') }}</a>
    </div>

    <form method="GET" action="{{ route('admin.questions.index') }}" class="mb-6 space-y-4 rounded-xl border border-zinc-800 bg-zinc-900/40 p-4">
        <div class="flex flex-wrap gap-3">
            <div class="min-w-[12rem] flex-1">
                <label for="q-bank-search" class="block text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Search') }}</label>
                <input id="q-bank-search" type="search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Question text, technology, topic…') }}"
                    class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white placeholder:text-zinc-600">
            </div>
            <div class="w-full sm:w-44">
                <label for="q-tech" class="block text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Technology') }}</label>
                <select id="q-tech" name="technology" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white">
                    <option value="">{{ __('All') }}</option>
                    @foreach($filterTechnologies as $tech)
                        <option value="{{ $tech }}" @selected($filters['technology'] === $tech)>{{ $tech }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-44">
                <label for="q-topic" class="block text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Topic') }}</label>
                <select id="q-topic" name="topic" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white">
                    <option value="">{{ __('All') }}</option>
                    @foreach($filterTopics as $t)
                        <option value="{{ $t }}" @selected($filters['topic'] === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-40">
                <label for="q-sort" class="block text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Sort') }}</label>
                <select id="q-sort" name="sort" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white">
                    <option value="id_desc" @selected($filters['sort'] === 'id_desc')>{{ __('Newest first') }}</option>
                    <option value="created_asc" @selected($filters['sort'] === 'created_asc')>{{ __('Oldest first') }}</option>
                    <option value="technology_asc" @selected($filters['sort'] === 'technology_asc')>{{ __('Technology A–Z') }}</option>
                    <option value="topic_asc" @selected($filters['sort'] === 'topic_asc')>{{ __('Topic A–Z') }}</option>
                </select>
            </div>
            <div class="w-full sm:w-32">
                <label for="q-per" class="block text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Per page') }}</label>
                <select id="q-per" name="per_page" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white">
                    @foreach([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected((int) $filters['per_page'] === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">{{ __('Apply filters') }}</button>
            <a href="{{ route('admin.questions.index') }}" class="inline-flex items-center rounded-md border border-zinc-700 px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-800">{{ __('Reset') }}</a>
        </div>
    </form>

    @if($questions->total() > 0)
        <p class="mb-3 text-sm text-zinc-500">
            {{ __(':count questions', ['count' => $questions->total()]) }}
            @if($questions->hasPages())
                — {{ __('page :current of :last', ['current' => $questions->currentPage(), 'last' => $questions->lastPage()]) }}
            @endif
        </p>
    @endif

    <div class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/40">
        <table class="min-w-full divide-y divide-zinc-800 text-left text-sm">
            <thead class="bg-zinc-900/80 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-4 py-3">{{ __('ID') }}</th>
                    <th class="px-4 py-3">{{ __('Technology') }}</th>
                    <th class="px-4 py-3">{{ __('Topic') }}</th>
                    <th class="hidden px-4 py-3 md:table-cell">{{ __('Question') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Opts') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($questions as $q)
                    <tr class="hover:bg-zinc-800/40">
                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-zinc-500">{{ $q->id }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $q->technology }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $q->topic }}</td>
                        <td class="hidden max-w-md px-4 py-3 text-zinc-400 md:table-cell">
                            <span class="line-clamp-2">{{ \Illuminate\Support\Str::limit($q->body, 140) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-zinc-500">{{ $q->options_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('admin.questions.edit', $q) }}" class="rounded-md border border-zinc-600 px-3 py-1.5 text-xs text-white hover:bg-zinc-800">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.questions.destroy', $q) }}" class="inline" onsubmit="return confirm({{ json_encode(__('Remove this question? It will be detached from any quizzes that use it. Past attempt records may be affected.')) }})">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-red-900/60 px-3 py-1.5 text-xs text-red-400 hover:bg-red-950/40">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No questions match these filters.') }}
                            <a href="{{ route('admin.questions.index') }}" class="text-emerald-400 hover:underline">{{ __('Clear filters') }}</a>
                            {{ __('or') }}
                            <a href="{{ route('admin.questions.create') }}" class="text-emerald-400 hover:underline">{{ __('add one') }}</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($questions->total() > 0 && ! $questions->hasPages())
        <p class="mt-4 text-sm text-zinc-500">{{ __('Showing all :count.', ['count' => $questions->total()]) }}</p>
    @endif

    <div class="mt-6">
        {{ $questions->links('vendor.pagination.zinc-admin') }}
    </div>
@endsection
