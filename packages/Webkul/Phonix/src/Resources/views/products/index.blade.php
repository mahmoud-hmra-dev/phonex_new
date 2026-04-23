@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $products */
    /** @var array $filters */
    /** @var \Illuminate\Support\Collection $categoryTree */
    /** @var array $brands */
    /** @var array<int> $wishlistIds */

    $currentSort = $filters['sort'] ?? 'created_at-desc';
    $perPage     = $filters['limit'] ?? 12;

    $sortOptions = [
        'created_at-desc' => __('phonix::app.listing.sort.newest'),
        'price-asc'       => __('phonix::app.listing.sort.price_low'),
        'price-desc'      => __('phonix::app.listing.sort.price_high'),
        'name-asc'        => __('phonix::app.listing.sort.name_asc'),
        'name-desc'       => __('phonix::app.listing.sort.name_desc'),
    ];

    $perPageOptions = [12, 24, 48];

    // Active filter chips
    $activeChips = [];
    if (! empty($filters['query'])) {
        $activeChips[] = ['key' => 'query', 'label' => '"' . $filters['query'] . '"'];
    }
    foreach ($filters['category_ids'] ?? [] as $cid) {
        $cat = $categoryTree->firstWhere('id', $cid);
        if ($cat) {
            $activeChips[] = ['key' => 'category_ids[]', 'value' => $cid, 'label' => $cat->name];
        }
    }
    foreach ($filters['brand_ids'] ?? [] as $bid) {
        $br = collect($brands)->firstWhere('id', $bid);
        if ($br) {
            $activeChips[] = ['key' => 'brand_ids[]', 'value' => $bid, 'label' => $br['label']];
        }
    }
    if ($filters['price_min'] !== null || $filters['price_max'] !== null) {
        $activeChips[] = [
            'key'   => 'price',
            'label' => core()->currency((float) ($filters['price_min'] ?? $priceAbsoluteMin)) . ' – ' . core()->currency((float) ($filters['price_max'] ?? $priceAbsoluteMax)),
        ];
    }
    if ($filters['rating'] ?? 0) {
        $activeChips[] = ['key' => 'rating', 'label' => $filters['rating'] . '★ ' . __('phonix::app.listing.filters.rating_and_up')];
    }
    if ($filters['in_stock']) {
        $activeChips[] = ['key' => 'in_stock', 'label' => __('phonix::app.product.in_stock')];
    }
    if ($filters['on_sale']) {
        $activeChips[] = ['key' => 'on_sale', 'label' => __('phonix::app.product.sale')];
    }

    // Title
    $pageTitle = ($currentCategory ?? null) ? $currentCategory->name : __('phonix::app.listing.title');
@endphp

<x-phonix::layouts.index :title="$pageTitle">

<div
    x-data="phonixFilters({
        priceMin: {{ $filters['price_min'] ?? $priceAbsoluteMin }},
        priceMax: {{ $filters['price_max'] ?? $priceAbsoluteMax }},
        absoluteMin: {{ $priceAbsoluteMin }},
        absoluteMax: {{ $priceAbsoluteMax }},
        wishlistItems: @json($wishlistIds),
        csrfToken: document.querySelector('meta[name=csrf-token]')?.content ?? '',
    })"
    class="bg-slate-50 dark:bg-dark-bg min-h-screen"
>

