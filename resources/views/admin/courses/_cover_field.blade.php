<div>
    <label class="block text-sm text-zinc-600">{{ __('Cover image') }}</label>
    @if(!empty($course?->cover_path))
        @if($course->coverUrl())
            <img
                src="{{ $course->coverUrl() }}"
                alt=""
                class="mt-2 h-28 w-full max-w-md rounded-lg object-cover ring-1 ring-zinc-200"
                onerror="this.classList.add('hidden'); this.nextElementSibling?.classList.remove('hidden');"
            >
            <p class="mt-2 hidden text-xs text-amber-700">{{ __('Cover file missing — upload again or remove.') }}</p>
        @else
            <p class="mt-2 text-xs text-amber-700">{{ __('Cover file missing — upload again or remove.') }}</p>
        @endif
        <label class="mt-2 flex items-center gap-2 text-sm text-zinc-700">
            <input type="checkbox" name="remove_cover" value="1" class="rounded border-zinc-300">
            {{ __('Remove cover') }}
        </label>
    @endif
    <input type="file" name="cover" accept="image/*" class="mt-2 block w-full text-sm text-zinc-700">
    <p class="mt-1 text-xs text-zinc-500">{{ __('JPG, PNG, or WebP. Max 5 MB.') }}</p>
</div>
