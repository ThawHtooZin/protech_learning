@php
    $summary = $post->reactionSummary(auth()->id());
    $emojis = ['👍', '❤️', '😂', '😮'];
@endphp

<div class="relative inline-flex shrink-0 items-center gap-1.5" x-data="{ open: false }" @keydown.escape.window="open = false">
    @foreach($summary as $row)
        <form method="POST" action="{{ route('teams.channels.posts.reactions.toggle', [$team, $channel, $post]) }}" class="inline-flex shrink-0">
            @csrf
            <input type="hidden" name="emoji" value="{{ $row['emoji'] }}">
            <button type="submit"
                class="inline-flex h-7 items-center gap-1 rounded-full border px-2 text-sm leading-none
                    {{ $row['reacted'] ? 'border-emerald-300 bg-emerald-50' : 'border-zinc-200 bg-white hover:bg-zinc-50' }}">
                <span aria-hidden="true">{{ $row['emoji'] }}</span>
                <span class="text-xs font-medium tabular-nums text-zinc-700">{{ $row['count'] }}</span>
            </button>
        </form>
    @endforeach

    <button type="button" @click="open = !open"
        class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700"
        aria-label="{{ __('Add reaction') }}"
        title="{{ __('Add reaction') }}"
        :aria-expanded="open.toString()">
        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5.5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM16 11.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
        </svg>
    </button>

    {{-- Teams-style horizontal bar: 4 emojis with clear spacing --}}
    <div x-show="open" x-cloak @click.outside="open = false"
        class="absolute right-0 top-full z-30 mt-1 flex items-center gap-1 rounded-full border border-zinc-200 bg-white px-2 py-1 shadow-lg">
        @foreach($emojis as $emoji)
            <form method="POST" action="{{ route('teams.channels.posts.reactions.toggle', [$team, $channel, $post]) }}" class="inline-flex shrink-0">
                @csrf
                <input type="hidden" name="emoji" value="{{ $emoji }}">
                <button type="submit" @click="open = false"
                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-lg leading-none hover:bg-zinc-100"
                    title="{{ $emoji }}">
                    {{ $emoji }}
                </button>
            </form>
        @endforeach
    </div>
</div>
