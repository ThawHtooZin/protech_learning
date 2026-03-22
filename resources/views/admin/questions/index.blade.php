@extends('layouts.admin')

@section('title', __('Question bank'))

@section('heading', __('Question bank'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-zinc-400">{{ __('Multiple-choice items you can attach to quizzes. Edit or remove questions here.') }}</p>
        <a href="{{ route('admin.questions.create') }}" class="shrink-0 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">{{ __('Add question') }}</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/40">
        <table class="min-w-full divide-y divide-zinc-800 text-left text-sm">
            <thead class="bg-zinc-900/80 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Technology') }}</th>
                    <th class="px-4 py-3">{{ __('Topic') }}</th>
                    <th class="hidden px-4 py-3 md:table-cell">{{ __('Question') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($questions as $q)
                    <tr class="hover:bg-zinc-800/40">
                        <td class="px-4 py-3 text-zinc-300">{{ $q->technology }}</td>
                        <td class="px-4 py-3 text-zinc-300">{{ $q->topic }}</td>
                        <td class="hidden max-w-md px-4 py-3 text-zinc-400 md:table-cell">
                            <span class="line-clamp-2">{{ \Illuminate\Support\Str::limit($q->body, 120) }}</span>
                        </td>
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
                        <td colspan="4" class="px-4 py-12 text-center text-zinc-500">{{ __('No questions yet.') }} <a href="{{ route('admin.questions.create') }}" class="text-emerald-400 hover:underline">{{ __('Add one') }}</a></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $questions->links() }}</div>
@endsection
