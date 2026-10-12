@php
    $label = $user->profile?->display_name ?? $user->email;
    $initial = strtoupper(mb_substr($label, 0, 1));
    $size = $size ?? 'md';
    $classes = $size === 'sm'
        ? 'h-8 w-8 text-xs'
        : 'h-10 w-10 text-sm';
    $palette = ['bg-emerald-600', 'bg-sky-600', 'bg-amber-600', 'bg-violet-600', 'bg-rose-600', 'bg-teal-600'];
    $color = $palette[($user->id - 1) % count($palette)];
@endphp
<span class="{{ $color }} {{ $classes }} inline-flex shrink-0 items-center justify-center rounded-full font-bold text-white" title="{{ $label }}" aria-hidden="true">
    {{ $initial }}
</span>
