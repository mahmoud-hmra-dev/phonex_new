@props([
    'currentPage' => 1,
    'totalPages' => 7,
    'from' => 1,
    'to' => 24,
    'total' => 156,
])

<nav
    {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row items-center justify-between gap-[16px] pt-[32px]']) }}
    aria-label="Pagination"
>
    {{-- Results info --}}
    <p class="text-sm text-slate-500 dark:text-slate-400">
        @lang('phonix::app.listing.pagination.showing_of', ['from' => $from, 'to' => $to, 'total' => $total])
    </p>

    {{-- Page buttons --}}
    <div class="flex items-center gap-[4px]">
        {{-- Previous --}}
        <button
            class="flex items-center justify-center w-[36px] h-[36px] rounded border border-slate-200 dark:border-dark-border text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-card transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
            @if ($currentPage <= 1) disabled @endif
            aria-label="@lang('phonix::app.listing.pagination.previous')"
        >
            <svg class="w-[16px] h-[16px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>

        {{-- Page Numbers --}}
        @for ($page = 1; $page <= $totalPages; $page++)
            @if ($page === 1 || $page === $totalPages || abs($page - $currentPage) <= 1)
                <button
                    class="flex items-center justify-center min-w-[36px] h-[36px] px-[8px] rounded text-sm font-medium transition-colors
                        {{ $page === $currentPage
                            ? 'bg-phoenix-500 text-white shadow-sm'
                            : 'border border-slate-200 dark:border-dark-border text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-card'
                        }}"
                    aria-label="@lang('phonix::app.listing.pagination.page', ['page' => $page])"
                    @if ($page === $currentPage) aria-current="page" @endif
                >
                    {{ $page }}
                </button>
            @elseif ($page === 2 && $currentPage > 3 || $page === $totalPages - 1 && $currentPage < $totalPages - 2)
                <span class="flex items-center justify-center w-[36px] h-[36px] text-sm text-slate-400" aria-hidden="true">
                    ...
                </span>
            @endif
        @endfor

        {{-- Next --}}
        <button
            class="flex items-center justify-center w-[36px] h-[36px] rounded border border-slate-200 dark:border-dark-border text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-card transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
            @if ($currentPage >= $totalPages) disabled @endif
            aria-label="@lang('phonix::app.listing.pagination.next')"
        >
            <svg class="w-[16px] h-[16px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>
</nav>
