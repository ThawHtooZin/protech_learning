{{-- Left: title + instructions (Teams-style focus) --}}
<div class="min-w-0 flex-1 space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8">
    <div>
        <label for="title" class="sr-only">{{ __('Title') }}</label>
        <input id="title" type="text" name="title" value="{{ old('title', $assignment->title ?? '') }}" required maxlength="200"
            placeholder="{{ __('Title') }}"
            class="w-full border-0 border-b border-zinc-200 bg-transparent px-0 py-2 text-2xl font-semibold tracking-tight text-zinc-900 placeholder:font-normal placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-0">
        @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="instructions" class="mb-2 block text-sm font-medium text-zinc-600">{{ __('Instructions') }}</label>
        <textarea id="instructions" name="instructions" rows="16"
            placeholder="{{ __('Enter instructions.') }}"
            class="min-h-[22rem] w-full resize-y rounded-xl border border-zinc-200 bg-zinc-50/80 px-4 py-3 text-sm leading-relaxed text-zinc-900 placeholder:text-zinc-400 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('instructions', $assignment->instructions ?? '') }}</textarea>
        @error('instructions')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

{{-- Right: due date + resources --}}
<aside class="w-full shrink-0 space-y-4 lg:w-72">
    <div class="rounded-2xl border border-zinc-200 bg-white p-5">
        <label for="due_at" class="flex items-center gap-2 text-sm font-medium text-zinc-700">
            <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ __('Due date (optional)') }}
        </label>
        <input id="due_at" type="datetime-local" name="due_at"
            value="{{ old('due_at', isset($assignment) && $assignment->due_at ? $assignment->due_at->format('Y-m-d\\TH:i') : '') }}"
            class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        @error('due_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-5">
        <label for="resources" class="flex items-center gap-2 text-sm font-medium text-zinc-700">
            <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
            {{ __('Resources (optional)') }}
        </label>
        <input id="resources" type="file" name="resources[]" multiple
            class="mt-2 block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100">
        <p class="mt-2 text-xs leading-relaxed text-zinc-500">
            {{ __('Any file type. Up to :max files, :size MB each.', [
                'max' => config('lms.assignments.max_files'),
                'size' => (int) (config('lms.assignments.max_file_kb') / 1024),
            ]) }}
        </p>
        @error('resources')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('resources.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

        @isset($assignment)
            @if($assignment->attachments->isNotEmpty())
                <div class="mt-4 border-t border-zinc-100 pt-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Existing resources') }}</p>
                    <ul class="mt-2 space-y-1.5 text-sm text-zinc-700">
                        @foreach($assignment->attachments as $attachment)
                            <li class="truncate">{{ $attachment->original_name }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endisset
    </div>
</aside>
