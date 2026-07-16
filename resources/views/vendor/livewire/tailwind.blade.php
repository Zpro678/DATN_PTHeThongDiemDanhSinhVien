@php
if (! isset($scrollTo)) {
    $scrollTo = false;
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
                            <div x-data="{ open: false }" class="relative">
                                <button type="button" @click="open = !open" @click.away="open = false" 
                                    class="inline-flex cursor-pointer items-center justify-between gap-2.5 rounded-xl border border-slate-200/80 bg-white py-1.5 pl-3.5 pr-2 text-[13px] font-bold text-slate-700 shadow-sm transition-all hover:border-slate-300 hover:bg-slate-50 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/15 min-w-[72px]">
                                    <span>{{ $this->perPage ?? 20 }}</span>
                                    <div class="flex h-5 w-5 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition-colors group-hover:bg-slate-200">
                                        <svg class="h-3 w-3 transition-transform duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>

                                <div x-show="open" x-cloak 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                     class="absolute bottom-full right-0 z-50 mb-2 w-max min-w-[90px] rounded-2xl border border-slate-200/60 bg-white/95 p-1.5 shadow-[0_12px_40px_-12px_rgba(0,0,0,0.15)] backdrop-blur-xl">
                                    @foreach([10, 20, 50, 100] as $value)
                                        <button type="button" 
                                            wire:click="$set('perPage', {{ $value }})"
                                            @click="open = false"
                                            class="group flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-[13px] transition-all {{ ($this->perPage ?? 20) == $value ? 'bg-blue-600 font-bold text-white shadow-md shadow-blue-600/20' : 'font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                            <span>{{ $value }}</span>
                                            @if (($this->perPage ?? 20) == $value)
                                                <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
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
