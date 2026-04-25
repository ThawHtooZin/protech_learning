@if ($paginator->hasPages())
    <nav role="navigation" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" aria-label="{{ __('Pagination Navigation') }}">
        <p class="text-sm text-zinc-500">
            @if ($paginator->firstItem())
                {{ __('Showing :from–:to of :total', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
            @else
                {{ __(':total results', ['total' => $paginator->total()]) }}
            @endif
        </p>
        <div class="flex flex-wrap items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="rounded-md border border-zinc-800 px-3 py-1.5 text-sm text-zinc-600">{{ __('Previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-md border border-zinc-700 bg-zinc-900 px-3 py-1.5 text-sm text-emerald-400 hover:bg-zinc-800">{{ __('Previous') }}</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-sm text-zinc-600">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="rounded-md border border-emerald-700/50 bg-emerald-950/50 px-3 py-1.5 text-sm font-medium text-emerald-200">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="rounded-md border border-zinc-700 bg-zinc-900 px-3 py-1.5 text-sm text-zinc-300 hover:border-zinc-600 hover:text-white" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-md border border-zinc-700 bg-zinc-900 px-3 py-1.5 text-sm text-emerald-400 hover:bg-zinc-800">{{ __('Next') }}</a>
            @else
                <span class="rounded-md border border-zinc-800 px-3 py-1.5 text-sm text-zinc-600">{{ __('Next') }}</span>
            @endif
        </div>
    </nav>
@endif