{{-- ============================================================
     HERO / PAGE HEADER
============================================================ --}}
<section class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-phoenix-950 to-slate-950">
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute -top-[100px] -end-[100px] w-[380px] h-[380px] bg-phoenix-500/15 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-[80px] -start-[80px] w-[280px] h-[280px] bg-plasma-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="container relative z-10 py-[36px] md:py-[56px]">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-[6px] text-xs text-white/50 mb-[18px]" aria-label="Breadcrumb">
            <a href="{{ route('phonix.home') }}" class="hover:text-white transition-colors">@lang('phonix::app.general.home')</a>
            <svg class="w-[12px] h-[12px] rtl:rotate-180 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            @if (! empty($currentCategory))
                <a href="{{ route('phonix.products.index') }}" class="hover:text-white transition-colors">@lang('phonix::app.general.shop')</a>
                <svg class="w-[12px] h-[12px] rtl:rotate-180 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                <span class="text-white/80">{{ $currentCategory->name }}</span>
            @else
                <span class="text-white/80">@lang('phonix::app.general.shop')</span>
            @endif
        </nav>

        <div class="flex flex-col lg:flex-row items-start lg:items-end justify-between gap-[20px]">
            <div>
                <h1 class="font-display text-fluid-3xl font-bold text-white mb-[8px] leading-tight tracking-tight">
                    {{ $pageTitle }}
                </h1>
                <p class="text-white/50 text-sm max-w-[520px]">@lang('phonix::app.listing.subtitle')</p>
            </div>
            <div class="flex items-center gap-[24px] shrink-0">
                <div>
                    <div class="font-display text-2xl md:text-3xl font-bold text-phoenix-300">{{ $products->total() }}</div>
                    <div class="text-[11px] text-white/40 uppercase tracking-widest mt-[2px]">@lang('phonix::app.listing.products_count')</div>
                </div>
                @if (count($activeChips))
                    <div>
                        <div class="font-display text-2xl md:text-3xl font-bold text-plasma-400">{{ count($activeChips) }}</div>
                        <div class="text-[11px] text-white/40 uppercase tracking-widest mt-[2px]">@lang('phonix::app.listing.filters.active')</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     ACTIVE FILTERS CHIP BAR
============================================================ --}}
@if (count($activeChips))
    <div class="bg-white dark:bg-dark-card border-b border-slate-100 dark:border-dark-border">
        <div class="container py-[14px] flex flex-wrap items-center gap-[8px]">
            <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                @lang('phonix::app.listing.filters.active')
            </span>
            @foreach ($activeChips as $chip)
                <button type="button"
                        data-chip-key="{{ $chip['key'] }}"
                        @isset($chip['value'])data-chip-value="{{ $chip['value'] }}"@endisset
                        @click="removeChip($event)"
                        class="inline-flex items-center gap-[6px] px-[12px] py-[6px] rounded-full text-xs font-semibold bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-700 dark:text-phoenix-300 border border-phoenix-200 dark:border-phoenix-700/50 hover:bg-phoenix-100 dark:hover:bg-phoenix-900/50 transition-colors">
                    {{ $chip['label'] }}
                    <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endforeach
            <a href="{{ route('phonix.products.index') }}"
               class="ms-[4px] text-xs font-semibold text-plasma-500 hover:text-plasma-600 transition-colors">
                @lang('phonix::app.listing.filters.clear_all')
            </a>
        </div>
    </div>
@endif

