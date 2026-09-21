@php
    // Shared page-number bar for Livewire lists (see App\Livewire\Concerns\PaginatesList).
    $pageName = $paginator->getPageName();
@endphp
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-gray-600">
            Showing <span class="font-semibold">{{ $paginator->firstItem() }}</span>
            to <span class="font-semibold">{{ $paginator->lastItem() }}</span>
            of <span class="font-semibold">{{ $paginator->total() }}</span> results
        </p>

        <div class="flex flex-wrap items-center justify-center gap-1">
            <button type="button" wire:click="previousPage('{{ $pageName }}')" @disabled($paginator->onFirstPage())
                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-40">
                &laquo; Prev
            </button>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-sm text-gray-400">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white">{{ $page }}</span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-indigo-50">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            <button type="button" wire:click="nextPage('{{ $pageName }}')" @disabled(! $paginator->hasMorePages())
                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-40">
                Next &raquo;
            </button>
        </div>
    </nav>
@endif
