<div>
    <label for="title" class="block text-sm font-medium text-zinc-700">{{ __('Title') }}</label>
    <input id="title" type="text" name="title" value="{{ old('title', $assignment->title ?? '') }}" required maxlength="200"
        class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900">
    @error('title')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
</div>

<div>
    <label for="instructions" class="block text-sm font-medium text-zinc-700">{{ __('Instructions') }}</label>
    <textarea id="instructions" name="instructions" rows="6"
        class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900">{{ old('instructions', $assignment->instructions ?? '') }}</textarea>
    @error('instructions')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
</div>

<div>
    <label for="due_at" class="block text-sm font-medium text-zinc-700">{{ __('Due date (optional)') }}</label>
    <input id="due_at" type="datetime-local" name="due_at"
        value="{{ old('due_at', isset($assignment) && $assignment->due_at ? $assignment->due_at->format('Y-m-d\\TH:i') : '') }}"
        class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900">
    @error('due_at')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
</div>

<div>
    <label for="resources" class="block text-sm font-medium text-zinc-700">{{ __('Resources (optional)') }}</label>
    <input id="resources" type="file" name="resources[]" multiple
        class="mt-1.5 block w-full text-sm text-zinc-600 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:text-zinc-900">
    <p class="mt-1 text-xs text-zinc-500">
        {{ __('Any file type. Up to :max files, :size MB each.', [
            'max' => config('lms.assignments.max_files'),
            'size' => (int) (config('lms.assignments.max_file_kb') / 1024),
        ]) }}
    </p>
    @error('resources')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
    @error('resources.*')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
</div>