{{-- ============================================================
     MAIN: SIDEBAR (form) + PRODUCTS
============================================================ --}}
<div class="container py-[24px] md:py-[32px]">
    <form
        id="phonix-filter-form"
        method="GET"
        action="{{ route('phonix.products.index') }}"
        data-turbo-action="replace"
        class="flex gap-[24px] items-start"
    >
        {{-- Mobile filter backdrop --}}
        <div x-show="mobileOpen" x-cloak @click="mobileOpen = false"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

        {{-- ─────────────────────────────────────────────────────────
             SIDEBAR
        ───────────────────────────────────────────────────────── --}}
        <aside
            :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0 rtl:translate-x-full rtl:lg:-translate-x-0'"
            class="fixed top-0 start-0 z-[61] h-full w-[320px] max-w-[90vw] overflow-y-auto scrollbar-thin bg-white dark:bg-dark-card transition-transform duration-300 ease-out lg:static lg:z-auto lg:h-auto lg:w-[280px] lg:shrink-0 lg:rounded-2xl lg:border lg:border-slate-100 lg:dark:border-dark-border lg:shadow-sm lg:sticky lg:top-[80px] lg:self-start"
        >
            {{-- Mobile drawer header --}}
            <div class="flex items-center justify-between p-[20px] border-b border-slate-100 dark:border-dark-border lg:hidden">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">@lang('phonix::app.listing.filters.title')</h2>
                <button type="button" @click="mobileOpen = false" class="p-[6px] rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-dark-surface transition-colors" aria-label="@lang('phonix::app.general.close')">
                    <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-[20px] lg:p-[18px] space-y-[18px]">

                {{-- Desktop header --}}
                <div class="hidden lg:flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-[8px]">
                        <svg class="w-[16px] h-[16px] text-phoenix-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
                        @lang('phonix::app.listing.filters.title')
                    </h3>
                    @if (count($activeChips))
                        <a href="{{ route('phonix.products.index') }}"
                           class="text-xs font-semibold text-plasma-500 hover:text-plasma-600 transition-colors">
                            @lang('phonix::app.listing.filters.clear_all')
                        </a>
                    @endif
                </div>

                {{-- Preserve search query when filtering --}}
                @if ($filters['query'])
                    <input type="hidden" name="query" value="{{ $filters['query'] }}"/>
                @endif

                {{-- ── CATEGORY ───────────────────────────── --}}
                @if ($categoryTree->count())
                    <details open class="group border-b border-slate-100 dark:border-dark-border pb-[14px]">
                        <summary class="flex items-center justify-between cursor-pointer list-none font-semibold text-sm text-slate-900 dark:text-white select-none">
                            <span class="flex items-center gap-[8px]">
                                <span class="w-[6px] h-[6px] rounded-full bg-phoenix-500"></span>
                                @lang('phonix::app.listing.filters.category')
                            </span>
                            <svg class="w-[16px] h-[16px] text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </summary>
                        <div class="mt-[12px] space-y-[4px] max-h-[260px] overflow-y-auto scrollbar-thin pe-[4px]">
                            @foreach ($categoryTree as $cat)
                                <label class="flex items-center gap-[10px] px-[8px] py-[6px] rounded-lg cursor-pointer hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors">
                                    <input
                                        type="checkbox"
                                        name="category_ids[]"
                                        value="{{ $cat->id }}"
                                        @checked(in_array($cat->id, $filters['category_ids'] ?? []))
                                        @change="$el.form.requestSubmit()"
                                        class="h-[16px] w-[16px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 focus:ring-2 focus:ring-offset-0 cursor-pointer"
                                    />
                                    <span class="text-sm text-slate-700 dark:text-slate-300 flex-1">{{ $cat->name }}</span>
                                </label>
                                @if ($cat->children && $cat->children->count())
                                    @foreach ($cat->children as $child)
                                        <label class="flex items-center gap-[10px] ps-[28px] pe-[8px] py-[5px] rounded-lg cursor-pointer hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors">
                                            <input
                                                type="checkbox"
                                                name="category_ids[]"
                                                value="{{ $child->id }}"
                                                @checked(in_array($child->id, $filters['category_ids'] ?? []))
                                                @change="$el.form.requestSubmit()"
                                                class="h-[14px] w-[14px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 focus:ring-2 cursor-pointer"
                                            />
                                            <span class="text-xs text-slate-600 dark:text-slate-400 flex-1">{{ $child->name }}</span>
                                        </label>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </details>
                @endif

                {{-- ── BRANDS ──────────────────────────────── --}}
                @if (count($brands))
                    <details open class="group border-b border-slate-100 dark:border-dark-border pb-[14px]">
                        <summary class="flex items-center justify-between cursor-pointer list-none font-semibold text-sm text-slate-900 dark:text-white select-none">
                            <span class="flex items-center gap-[8px]">
                                <span class="w-[6px] h-[6px] rounded-full bg-plasma-500"></span>
                                @lang('phonix::app.listing.filters.brand')
                            </span>
                            <svg class="w-[16px] h-[16px] text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </summary>
                        <div class="mt-[12px] space-y-[4px] max-h-[220px] overflow-y-auto scrollbar-thin pe-[4px]">
                            @foreach ($brands as $brand)
                                <label class="flex items-center gap-[10px] px-[8px] py-[6px] rounded-lg cursor-pointer hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors">
                                    <input
                                        type="checkbox"
                                        name="brand_ids[]"
                                        value="{{ $brand['id'] }}"
                                        @checked(in_array($brand['id'], $filters['brand_ids'] ?? []))
                                        @change="$el.form.requestSubmit()"
                                        class="h-[16px] w-[16px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 focus:ring-2 cursor-pointer"
                                    />
                                    <span class="text-sm text-slate-700 dark:text-slate-300 flex-1">{{ $brand['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endif

                {{-- ── PRICE RANGE ─────────────────────────── --}}
                <details open class="group border-b border-slate-100 dark:border-dark-border pb-[14px]">
                    <summary class="flex items-center justify-between cursor-pointer list-none font-semibold text-sm text-slate-900 dark:text-white select-none">
                        <span class="flex items-center gap-[8px]">
                            <span class="w-[6px] h-[6px] rounded-full bg-gold"></span>
                            @lang('phonix::app.listing.filters.price_range')
                        </span>
                        <svg class="w-[16px] h-[16px] text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </summary>
                    <div class="mt-[14px] space-y-[12px]">
                        {{-- price bound display --}}
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-700 dark:text-phoenix-300 px-[10px] py-[4px] rounded-full" x-text="formatMoney(priceMin)"></span>
                            <span class="text-slate-300">—</span>
                            <span class="bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-700 dark:text-phoenix-300 px-[10px] py-[4px] rounded-full" x-text="formatMoney(priceMax)"></span>
                        </div>

                        {{-- dual range slider --}}
                        <div class="relative h-[6px] rounded-full bg-slate-200 dark:bg-dark-border mx-[6px]">
                            <div
                                class="absolute h-full rounded-full bg-gradient-to-r from-phoenix-500 to-phoenix-400"
                                :style="'left:' + percentMin() + '%; right:' + (100 - percentMax()) + '%'"
                            ></div>
                            <input
                                type="range"
                                :min="absoluteMin" :max="absoluteMax" step="10"
                                x-model.number="priceMin"
                                @change="priceMin = Math.min(priceMin, priceMax - 10)"
                                class="absolute w-full h-[6px] opacity-0 cursor-pointer top-0 z-[3]"
                                aria-label="Min price"
                            />
                            <input
                                type="range"
                                :min="absoluteMin" :max="absoluteMax" step="10"
                                x-model.number="priceMax"
                                @change="priceMax = Math.max(priceMax, priceMin + 10)"
                                class="absolute w-full h-[6px] opacity-0 cursor-pointer top-0 z-[4]"
                                aria-label="Max price"
                            />
                            <div class="absolute top-1/2 -translate-y-1/2 w-[16px] h-[16px] bg-white border-2 border-phoenix-500 rounded-full shadow pointer-events-none z-[5]"
                                 :style="'left: calc(' + percentMin() + '% - 8px)'"></div>
                            <div class="absolute top-1/2 -translate-y-1/2 w-[16px] h-[16px] bg-white border-2 border-phoenix-500 rounded-full shadow pointer-events-none z-[5]"
                                 :style="'left: calc(' + percentMax() + '% - 8px)'"></div>
                        </div>

                        {{-- manual inputs --}}
                        <div class="grid grid-cols-2 gap-[8px]">
                            <label class="block">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-[4px] block">
                                    @lang('phonix::app.listing.filters.price_min')
                                </span>
                                <input
                                    type="number" :min="absoluteMin" :max="absoluteMax" step="1"
                                    x-model.number="priceMin"
                                    class="w-full bg-slate-50 dark:bg-dark-surface border border-slate-200 dark:border-dark-border rounded-lg px-[10px] py-[8px] text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-phoenix-400 focus:border-transparent outline-none transition-all"
                                />
                            </label>
                            <label class="block">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-[4px] block">
                                    @lang('phonix::app.listing.filters.price_max')
                                </span>
                                <input
                                    type="number" :min="absoluteMin" :max="absoluteMax" step="1"
                                    x-model.number="priceMax"
                                    class="w-full bg-slate-50 dark:bg-dark-surface border border-slate-200 dark:border-dark-border rounded-lg px-[10px] py-[8px] text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-phoenix-400 focus:border-transparent outline-none transition-all"
                                />
                            </label>
                        </div>

                        {{-- hidden posted field --}}
                        <input type="hidden" name="price" :value="priceMin + ',' + priceMax"/>

                        <button
                            type="submit"
                            class="w-full bg-phoenix-500 hover:bg-phoenix-600 text-white text-sm font-semibold py-[10px] rounded-lg transition-colors shadow-sm shadow-phoenix-500/30"
                        >
                            @lang('phonix::app.listing.filters.apply_price')
                        </button>
                    </div>
                </details>

                {{-- ── RATING ──────────────────────────────── --}}
                <details open class="group border-b border-slate-100 dark:border-dark-border pb-[14px]">
                    <summary class="flex items-center justify-between cursor-pointer list-none font-semibold text-sm text-slate-900 dark:text-white select-none">
                        <span class="flex items-center gap-[8px]">
                            <span class="w-[6px] h-[6px] rounded-full bg-gold"></span>
                            @lang('phonix::app.listing.filters.rating')
                        </span>
                        <svg class="w-[16px] h-[16px] text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </summary>
                    <div class="mt-[12px] space-y-[2px]">
                        @foreach ([4, 3, 2, 1] as $r)
                            <label class="flex items-center gap-[10px] px-[8px] py-[6px] rounded-lg cursor-pointer hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors">
                                <input
                                    type="radio" name="rating" value="{{ $r }}"
                                    @checked(($filters['rating'] ?? 0) == $r)
                                    @change="$el.form.requestSubmit()"
                                    class="h-[16px] w-[16px] border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 focus:ring-2 cursor-pointer"
                                />
                                <span class="flex items-center gap-[1px]">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-[14px] h-[14px] {{ $i <= $r ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">@lang('phonix::app.listing.filters.rating_and_up')</span>
                            </label>
                        @endforeach
                    </div>
                </details>

                {{-- ── AVAILABILITY ────────────────────────── --}}
                <details open class="group">
                    <summary class="flex items-center justify-between cursor-pointer list-none font-semibold text-sm text-slate-900 dark:text-white select-none">
                        <span class="flex items-center gap-[8px]">
                            <span class="w-[6px] h-[6px] rounded-full bg-emerald-500"></span>
                            @lang('phonix::app.listing.filters.availability')
                        </span>
                        <svg class="w-[16px] h-[16px] text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </summary>
                    <div class="mt-[12px] space-y-[2px]">
                        <label class="flex items-center gap-[10px] px-[8px] py-[6px] rounded-lg cursor-pointer hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors">
                            <input type="checkbox" name="in_stock" value="1" @checked($filters['in_stock']) @change="$el.form.requestSubmit()"
                                   class="h-[16px] w-[16px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 focus:ring-2 cursor-pointer"/>
                            <span class="text-sm text-slate-700 dark:text-slate-300">@lang('phonix::app.product.in_stock')</span>
                        </label>
                        <label class="flex items-center gap-[10px] px-[8px] py-[6px] rounded-lg cursor-pointer hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors">
                            <input type="checkbox" name="on_sale" value="1" @checked($filters['on_sale']) @change="$el.form.requestSubmit()"
                                   class="h-[16px] w-[16px] rounded border-slate-300 dark:border-dark-border text-plasma-500 focus:ring-plasma-400 focus:ring-2 cursor-pointer"/>
                            <span class="text-sm text-slate-700 dark:text-slate-300">@lang('phonix::app.product.sale')</span>
                        </label>
                    </div>
                </details>

                {{-- Mobile actions --}}
                <div class="flex gap-[8px] lg:hidden pt-[8px]">
                    <button type="button" @click="mobileOpen = false"
                            class="flex-1 py-[11px] text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-dark-surface rounded-lg hover:bg-slate-200 dark:hover:bg-dark-border transition-colors">
                        @lang('phonix::app.general.close')
                    </button>
                    <button type="submit" class="flex-1 btn-phoenix !py-[11px] text-sm">
                        @lang('phonix::app.listing.filters.apply')
                    </button>
                </div>
            </div>
        </aside>

        {{-- ─────────────────────────────────────────────────────────
             PRODUCT AREA
        ───────────────────────────────────────────────────────── --}}
        <div class="flex-1 min-w-0">

            {{-- Toolbar --}}
            <div class="flex flex-wrap items-center justify-between gap-[12px] mb-[20px]">
                <div class="flex items-center gap-[10px]">
                    <button
                        type="button"
                        @click="mobileOpen = true"
                        class="lg:hidden inline-flex items-center gap-[6px] px-[14px] py-[9px] rounded-xl bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border text-sm font-medium text-slate-700 dark:text-slate-300 hover:border-phoenix-400 hover:text-phoenix-600 transition-all shadow-sm"
                    >
                        <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
                        @lang('phonix::app.listing.filters.title')
                        @if (count($activeChips))
                            <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-full bg-phoenix-500 text-white text-[10px] font-bold">{{ count($activeChips) }}</span>
                        @endif
                    </button>

                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        @if ($products->total() > 0)
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $products->firstItem() }}–{{ $products->lastItem() }}</span>
                            <span class="text-slate-400 dark:text-slate-500"> / {{ $products->total() }}</span>
                        @else
                            @lang('phonix::app.listing.no_results')
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-[8px]">
                    {{-- Sort --}}
                    <label class="relative">
                        <span class="sr-only">@lang('phonix::app.listing.sort.label')</span>
                        <select
                            name="sort"
                            @change="$el.form.requestSubmit()"
                            class="appearance-none bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border rounded-xl ps-[14px] pe-[36px] py-[9px] text-sm text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-phoenix-400 outline-none cursor-pointer shadow-sm min-w-[170px]"
                        >
                            @foreach ($sortOptions as $val => $label)
                                <option value="{{ $val }}" @selected($currentSort === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute end-[12px] top-1/2 -translate-y-1/2 w-[14px] h-[14px] text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </label>

                    {{-- Per page --}}
                    <label class="relative hidden sm:block">
                        <span class="sr-only">@lang('phonix::app.listing.per_page')</span>
                        <select
                            name="limit"
                            @change="$el.form.requestSubmit()"
                            class="appearance-none bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border rounded-xl ps-[14px] pe-[32px] py-[9px] text-sm text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-phoenix-400 outline-none cursor-pointer shadow-sm"
                        >
                            @foreach ($perPageOptions as $opt)
                                <option value="{{ $opt }}" @selected((int) $perPage === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute end-[12px] top-1/2 -translate-y-1/2 w-[14px] h-[14px] text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </label>

                    {{-- View toggle (client-only, not part of form) --}}
                    <div class="hidden sm:flex items-center bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border rounded-xl overflow-hidden shadow-sm">
                        <button type="button" @click="setView('grid')" :class="view === 'grid' ? 'bg-phoenix-500 text-white' : 'text-slate-400 hover:text-slate-600'" class="p-[10px] transition-colors">
                            <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        </button>
                        <button type="button" @click="setView('list')" :class="view === 'list' ? 'bg-phoenix-500 text-white' : 'text-slate-400 hover:text-slate-600'" class="p-[10px] transition-colors border-s border-slate-200 dark:border-dark-border">
                            <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Empty state --}}
            @if ($products->isEmpty())
                <div class="bg-white dark:bg-dark-card rounded-2xl border border-slate-100 dark:border-dark-border p-[48px] md:p-[64px] text-center shadow-sm">
                    <div class="w-[80px] h-[80px] mx-auto mb-[20px] rounded-full bg-slate-50 dark:bg-dark-surface flex items-center justify-center">
                        <svg class="w-[40px] h-[40px] text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-[8px]">@lang('phonix::app.listing.empty.title')</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-[24px] max-w-[340px] mx-auto">@lang('phonix::app.listing.empty.description')</p>
                    <a href="{{ route('phonix.products.index') }}" class="btn-phoenix !px-[24px] !py-[10px] text-sm">
                        @lang('phonix::app.listing.empty.clear_filters')
                    </a>
                </div>
            @else

            {{-- GRID --}}
            <div x-show="view === 'grid'" class="grid grid-cols-2 lg:grid-cols-3 gap-[14px] md:gap-[20px]">
                @foreach ($products as $product)
                    @include('phonix::products.partials.card-grid', ['product' => $product])
                @endforeach
            </div>

            {{-- LIST --}}
            <div x-show="view === 'list'" x-cloak class="space-y-[12px]">
                @foreach ($products as $product)
                    @include('phonix::products.partials.card-list', ['product' => $product])
                @endforeach
            </div>

            {{-- PAGINATION --}}
            @if ($products->hasPages())
                <nav class="flex flex-col sm:flex-row items-center justify-between gap-[16px] mt-[40px] pt-[24px] border-t border-slate-200 dark:border-dark-border" aria-label="@lang('phonix::app.listing.pagination.label')">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        @lang('phonix::app.listing.pagination.showing_of', ['from' => $products->firstItem(), 'to' => $products->lastItem(), 'total' => $products->total()])
                    </p>
                    <div class="flex items-center gap-[4px]">
                        @php $curPage = $products->currentPage(); $lastPage = $products->lastPage(); @endphp
                        @if ($products->onFirstPage())
                            <span class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border border-slate-200 dark:border-dark-border text-slate-300 cursor-not-allowed">
                                <svg class="w-[14px] h-[14px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                            </span>
                        @else
                            <a href="{{ $products->previousPageUrl() }}" class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border border-slate-200 dark:border-dark-border text-slate-600 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:border-phoenix-300 hover:text-phoenix-600 transition-all shadow-sm">
                                <svg class="w-[14px] h-[14px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                            </a>
                        @endif

                        @for ($p = 1; $p <= $lastPage; $p++)
                            @if ($p === 1 || $p === $lastPage || abs($p - $curPage) <= 1)
                                @if ($p === $curPage)
                                    <span class="flex items-center justify-center min-w-[38px] h-[38px] px-[10px] rounded-xl text-sm font-bold bg-phoenix-500 text-white shadow shadow-phoenix-500/30">{{ $p }}</span>
                                @else
                                    <a href="{{ $products->url($p) }}" class="flex items-center justify-center min-w-[38px] h-[38px] px-[10px] rounded-xl border border-slate-200 dark:border-dark-border text-sm text-slate-600 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:border-phoenix-300 hover:text-phoenix-600 transition-all shadow-sm">{{ $p }}</a>
                                @endif
                            @elseif (($p === 2 && $curPage > 3) || ($p === $lastPage - 1 && $curPage < $lastPage - 2))
                                <span class="flex items-center justify-center w-[32px] h-[38px] text-slate-400">…</span>
                            @endif
                        @endfor

                        @if ($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}" class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border border-slate-200 dark:border-dark-border text-slate-600 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:border-phoenix-300 hover:text-phoenix-600 transition-all shadow-sm">
                                <svg class="w-[14px] h-[14px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </a>
                        @else
                            <span class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border border-slate-200 dark:border-dark-border text-slate-300 cursor-not-allowed">
                                <svg class="w-[14px] h-[14px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </span>
                        @endif
                    </div>
                </nav>
            @endif
            @endif

        </div>
    </form>
</div>

</div>{{-- end x-data --}}

@pushOnce('scripts')
<script>
function phonixFilters(init) {
    return {
        ...init,
        mobileOpen: false,
        view: localStorage.getItem('phonix_view') || 'grid',
        cartLoading: null,
        wishlistLoading: null,

        setView(v) { this.view = v; localStorage.setItem('phonix_view', v); },

        removeChip(evt) {
            const key = evt.currentTarget.dataset.chipKey;
            const value = evt.currentTarget.dataset.chipValue;
            const form = document.getElementById('phonix-filter-form');
            if (!form) return;

            // Special keys that map to hidden fields driven by Alpine state
            if (key === 'price') {
                this.priceMin = this.absoluteMin;
                this.priceMax = this.absoluteMax;
                // let Alpine update the hidden field value before submit
                this.$nextTick(() => form.requestSubmit());
                return;
            }

            form.querySelectorAll(`[name="${key}"]`).forEach(el => {
                if (value !== undefined) {
                    if (String(el.value) === String(value)) el.checked = false;
                } else {
                    if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
                    else el.value = '';
                }
            });
            form.requestSubmit();
        },

        percentMin() {
            const r = this.absoluteMax - this.absoluteMin;
            if (r <= 0) return 0;
            return Math.min(100, Math.max(0, ((this.priceMin - this.absoluteMin) / r) * 100));
        },
        percentMax() {
            const r = this.absoluteMax - this.absoluteMin;
            if (r <= 0) return 100;
            return Math.min(100, Math.max(0, ((this.priceMax - this.absoluteMin) / r) * 100));
        },
        formatMoney(n) {
            try { return '$' + Number(n).toLocaleString(); } catch(e) { return '$' + n; }
        },

        async addToCart(productId, productUrl) {
            if (this.cartLoading === productId) return;
            this.cartLoading = productId;
            try {
                const res = await fetch('{{ route("phonix.cart.add") }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ product_id: productId, quantity: 1 }),
                });
                const data = await res.json().catch(() => ({}));
                if (data.redirect && !data.success) {
                    window.Turbo ? window.Turbo.visit(data.redirect) : (window.location.href = data.redirect);
                    return;
                }
                if (res.ok && data.success) {
                    window.phonix?.updateCartBadge(data.items_qty ?? 0);
                    window.phonix?.toast(data.message || @json(__('phonix::app.messages.success.added_to_cart')), 'success');
                } else {
                    window.phonix?.toast(data.error || data.message || @json(__('phonix::app.messages.error.general')), 'error');
                }
            } catch (e) {
                window.phonix?.toast(@json(__('phonix::app.messages.error.general')), 'error');
            } finally {
                this.cartLoading = null;
            }
        },

        async toggleWishlist(productId) {
            if (this.wishlistLoading === productId) return;
            this.wishlistLoading = productId;
            try {
                const res = await fetch('{{ route("phonix.wishlist.toggle") }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ product_id: productId }),
                });
                if (res.status === 401) {
                    const d = await res.json();
                    if (d.redirect) window.location.href = d.redirect;
                    return;
                }
                const d = await res.json();
                if (d.success) {
                    d.in_wishlist
                        ? this.wishlistItems.push(productId)
                        : (this.wishlistItems = this.wishlistItems.filter(id => id !== productId));
                }
            } catch(e) {}
            finally { this.wishlistLoading = null; }
        },
    };
}
</script>
@endPushOnce

</x-phonix::layouts.index>
