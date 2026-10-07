@extends('layouts.admin')

@section('title', __('Purchases'))

@section('heading', __('Purchases'))

@section('content')
    <p class="mb-6 text-sm text-zinc-600">{{ __('Review bank slips for public course purchases. Approve to enroll the learner.') }}</p>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-panel">
        <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
            <thead class="bg-rail text-xs font-semibold uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Learner') }}</th>
                    <th class="px-4 py-3">{{ __('Course') }}</th>
                    <th class="hidden px-4 py-3 sm:table-cell">{{ __('Status') }}</th>
                    <th class="hidden px-4 py-3 md:table-cell">{{ __('Submitted') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200">
                @forelse($purchases as $purchase)
                    <tr class="hover:bg-zinc-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-zinc-900">{{ $purchase->user?->profile?->display_name ?: $purchase->user?->email }}</p>
                            <p class="text-xs text-zinc-500">{{ $purchase->user?->email }}</p>
                        </td>
                        <td class="px-4 py-3 text-zinc-800">{{ $purchase->course?->title }}</td>
                        <td class="hidden px-4 py-3 sm:table-cell">
                            @if($purchase->status->value === 'pending')
                                <span class="rounded bg-amber-50 px-2 py-0.5 text-xs text-amber-800">{{ __('Pending') }}</span>
                            @elseif($purchase->status->value === 'approved')
                                <span class="rounded bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">{{ __('Approved') }}</span>
                            @else
                                <span class="rounded bg-red-50 px-2 py-0.5 text-xs text-red-600">{{ __('Rejected') }}</span>
                            @endif
                        </td>
                        <td class="hidden px-4 py-3 text-zinc-500 md:table-cell">{{ $purchase->created_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('admin.purchases.slip', $purchase) }}" class="rounded-md border border-zinc-300 px-3 py-1.5 text-xs text-zinc-800 hover:bg-zinc-100" target="_blank">{{ __('View slip') }}</a>
                                @if($purchase->isPending())
                                    <form method="POST" action="{{ route('admin.purchases.approve', $purchase) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs text-white hover:bg-emerald-500">{{ __('Approve') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.purchases.reject', $purchase) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded-md border border-red-300 px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">{{ __('Reject') }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-zinc-500">{{ __('No purchases yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchases->hasPages())
        <div class="mt-6">{{ $purchases->links() }}</div>
    @endif
@endsection
