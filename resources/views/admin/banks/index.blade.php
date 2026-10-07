@extends('layouts.admin')

@section('title', __('Bank accounts'))

@section('heading', __('Bank accounts'))

@section('content')
    <p class="mb-6 text-sm text-zinc-600">{{ __('Shown on the Buy modal for public courses. Add one or more accounts.') }}</p>

    <div class="mb-10 max-w-xl rounded-xl border border-zinc-200 bg-white p-5">
        <h2 class="text-sm font-semibold text-zinc-900">{{ __('Add bank account') }}</h2>
        <form method="POST" action="{{ route('admin.banks.store') }}" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-sm text-zinc-600">{{ __('Bank') }}</label>
                <input type="text" name="name" required value="{{ old('name') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-zinc-900" placeholder="KBZPay">
            </div>
            <div>
                <label class="block text-sm text-zinc-600">{{ __('Account name') }}</label>
                <input type="text" name="account_name" required value="{{ old('account_name') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-zinc-900">
            </div>
            <div>
                <label class="block text-sm text-zinc-600">{{ __('Account number') }}</label>
                <input type="text" name="account_number" required value="{{ old('account_number') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-zinc-900">
            </div>
            <div>
                <label class="block text-sm text-zinc-600">{{ __('Note') }}</label>
                <input type="text" name="note" value="{{ old('note') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-zinc-900">
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <div>
                    <label class="block text-sm text-zinc-600">{{ __('Sort') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="mt-1 w-24 rounded-md border border-zinc-300 px-3 py-2 text-zinc-900">
                </div>
                <label class="mt-6 flex items-center gap-2 text-sm text-zinc-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-zinc-300" checked>
                    {{ __('Active') }}
                </label>
            </div>
            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">{{ __('Add') }}</button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($accounts as $account)
            <form method="POST" action="{{ route('admin.banks.update', $account) }}" class="rounded-xl border border-zinc-200 bg-white p-5">
                @csrf
                @method('PUT')
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm text-zinc-600">{{ __('Bank') }}</label>
                        <input type="text" name="name" required value="{{ old('name', $account->name) }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-zinc-900">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-600">{{ __('Account name') }}</label>
                        <input type="text" name="account_name" required value="{{ old('account_name', $account->account_name) }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-zinc-900">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-600">{{ __('Account number') }}</label>
                        <input type="text" name="account_number" required value="{{ old('account_number', $account->account_number) }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-zinc-900">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-600">{{ __('Note') }}</label>
                        <input type="text" name="note" value="{{ old('note', $account->note) }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-zinc-900">
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-4">
                    <div>
                        <label class="block text-sm text-zinc-600">{{ __('Sort') }}</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $account->sort_order) }}" min="0" class="mt-1 w-24 rounded-md border border-zinc-300 px-3 py-2 text-zinc-900">
                    </div>
                    <label class="mt-6 flex items-center gap-2 text-sm text-zinc-700">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-zinc-300" @checked(old('is_active', $account->is_active))>
                        {{ __('Active') }}
                    </label>
                    <div class="mt-6 flex flex-1 flex-wrap justify-end gap-2">
                        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-500">{{ __('Save') }}</button>
                    </div>
                </div>
            </form>
            <form method="POST" action="{{ route('admin.banks.destroy', $account) }}" class="-mt-2 mb-6 flex justify-end">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:underline" data-confirm="{{ __('Delete this bank account?') }}" data-confirm-danger>{{ __('Delete') }}</button>
            </form>
        @empty
            <p class="text-sm text-zinc-500">{{ __('No bank accounts yet.') }}</p>
        @endforelse
    </div>
@endsection
