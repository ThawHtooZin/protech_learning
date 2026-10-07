@extends('layouts.admin')

@section('title', __('Courses'))

@section('heading', __('Courses'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-zinc-600">{{ __('Create, edit, or remove courses. Use “Edit” to add modules and lessons.') }}</p>
        <a href="{{ route('admin.courses.create') }}" class="shrink-0 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">{{ __('New course') }}</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-panel">
        <table class="min-w-full divide-y divide-zinc-800 text-left text-sm">
            <thead class="bg-rail text-xs font-semibold uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Title') }}</th>
                    <th class="hidden px-4 py-3 sm:table-cell">{{ __('Access') }}</th>
                    <th class="hidden px-4 py-3 sm:table-cell">{{ __('Status') }}</th>
                    <th class="hidden px-4 py-3 md:table-cell">{{ __('Modules') }}</th>
                    <th class="hidden px-4 py-3 lg:table-cell">{{ __('Updated') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse($courses as $c)
                    <tr class="hover:bg-zinc-100/40">
                        <td class="px-4 py-3 font-medium text-zinc-900">{{ $c->title }}</td>
                        <td class="hidden px-4 py-3 sm:table-cell">
                            @if($c->isPublic())
                                <span class="text-xs text-zinc-700">{{ __('Public') }}@if($c->price_mmk !== null) · {{ number_format($c->price_mmk) }} {{ __('MMK') }}@endif</span>
                            @else
                                <span class="text-xs text-zinc-700">{{ __('Private') }}@unless($c->is_listed) · {{ __('Unlisted') }}@endunless</span>
                            @endif
                        </td>
                        <td class="hidden px-4 py-3 sm:table-cell">
                            @if($c->is_published)
                                <span class="rounded bg-emerald-50/80 px-2 py-0.5 text-xs text-emerald-700">{{ __('Published') }}</span>
                            @else
                                <span class="rounded bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600">{{ __('Draft') }}</span>
                            @endif
                        </td>
                        <td class="hidden px-4 py-3 text-zinc-600 md:table-cell">{{ $c->modules_count }}</td>
                        <td class="hidden px-4 py-3 text-zinc-500 lg:table-cell">{{ $c->updated_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-wrap justify-end gap-2">
                                <button type="button" class="rounded-md border border-emerald-300 px-3 py-1.5 text-xs text-emerald-800 hover:bg-emerald-50" onclick="ProtechModal.open('grant-access-dialog-{{ $c->id }}')">{{ __('Grant access') }}</button>
                                <a href="{{ route('admin.courses.edit', $c) }}" class="rounded-md border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.courses.destroy', $c) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-red-300 px-3 py-1.5 text-xs text-red-400 hover:bg-red-50" data-confirm="{{ __('Delete this course and all its modules, lessons, and enrollments? This cannot be undone.') }}" data-confirm-danger>{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-zinc-500">{{ __('No courses yet.') }} <a href="{{ route('admin.courses.create') }}" class="text-emerald-700 hover:underline">{{ __('Create one') }}</a></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($courses->hasPages())
        <div class="mt-6">{{ $courses->links() }}</div>
    @endif

    @foreach($courses as $c)
        @include('admin.courses._grant_access_modal', [
            'course' => $c,
            'grantUsers' => $grantUsers,
            'grantTeams' => $grantTeams,
            'grantedUserIds' => $c->enrollments->pluck('user_id')->all(),
            'grantedTeamIds' => $c->teams->pluck('id')->all(),
        ])
    @endforeach
@endsection
