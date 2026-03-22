@extends('layouts.admin')

@section('title', __('Courses'))

@section('heading', __('Courses'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-zinc-400">{{ __('Create, edit, or remove courses. Use “Edit” to add modules, lessons, and quizzes.') }}</p>
        <a href="{{ route('admin.courses.create') }}" class="shrink-0 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">{{ __('New course') }}</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/40">
        <table class="min-w-full divide-y divide-zinc-800 text-left text-sm">
            <thead class="bg-zinc-900/80 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Title') }}</th>
                    <th class="hidden px-4 py-3 sm:table-cell">{{ __('Status') }}</th>
                    <th class="hidden px-4 py-3 md:table-cell">{{ __('Modules') }}</th>
                    <th class="hidden px-4 py-3 lg:table-cell">{{ __('Updated') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($courses as $c)
                    <tr class="hover:bg-zinc-800/40">
                        <td class="px-4 py-3 font-medium text-white">{{ $c->title }}</td>
                        <td class="hidden px-4 py-3 sm:table-cell">
                            @if($c->is_published)
                                <span class="rounded bg-emerald-950/80 px-2 py-0.5 text-xs text-emerald-300">{{ __('Published') }}</span>
                            @else
                                <span class="rounded bg-zinc-800 px-2 py-0.5 text-xs text-zinc-400">{{ __('Draft') }}</span>
                            @endif
                        </td>
                        <td class="hidden px-4 py-3 text-zinc-400 md:table-cell">{{ $c->modules_count }}</td>
                        <td class="hidden px-4 py-3 text-zinc-500 lg:table-cell">{{ $c->updated_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('admin.courses.edit', $c) }}" class="rounded-md border border-zinc-600 px-3 py-1.5 text-xs text-white hover:bg-zinc-800">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.courses.destroy', $c) }}" class="inline" onsubmit="return confirm({{ json_encode(__('Delete this course and all its modules, lessons, quizzes, and enrollments? This cannot be undone.')) }})">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-red-900/60 px-3 py-1.5 text-xs text-red-400 hover:bg-red-950/40">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-zinc-500">{{ __('No courses yet.') }} <a href="{{ route('admin.courses.create') }}" class="text-emerald-400 hover:underline">{{ __('Create one') }}</a></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($courses->hasPages())
        <div class="mt-6">{{ $courses->links() }}</div>
    @endif
@endsection
