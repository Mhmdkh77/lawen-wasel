@php
    $isLivewire = $livewire ?? false;
    $pageName = $paginator->getPageName();
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $visiblePages = array_values(array_unique(array_filter([
        1,
        $currentPage - 1,
        $currentPage,
        $currentPage + 1,
        $lastPage,
        $currentPage <= 2 ? 3 : null,
        $currentPage >= $lastPage - 1 ? $lastPage - 2 : null,
    ], fn ($page) => $page !== null && $page >= 1 && $page <= $lastPage)));
    sort($visiblePages);
@endphp

<nav aria-label="Table pagination" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-ink-700">
        @if ($paginator->total() === 0)
            No results
        @else
            Showing <span class="font-semibold text-ink-900">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            of <span class="font-semibold text-ink-900">{{ number_format($paginator->total()) }}</span>
        @endif
    </p>

    <div class="flex max-w-full flex-wrap items-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-medium text-gray-400">Previous</span>
        @elseif ($isLivewire)
            <button type="button" wire:click="previousPage('{{ $pageName }}')" wire:loading.attr="disabled" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-ink-800 transition hover:border-brand-400 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 disabled:opacity-50">Previous</button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-ink-800 transition hover:border-brand-400 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">Previous</a>
        @endif

        @if ($paginator->total() > 0)
            @php $previousVisiblePage = null; @endphp
            @foreach ($visiblePages as $page)
                @if ($previousVisiblePage !== null && $page > $previousVisiblePage + 1)
                    <span aria-hidden="true" class="inline-flex h-9 min-w-7 items-center justify-center text-sm text-gray-400">&hellip;</span>
                @endif
                @if ($page === $currentPage)
                    <span aria-current="page" aria-label="Page {{ $page }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-ink-900 px-2 text-sm font-semibold text-white">{{ $page }}</span>
                @elseif ($isLivewire)
                    <button type="button" wire:key="pagination-{{ $pageName }}-{{ $page }}" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" wire:loading.attr="disabled" aria-label="Go to page {{ $page }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-2 text-sm font-medium text-ink-800 transition hover:border-brand-400 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 disabled:opacity-50">{{ $page }}</button>
                @else
                    <a href="{{ $paginator->url($page) }}" aria-label="Go to page {{ $page }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-2 text-sm font-medium text-ink-800 transition hover:border-brand-400 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ $page }}</a>
                @endif
                @php $previousVisiblePage = $page; @endphp
            @endforeach
        @endif

        @if ($paginator->hasMorePages())
            @if ($isLivewire)
                <button type="button" wire:click="nextPage('{{ $pageName }}')" wire:loading.attr="disabled" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-ink-800 transition hover:border-brand-400 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 disabled:opacity-50">Next</button>
            @else
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-ink-800 transition hover:border-brand-400 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">Next</a>
            @endif
        @else
            <span aria-disabled="true" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-medium text-gray-400">Next</span>
        @endif
    </div>
</nav>
