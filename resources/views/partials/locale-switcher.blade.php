{{-- Compact language switcher — EN | မြန် --}}
@php
    $current = app()->getLocale();
@endphp
<div class="inline-flex items-center overflow-hidden rounded-md border border-zinc-700 text-[11px] font-semibold leading-none" role="group" aria-label="{{ __('Language') }}">
    <a href="{{ route('locale.switch', 'en') }}"
        class="px-2 py-1.5 transition {{ $current === 'en' ? 'bg-zinc-700 text-white' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}"
        hreflang="en" lang="en">EN</a>
    <a href="{{ route('locale.switch', 'my') }}"
        class="px-2 py-1.5 transition {{ $current === 'my' ? 'bg-zinc-700 text-white' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}"
        hreflang="my" lang="my">မြန်</a>
</div>
