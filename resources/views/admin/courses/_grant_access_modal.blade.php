@php
    $pickerOptions = collect();

    foreach ($grantUsers as $u) {
        $label = $u->profile?->display_name ?: $u->email;
        $pickerOptions->push([
            'key' => 'user:'.$u->id,
            'type' => 'user',
            'id' => $u->id,
            'label' => $label,
            'meta' => $u->email,
        ]);
    }

    foreach ($grantTeams as $t) {
        $pickerOptions->push([
            'key' => 'team:'.$t->id,
            'type' => 'team',
            'id' => $t->id,
            'label' => $t->name,
            'meta' => trans_choice(':count member|:count members', $t->users_count ?? 0, ['count' => $t->users_count ?? 0]),
        ]);
    }

    $selectedKeys = collect($grantedUserIds ?? [])
        ->map(fn ($id) => 'user:'.$id)
        ->merge(collect($grantedTeamIds ?? [])->map(fn ($id) => 'team:'.$id))
        ->values()
        ->all();

    $dialogId = 'grant-access-dialog-'.$course->id;
@endphp

<x-modal :id="$dialogId" :title="__('Grant access')" :subtitle="$course->title" size="xl">
    <div
        class="flex min-h-0 flex-1 flex-col"
        x-data="courseAccessPicker(@js($pickerOptions), @js($selectedKeys))"
    >
        <form method="POST" action="{{ route('admin.courses.access.update', $course) }}" class="flex min-h-0 flex-1 flex-col" @submit="syncHidden()">
            @csrf
            @method('PUT')

            <template x-for="key in selected" :key="key">
                <input type="hidden" :name="key.startsWith('user:') ? 'user_ids[]' : 'team_ids[]'" :value="key.split(':')[1]">
            </template>

            <div class="flex min-h-0 flex-1 flex-col gap-5 overflow-hidden px-6 py-5">
                {{-- Add people / teams --}}
                <div class="shrink-0 space-y-2">
                    <label class="block text-sm font-medium text-zinc-800">{{ __('Add people or teams') }}</label>
                    <input
                        x-ref="addSearch"
                        type="search"
                        x-model="addQuery"
                        @keydown.enter.prevent="pickHighlighted()"
                        @keydown.arrow-down.prevent="move(1)"
                        @keydown.arrow-up.prevent="move(-1)"
                        placeholder="{{ __('Search to add…') }}"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                        autocomplete="off"
                    >
                    <div class="max-h-36 overflow-y-auto rounded-lg border border-zinc-200 bg-zinc-50/80 overscroll-contain">
                        <template x-if="addCandidates.length === 0">
                            <p class="px-3 py-4 text-center text-sm text-zinc-500" x-text="addQuery.trim() ? '{{ __('No matches.') }}' : '{{ __('Everyone available is already on the list.') }}'"></p>
                        </template>
                        <template x-for="(item, index) in addCandidates" :key="item.key">
                            <button
                                type="button"
                                class="flex w-full items-start gap-2 border-b border-zinc-100 px-3 py-2 text-left text-sm last:border-0 hover:bg-white"
                                :class="index === highlight ? 'bg-emerald-50' : ''"
                                @click="add(item.key)"
                                @mouseenter="highlight = index"
                            >
                                <span class="mt-0.5 rounded bg-white px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-zinc-600 ring-1 ring-zinc-200" x-text="item.type === 'team' ? '{{ __('Team') }}' : '{{ __('User') }}'"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block font-medium text-zinc-900" x-text="item.label"></span>
                                    <span class="block truncate text-xs text-zinc-500" x-text="item.meta"></span>
                                </span>
                                <span class="shrink-0 text-xs font-medium text-emerald-700">{{ __('Add') }}</span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Who has access — searchable table --}}
                <div class="flex min-h-0 flex-1 flex-col gap-2">
                    <div class="flex shrink-0 flex-wrap items-center justify-between gap-2">
                        <label class="text-sm font-medium text-zinc-800">
                            {{ __('Who has access') }}
                            <span class="font-normal text-zinc-500" x-text="'(' + selected.length + ')'"></span>
                        </label>
                        <input
                            type="search"
                            x-model="tableQuery"
                            placeholder="{{ __('Filter access list…') }}"
                            class="w-full max-w-xs rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 sm:w-64"
                            autocomplete="off"
                        >
                    </div>

                    <div class="min-h-0 flex-1 overflow-auto rounded-lg border border-zinc-200 overscroll-contain">
                        <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                            <thead class="sticky top-0 bg-zinc-50 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                <tr>
                                    <th class="px-3 py-2.5">{{ __('Type') }}</th>
                                    <th class="px-3 py-2.5">{{ __('Name') }}</th>
                                    <th class="hidden px-3 py-2.5 sm:table-cell">{{ __('Detail') }}</th>
                                    <th class="px-3 py-2.5 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 bg-white">
                                <tr x-show="selected.length === 0">
                                    <td colspan="4" class="px-3 py-10 text-center text-zinc-500">{{ __('No one has access yet. Add people or teams above.') }}</td>
                                </tr>
                                <tr x-show="selected.length > 0 && tableRows.length === 0">
                                    <td colspan="4" class="px-3 py-10 text-center text-zinc-500">{{ __('No matches.') }}</td>
                                </tr>
                                <template x-for="item in tableRows" :key="item.key">
                                    <tr class="hover:bg-zinc-50/80">
                                        <td class="px-3 py-2.5">
                                            <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-zinc-600" x-text="item.type === 'team' ? '{{ __('Team') }}' : '{{ __('User') }}'"></span>
                                        </td>
                                        <td class="px-3 py-2.5 font-medium text-zinc-900" x-text="item.label"></td>
                                        <td class="hidden px-3 py-2.5 text-zinc-500 sm:table-cell" x-text="item.meta"></td>
                                        <td class="px-3 py-2.5 text-right">
                                            <button
                                                type="button"
                                                class="rounded-md border border-red-200 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-50"
                                                @click="remove(item.key)"
                                            >{{ __('Remove') }}</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <p class="shrink-0 text-xs text-zinc-500">{{ __('Save replaces the access list for this course. Removing someone sends a notification.') }}</p>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-zinc-200 px-6 py-4">
                <button type="button" data-modal-close class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50">{{ __('Cancel') }}</button>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Save access') }}</button>
            </div>
        </form>
    </div>
</x-modal>
