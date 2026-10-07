@props([
    'id',
    'title',
    'subtitle' => null,
    'size' => 'md', // sm | md | lg | xl
])

@php
    $sizeClass = match ($size) {
        'sm' => 'ui-modal--sm',
        'lg' => 'ui-modal--lg',
        'xl' => 'ui-modal--xl',
        default => 'ui-modal--md',
    };
@endphp

<dialog
    id="{{ $id }}"
    class="ui-modal {{ $sizeClass }}"
    aria-labelledby="{{ $id }}-title"
    {{ $attributes }}
>
    <div class="ui-modal__panel flex max-h-[inherit] flex-col">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-zinc-200 px-6 py-5">
            <div class="min-w-0">
                <h2 id="{{ $id }}-title" class="text-xl font-semibold text-zinc-900">{{ $title }}</h2>
                @if($subtitle)
                    <p class="mt-1 text-sm text-zinc-500">{{ $subtitle }}</p>
                @endif
            </div>
            <button
                type="button"
                data-modal-close
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-zinc-300 bg-white text-xl font-semibold leading-none text-zinc-800 hover:bg-zinc-100 hover:text-zinc-950"
                aria-label="{{ __('Close') }}"
            >&times;</button>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-zinc-200 px-6 py-4">
                {{ $footer }}
            </div>
        @endisset
    </div>
</dialog>
