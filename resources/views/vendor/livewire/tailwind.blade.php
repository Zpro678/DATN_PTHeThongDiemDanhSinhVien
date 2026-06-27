@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div class="flex flex-col w-full gap-4 sm:flex-row sm:flex-1 sm:items-center sm:justify-between">
                <div class="text-center sm:text-left">
                    <p class="text-sm text-slate-500">
                        Hiển thị
                        <span class="font-bold text-slate-900">{{ $paginator->firstItem() }}</span>
                        đến
                        <span class="font-bold text-slate-900">{{ $paginator->lastItem() }}</span>
                        trong tổng số
                        <span class="font-bold text-slate-900">{{ $paginator->total() }}</span>
                        kết quả
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 w-full sm:w-auto">
                    @if (property_exists($this, 'perPage'))
                        <div class="flex items-center gap-2">
                            <span class="text-[13px] font-medium text-slate-500">Hiển thị:</span>
                            <select wire:model.live="perPage" class="cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-slate-300 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 8px auto;">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    @endif

                    <nav class="flex items-center justify-center gap-1">
                        {{-- Previous Page Link --}}
                        @if ($paginator->onFirstPage())
                            <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg text-slate-300">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        @else
                            <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-200/70 hover:text-slate-800 focus:outline-none" aria-label="{{ __('pagination.previous') }}">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif

                        {{-- Pagination Elements --}}
                        @php
                            $lastPage = $paginator->lastPage();
                            $currentPage = $paginator->currentPage();
                            
                            $start = max(1, $currentPage - 1);
                            $end = min($lastPage, $currentPage + 1);
                            
                            if ($end - $start < 2 && $lastPage >= 3) {
                                if ($start == 1) {
                                    $end = 3;
                                } elseif ($end == $lastPage) {
                                    $start = $lastPage - 2;
                                }
                            }
                        @endphp

                        @for ($page = $start; $page <= $end; $page++)
                            <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                @if ($page == $currentPage)
                                    <span aria-current="page" class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg bg-primary px-3 text-[14px] font-bold text-white shadow-sm shadow-primary/30">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg px-3 text-[14px] font-bold text-slate-600 transition-colors hover:bg-slate-200/70 hover:text-slate-900 focus:outline-none" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </button>
                                @endif
                            </span>
                        @endfor

                        {{-- Next Page Link --}}
                        @if ($paginator->hasMorePages())
                            <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-200/70 hover:text-slate-800 focus:outline-none" aria-label="{{ __('pagination.next') }}">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @else
                            <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg text-slate-300">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        @endif
                    </nav>
                </div>
            </div>
        </nav>
    @endif
</div>
