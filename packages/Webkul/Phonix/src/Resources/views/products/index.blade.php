@php
    $productRepository  = app(\Webkul\Product\Repositories\ProductRepository::class);
    $categoryRepository = app(\Webkul\Category\Repositories\CategoryRepository::class);

    // Category tree for sidebar & hero pills
    $allCategories = $categoryRepository->getVisibleCategoryTree(
        core()->getCurrentChannel()->root_category_id
    );

    // Current filter state from URL
    $currentSort      = request('sort', 'created_at-desc');
    $currentLimit     = (int) request('limit', 12);
    $priceRange       = request('price', '');
    $selectedCategory = request('category_id', '');
    $searchQuery      = request('query', '');

    // Parse price range "min,max"
    $priceMin = 0;
    $priceMax = 10000;
    if ($priceRange && str_contains($priceRange, ',')) {
        [$parsedMin, $parsedMax] = explode(',', $priceRange, 2);
        $priceMin = max(0, (int) $parsedMin);
        $priceMax = max($priceMin + 1, (int) $parsedMax);
    }

    // Build search params for Bagisto's getAll()
    $searchParams = array_filter([
        'status'               => 1,
        'visible_individually' => 1,
        'category_id'          => $selectedCategory ?: null,
        'price'                => $priceRange ?: null,
        'sort'                 => $currentSort,
        'limit'                => $currentLimit,
        'page'                 => request('page', 1),
        'query'                => $searchQuery ?: null,
    ], fn($v) => $v !== null && $v !== '');

    $products       = $productRepository->getAll($searchParams);
    $paginatorFrom  = $products->firstItem() ?? 0;
    $paginatorTo    = $products->lastItem()  ?? 0;
    $paginatorTotal = $products->total();

    // Active filter chips
    $activeFilters = [];
    if ($selectedCategory) {
        $activeCat = $categoryRepository->find($selectedCategory);
        if ($activeCat) $activeFilters[] = ['key' => 'category_id', 'label' => $activeCat->name];
    }
    if ($priceRange) {
        $activeFilters[] = ['key' => 'price', 'label' => core()->currency($priceMin) . ' – ' . core()->currency($priceMax)];
    }
    if ($searchQuery) {
        $activeFilters[] = ['key' => 'query', 'label' => '"' . $searchQuery . '"'];
    }

    $sortOptions = [
        'created_at-desc' => __('phonix::app.listing.sort.newest'),
        'price-asc'       => __('phonix::app.listing.sort.price_low'),
        'price-desc'      => __('phonix::app.listing.sort.price_high'),
        'name-asc'        => __('phonix::app.listing.sort.name_asc'),
        'name-desc'       => __('phonix::app.listing.sort.name_desc'),
    ];

    $selectedCategoryName = $selectedCategory
        ? (optional($categoryRepository->find($selectedCategory))->name ?? '')
        : '';
@endphp

@pushOnce('styles')
<style>[x-cloak]{display:none!important}</style>
@endPushOnce

<x-phonix::layouts.index :title="$selectedCategoryName ?: __('phonix::app.listing.title')">

<div
    x-data="{
        viewMode: localStorage.getItem('phonix_view') || 'grid',
        filtersOpen: false,
        cartLoading: null,
        wishlistLoading: null,
        wishlistItems: [],
        priceMin: {{ $priceMin }},
        priceMax: {{ $priceMax }},
        csrfToken: document.querySelector('meta[name=csrf-token]')?.content ?? '',

        setView(m) {
            this.viewMode = m;
            localStorage.setItem('phonix_view', m);
        },

        buildUrl(params) {
            const url = new URL(window.location.href);
            Object.entries(params).forEach(([k, v]) => {
                if (v === null || v === '') url.searchParams.delete(k);
                else url.searchParams.set(k, v);
            });
            url.searchParams.delete('page');
            return url.toString();
        },

        applyPrice() {
            window.location.href = this.buildUrl({ price: this.priceMin + ',' + this.priceMax });
        },

        setSort(val) {
            window.location.href = this.buildUrl({ sort: val });
        },

        setLimit(val) {
            window.location.href = this.buildUrl({ limit: val });
        },

        setCategory(id) {
            window.location.href = this.buildUrl({ category_id: id || null });
        },

        removeFilter(key) {
            window.location.href = this.buildUrl({ [key]: null });
        },

        addToCart(productId, productUrl) {
            if (this.cartLoading === productId) return;
            this.cartLoading = productId;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('phonix.cart.add') }}';
            form.style.display = 'none';
            const token = document.createElement('input');
            token.type = 'hidden'; token.name = '_token'; token.value = this.csrfToken;
            const pid = document.createElement('input');
            pid.type = 'hidden'; pid.name = 'product_id'; pid.value = productId;
            const qty = document.createElement('input');
            qty.type = 'hidden'; qty.name = 'quantity'; qty.value = 1;
            form.appendChild(token); form.appendChild(pid); form.appendChild(qty);
            document.body.appendChild(form);
            form.submit();
        },

        async toggleWishlist(productId) {
            if (this.wishlistLoading === productId) return;
            this.wishlistLoading = productId;
            try {
                const res = await fetch('{{ route('phonix.wishlist.toggle') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ product_id: productId })
                });
                const data = await res.json();
                if (res.status === 401 && data.redirect) { window.location.href = data.redirect; return; }
                if (data.success) {
                    data.in_wishlist
                        ? this.wishlistItems.push(productId)
                        : (this.wishlistItems = this.wishlistItems.filter(id => id !== productId));
                }
            } catch(e) {}
            finally { this.wishlistLoading = null; }
        }
    }"
>

{{-- ================================================================
     HERO BANNER
================================================================ --}}
<div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-phoenix-950 to-slate-900">
    {{-- decorative orbs --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-[120px] -end-[120px] w-[400px] h-[400px] bg-phoenix-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-[80px] -start-[80px] w-[300px] h-[300px] bg-phoenix-400/10 rounded-full blur-3xl"></div>
        <div class="absolute inset-0" style="background-image:radial-gradient(ellipse at 60% 40%, rgba(var(--phoenix-500-rgb,0,186,209),.06) 0%, transparent 65%)"></div>
    </div>

    <div class="container relative z-10 py-[40px] md:py-[56px]">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-[6px] text-xs text-white/50 mb-[20px]" aria-label="Breadcrumb">
            <a href="{{ route('phonix.home') }}" class="hover:text-white/80 transition-colors">@lang('phonix::app.general.home')</a>
            <svg class="w-[12px] h-[12px] rtl:rotate-180 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            @if ($selectedCategoryName)
                <a href="{{ route('phonix.products.index') }}" class="hover:text-white/80 transition-colors">@lang('phonix::app.general.shop')</a>
                <svg class="w-[12px] h-[12px] rtl:rotate-180 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                <span class="text-white/80">{{ $selectedCategoryName }}</span>
            @else
                <span class="text-white/80">@lang('phonix::app.general.shop')</span>
            @endif
        </nav>

        <div class="flex flex-col lg:flex-row items-start lg:items-end justify-between gap-[24px]">
            <div>
                <h1 class="text-fluid-3xl font-bold text-white mb-[8px] leading-tight">
                    {{ $selectedCategoryName ?: __('phonix::app.listing.title') }}
                </h1>
                <p class="text-white/50 text-sm max-w-[520px]">@lang('phonix::app.listing.subtitle')</p>
            </div>
            <div class="flex items-center gap-[32px] shrink-0">
                <div class="text-center">
                    <div class="text-3xl font-bold text-phoenix-400">{{ $paginatorTotal }}</div>
                    <div class="text-[11px] text-white/40 uppercase tracking-widest mt-[2px]">@lang('phonix::app.listing.products_count')</div>
                </div>
                @if (count($activeFilters))
                    <div class="text-center">
                        <div class="text-3xl font-bold text-coral">{{ count($activeFilters) }}</div>
                        <div class="text-[11px] text-white/40 uppercase tracking-widest mt-[2px]">@lang('phonix::app.listing.filters.active')</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Category pills --}}
        @if ($allCategories->count() > 0)
            <div class="flex items-center gap-[8px] mt-[28px] overflow-x-auto scrollbar-none pb-[4px]">
                <button
                    @click="setCategory('')"
                    class="shrink-0 inline-flex items-center px-[16px] py-[8px] rounded-full text-xs font-semibold transition-all duration-200 {{ !$selectedCategory ? 'bg-white text-phoenix-800 shadow-sm shadow-phoenix-500/30' : 'bg-white/10 text-white/80 hover:bg-white/20 border border-white/10' }}"
                >
                    @lang('phonix::app.listing.filters.all_categories')
                </button>
                @foreach ($allCategories as $cat)
                    <button
                        @click="setCategory('{{ $cat->id }}')"
                        class="shrink-0 inline-flex items-center px-[16px] py-[8px] rounded-full text-xs font-semibold transition-all duration-200 {{ (string)$selectedCategory === (string)$cat->id ? 'bg-white text-phoenix-800 shadow-sm shadow-phoenix-500/30' : 'bg-white/10 text-white/80 hover:bg-white/20 border border-white/10' }}"
                    >
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ================================================================
     ACTIVE FILTER CHIPS
================================================================ --}}
@if (count($activeFilters))
    <div class="bg-white dark:bg-dark-bg border-b border-slate-100 dark:border-dark-border">
        <div class="container py-[12px] flex flex-wrap items-center gap-[8px]">
            <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">@lang('phonix::app.listing.filters.active'):</span>
            @foreach ($activeFilters as $filter)
                <button
                    @click="removeFilter('{{ $filter['key'] }}')"
                    class="inline-flex items-center gap-[6px] px-[10px] py-[5px] rounded-full text-xs font-medium bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-700 dark:text-phoenix-300 border border-phoenix-200 dark:border-phoenix-700 hover:bg-phoenix-100 dark:hover:bg-phoenix-900/50 transition-colors"
                >
                    {{ $filter['label'] }}
                    <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endforeach
            <a href="{{ route('phonix.products.index') }}" class="text-xs text-red-400 hover:text-red-600 font-medium transition-colors ms-[4px]">
                @lang('phonix::app.listing.filters.clear_all')
            </a>
        </div>
    </div>
@endif

{{-- ================================================================
     MAIN: SIDEBAR + PRODUCTS
================================================================ --}}
<div class="bg-slate-50 dark:bg-dark-bg min-h-screen">
    <div class="container py-[32px]">
        <div class="flex gap-[24px] items-start">

            {{-- ============================================================
                 MOBILE: filter overlay backdrop
            ============================================================ --}}
            <div
                x-show="filtersOpen"
                @click="filtersOpen = false"
                class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm lg:hidden"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-cloak
            ></div>

            {{-- ============================================================
                 SIDEBAR
            ============================================================ --}}
            <aside
                :class="filtersOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed top-0 start-0 z-[61] h-full w-[300px] overflow-y-auto
                       bg-white dark:bg-dark-surface
                       transition-transform duration-300 ease-out
                       lg:static lg:z-auto lg:h-auto lg:w-[260px] lg:shrink-0
                       lg:bg-transparent lg:dark:bg-transparent
                       lg:sticky lg:top-[80px] lg:self-start
                       scrollbar-thin"
            >
                {{-- Mobile header --}}
                <div class="flex items-center justify-between p-[20px] border-b border-slate-100 dark:border-dark-border lg:hidden">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white">@lang('phonix::app.listing.filters.title')</h3>
                    <button @click="filtersOpen = false" class="p-[6px] rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-dark-card transition-colors">
                        <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-[20px] lg:p-0 space-y-[12px]">

                    {{-- Desktop filter header --}}
                    <div class="hidden lg:flex items-center justify-between mb-[4px]">
                        <div class="flex items-center gap-[8px]">
                            <svg class="w-[16px] h-[16px] text-phoenix-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                            </svg>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">@lang('phonix::app.listing.filters.title')</h3>
                        </div>
                        <a href="{{ route('phonix.products.index') }}" class="text-xs text-phoenix-600 dark:text-phoenix-400 hover:underline">@lang('phonix::app.listing.filters.clear_all')</a>
                    </div>

                    {{-- ── 1. CATEGORIES ────────────────── --}}
                    <div x-data="{ open: true }" class="bg-white dark:bg-dark-card rounded-xl border border-slate-100 dark:border-dark-border overflow-hidden shadow-sm">
                        <button
                            type="button"
                            @click="open = !open"
                            class="flex items-center justify-between w-full px-[16px] py-[14px] text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-dark-surface/50 transition-colors"
                        >
                            <span class="flex items-center gap-[8px]">
                                <span class="w-[6px] h-[6px] rounded-full bg-phoenix-500 shrink-0"></span>
                                @lang('phonix::app.listing.filters.category')
                            </span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-[15px] h-[15px] text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>

                        <div x-show="open" x-collapse class="border-t border-slate-100 dark:border-dark-border">
                            <div class="p-[12px] space-y-[2px] max-h-[240px] overflow-y-auto scrollbar-thin">
                                <button
                                    type="button"
                                    @click="setCategory('')"
                                    class="flex items-center justify-between w-full px-[10px] py-[8px] rounded-lg text-sm transition-colors {{ !$selectedCategory ? 'bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-700 dark:text-phoenix-300 font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-surface' }}"
                                >
                                    <span>@lang('phonix::app.listing.filters.all_categories')</span>
                                    @if (!$selectedCategory)
                                        <svg class="w-[14px] h-[14px] text-phoenix-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    @endif
                                </button>
                                @foreach ($allCategories as $category)
                                    <button
                                        type="button"
                                        @click="setCategory('{{ $category->id }}')"
                                        class="flex items-center justify-between w-full px-[10px] py-[8px] rounded-lg text-sm transition-colors {{ (string)$selectedCategory === (string)$category->id ? 'bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-700 dark:text-phoenix-300 font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-surface' }}"
                                    >
                                        <span>{{ $category->name }}</span>
                                        @if ((string)$selectedCategory === (string)$category->id)
                                            <svg class="w-[14px] h-[14px] text-phoenix-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        @endif
                                    </button>
                                    @if ($category->children && $category->children->count() > 0)
                                        @foreach ($category->children as $child)
                                            <button
                                                type="button"
                                                @click="setCategory('{{ $child->id }}')"
                                                class="flex items-center justify-between w-full ps-[28px] pe-[10px] py-[7px] rounded-lg text-xs transition-colors {{ (string)$selectedCategory === (string)$child->id ? 'bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-600 dark:text-phoenix-400 font-medium' : 'text-slate-500 dark:text-slate-500 hover:bg-slate-50 dark:hover:bg-dark-surface' }}"
                                            >
                                                <span class="flex items-center gap-[6px]">
                                                    <span class="w-[4px] h-[4px] rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                                                    {{ $child->name }}
                                                </span>
                                            </button>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ── 2. PRICE RANGE ───────────────── --}}
                    <div x-data="{ open: true }" class="bg-white dark:bg-dark-card rounded-xl border border-slate-100 dark:border-dark-border overflow-hidden shadow-sm">
                        <button
                            type="button"
                            @click="open = !open"
                            class="flex items-center justify-between w-full px-[16px] py-[14px] text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-dark-surface/50 transition-colors"
                        >
                            <span class="flex items-center gap-[8px]">
                                <span class="w-[6px] h-[6px] rounded-full bg-gold shrink-0"></span>
                                @lang('phonix::app.listing.filters.price_range')
                            </span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-[15px] h-[15px] text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>

                        <div x-show="open" x-collapse class="border-t border-slate-100 dark:border-dark-border">
                            <div class="p-[16px] space-y-[12px]">

                                {{-- Current price display --}}
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-phoenix-600 dark:text-phoenix-400 bg-phoenix-50 dark:bg-phoenix-900/30 px-[10px] py-[4px] rounded-full" x-text="'$' + priceMin.toLocaleString()"></span>
                                    <span class="text-xs text-slate-400">–</span>
                                    <span class="text-xs font-semibold text-phoenix-600 dark:text-phoenix-400 bg-phoenix-50 dark:bg-phoenix-900/30 px-[10px] py-[4px] rounded-full" x-text="'$' + priceMax.toLocaleString()"></span>
                                </div>

                                {{-- Range track --}}
                                <div class="relative h-[6px] rounded-full bg-slate-200 dark:bg-dark-border mx-[6px]">
                                    <div
                                        class="absolute h-full rounded-full bg-gradient-to-r from-phoenix-500 to-phoenix-400"
                                        :style="'left:' + Math.min(priceMin,priceMax) / 10000 * 100 + '%;right:' + (100 - Math.max(priceMin,priceMax) / 10000 * 100) + '%'"
                                    ></div>
                                    {{-- Min slider --}}
                                    <input
                                        type="range" min="0" max="10000" step="50"
                                        x-model.number="priceMin"
                                        @change="if(priceMin >= priceMax) priceMin = priceMax - 50"
                                        class="absolute w-full h-[6px] opacity-0 cursor-pointer top-0"
                                        style="z-index: 3"
                                        aria-label="Min price"
                                    />
                                    {{-- Max slider --}}
                                    <input
                                        type="range" min="0" max="10000" step="50"
                                        x-model.number="priceMax"
                                        @change="if(priceMax <= priceMin) priceMax = priceMin + 50"
                                        class="absolute w-full h-[6px] opacity-0 cursor-pointer top-0"
                                        style="z-index: 4"
                                        aria-label="Max price"
                                    />
                                    {{-- Visual thumbs --}}
                                    <div
                                        class="absolute top-1/2 -translate-y-1/2 w-[16px] h-[16px] bg-white border-2 border-phoenix-500 rounded-full shadow-md pointer-events-none transition-all"
                                        :style="'left: calc(' + priceMin / 10000 * 100 + '% - 8px)'"
                                        style="z-index: 5"
                                    ></div>
                                    <div
                                        class="absolute top-1/2 -translate-y-1/2 w-[16px] h-[16px] bg-white border-2 border-phoenix-500 rounded-full shadow-md pointer-events-none transition-all"
                                        :style="'left: calc(' + priceMax / 10000 * 100 + '% - 8px)'"
                                        style="z-index: 5"
                                    ></div>
                                </div>

                                {{-- Manual number inputs --}}
                                <div class="grid grid-cols-2 gap-[8px]">
                                    <div>
                                        <label class="block text-[10px] font-medium text-slate-400 dark:text-slate-500 mb-[5px] uppercase tracking-wider">@lang('phonix::app.listing.filters.price_min')</label>
                                        <input
                                            type="number" min="0" max="10000" step="50"
                                            x-model.number="priceMin"
                                            @input="if(priceMin >= priceMax) priceMax = priceMin + 50"
                                            class="w-full bg-slate-50 dark:bg-dark-surface border border-slate-200 dark:border-dark-border rounded-lg px-[10px] py-[8px] text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-phoenix-400 focus:border-transparent outline-none transition-all"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-medium text-slate-400 dark:text-slate-500 mb-[5px] uppercase tracking-wider">@lang('phonix::app.listing.filters.price_max')</label>
                                        <input
                                            type="number" min="0" max="10000" step="50"
                                            x-model.number="priceMax"
                                            @input="if(priceMax <= priceMin) priceMin = priceMax - 50"
                                            class="w-full bg-slate-50 dark:bg-dark-surface border border-slate-200 dark:border-dark-border rounded-lg px-[10px] py-[8px] text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-phoenix-400 focus:border-transparent outline-none transition-all"
                                        />
                                    </div>
                                </div>

                                {{-- Apply button --}}
                                <button
                                    type="button"
                                    @click="applyPrice()"
                                    class="w-full bg-phoenix-500 hover:bg-phoenix-600 text-white text-sm font-semibold py-[10px] rounded-lg transition-colors shadow-sm shadow-phoenix-500/30"
                                >
                                    @lang('phonix::app.listing.filters.apply_price')
                                </button>

                                @if ($priceRange)
                                    <button
                                        type="button"
                                        @click="removeFilter('price')"
                                        class="w-full text-center text-xs text-slate-400 dark:text-slate-500 hover:text-coral transition-colors"
                                    >
                                        @lang('phonix::app.listing.filters.clear_rating')
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── 3. SORT (mobile only) ────────── --}}
                    <div class="lg:hidden bg-white dark:bg-dark-card rounded-xl border border-slate-100 dark:border-dark-border overflow-hidden shadow-sm">
                        <div class="px-[16px] py-[14px]">
                            <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-[8px] flex items-center gap-[8px]">
                                <span class="w-[6px] h-[6px] rounded-full bg-coral shrink-0"></span>
                                @lang('phonix::app.listing.sort.label')
                            </label>
                            <select
                                class="w-full bg-slate-50 dark:bg-dark-surface border border-slate-200 dark:border-dark-border rounded-lg px-[10px] py-[9px] text-sm text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-phoenix-400 outline-none cursor-pointer"
                                @change="setSort($event.target.value)"
                            >
                                @foreach ($sortOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $currentSort === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Mobile Apply/Close --}}
                    <button
                        type="button"
                        @click="filtersOpen = false"
                        class="w-full lg:hidden btn-phoenix py-[12px] text-sm font-semibold"
                    >
                        @lang('phonix::app.listing.filters.apply')
                    </button>

                </div>
            </aside>

            {{-- ============================================================
                 PRODUCT AREA
            ============================================================ --}}
            <div class="flex-1 min-w-0">

                {{-- ── TOOLBAR ──────────────────────────── --}}
                <div class="flex flex-wrap items-center justify-between gap-[12px] mb-[20px]">

                    {{-- Left: mobile filter + count --}}
                    <div class="flex items-center gap-[10px]">
                        <button
                            type="button"
                            @click="filtersOpen = true"
                            class="lg:hidden inline-flex items-center gap-[6px] px-[14px] py-[9px] rounded-xl bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border text-sm font-medium text-slate-600 dark:text-slate-400 hover:border-phoenix-400 hover:text-phoenix-600 transition-all shadow-sm"
                        >
                            <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                            </svg>
                            @lang('phonix::app.listing.filters.title')
                            @if (count($activeFilters))
                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-full bg-phoenix-500 text-white text-[10px] font-bold">{{ count($activeFilters) }}</span>
                            @endif
                        </button>

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            @if ($paginatorTotal > 0)
                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $paginatorFrom }}–{{ $paginatorTo }}</span>
                                <span class="text-slate-400 dark:text-slate-500"> / {{ $paginatorTotal }}</span>
                            @else
                                @lang('phonix::app.listing.no_results')
                            @endif
                        </p>
                    </div>

                    {{-- Right: sort + limit + view --}}
                    <div class="flex items-center gap-[8px]">
                        {{-- Sort --}}
                        <div class="hidden lg:block">
                            <select
                                class="bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border rounded-xl px-[14px] py-[9px] text-sm text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-phoenix-400 outline-none cursor-pointer shadow-sm min-w-[160px]"
                                @change="setSort($event.target.value)"
                            >
                                @foreach ($sortOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $currentSort === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Per page --}}
                        <select
                            class="hidden sm:block bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border rounded-xl px-[14px] py-[9px] text-sm text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-phoenix-400 outline-none cursor-pointer shadow-sm"
                            @change="setLimit($event.target.value)"
                        >
                            @foreach ([12, 24, 48] as $opt)
                                <option value="{{ $opt }}" {{ $currentLimit === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>

                        {{-- View toggle --}}
                        <div class="hidden sm:flex items-center bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border rounded-xl overflow-hidden shadow-sm">
                            <button
                                type="button"
                                @click="setView('grid')"
                                :class="viewMode === 'grid' ? 'bg-phoenix-500 text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'"
                                class="p-[10px] transition-colors"
                                title="Grid view"
                            >
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                                </svg>
                            </button>
                            <button
                                type="button"
                                @click="setView('list')"
                                :class="viewMode === 'list' ? 'bg-phoenix-500 text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'"
                                class="p-[10px] transition-colors border-s border-slate-200 dark:border-dark-border"
                                title="List view"
                            >
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── EMPTY STATE ──────────────────────── --}}
                @if ($products->isEmpty())
                    <div class="bg-white dark:bg-dark-card rounded-2xl border border-slate-100 dark:border-dark-border p-[64px] text-center shadow-sm">
                        <div class="w-[80px] h-[80px] mx-auto mb-[24px] rounded-full bg-slate-50 dark:bg-dark-surface flex items-center justify-center">
                            <svg class="w-[40px] h-[40px] text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-slate-700 dark:text-slate-300 mb-[8px]">@lang('phonix::app.listing.empty.title')</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-[24px] max-w-[320px] mx-auto">@lang('phonix::app.listing.empty.description')</p>
                        <a href="{{ route('phonix.products.index') }}" class="btn-phoenix px-[24px] py-[10px] text-sm">@lang('phonix::app.listing.empty.clear_filters')</a>
                    </div>

                @else

                {{-- ── GRID VIEW ────────────────────────── --}}
                <div
                    x-show="viewMode === 'grid'"
                    class="grid grid-cols-2 lg:grid-cols-3 gap-[16px] md:gap-[20px]"
                >
                    @foreach ($products as $product)
                    @php
                        $pImg        = product_image()->getProductBaseImage($product);
                        $hasDiscount = $product->getTypeInstance()->haveDiscount();
                        $salePrice   = $hasDiscount ? $product->getTypeInstance()->getMinimalPrice() : null;
                        $discPct     = ($hasDiscount && $product->price > 0)
                                       ? round((($product->price - $salePrice) / $product->price) * 100) : 0;
                        $avgRating   = $product->reviews->count() > 0 ? round($product->reviews->avg('rating')) : 0;
                        $pUrl        = route('phonix.products.view', ['slug' => $product->url_key]);
                    @endphp
                    <div
                        x-data="{ hov: false }"
                        @mouseenter="hov = true"
                        @mouseleave="hov = false"
                        class="bg-white dark:bg-dark-card rounded-2xl overflow-hidden border border-slate-100 dark:border-dark-border shadow-sm hover:shadow-lg hover:border-phoenix-200 dark:hover:border-phoenix-700 transition-all duration-300 group"
                    >
                        {{-- Image --}}
                        <div class="relative aspect-square overflow-hidden bg-slate-50 dark:bg-dark-surface">
                            <a href="{{ $pUrl }}" class="block w-full h-full">
                                @if ($pImg['medium_image_url'])
                                    <img src="{{ $pImg['medium_image_url'] }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                        <svg class="w-[48px] h-[48px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                                    </div>
                                @endif
                            </a>

                            {{-- Badges --}}
                            <div class="absolute top-[10px] start-[10px] z-10 flex flex-col gap-[4px]">
                                @if ($hasDiscount && $discPct > 0)
                                    <span class="px-[8px] py-[3px] rounded-lg text-[10px] font-bold bg-coral text-white shadow-sm">-{{ $discPct }}%</span>
                                @endif
                                @if ($product->new && !$hasDiscount)
                                    <span class="px-[8px] py-[3px] rounded-lg text-[10px] font-bold bg-phoenix-500 text-white shadow-sm">@lang('phonix::app.product.new')</span>
                                @endif
                            </div>

                            {{-- Wishlist button --}}
                            <button
                                type="button"
                                @click.stop="toggleWishlist({{ $product->id }})"
                                :disabled="wishlistLoading === {{ $product->id }}"
                                :class="wishlistItems.includes({{ $product->id }}) ? 'bg-red-500 text-white shadow-red-200 dark:shadow-red-900' : 'bg-white/90 dark:bg-dark-card/90 text-slate-400 hover:text-red-500'"
                                class="absolute top-[10px] end-[10px] z-10 w-[34px] h-[34px] rounded-full flex items-center justify-center backdrop-blur-sm shadow-sm transition-all duration-200"
                            >
                                <svg class="w-[15px] h-[15px]" :fill="wishlistItems.includes({{ $product->id }}) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                </svg>
                            </button>

                            {{-- Hover overlay: Quick View --}}
                            <div
                                x-show="hov"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute bottom-[12px] inset-x-[12px] z-10"
                            >
                                <a href="{{ $pUrl }}" class="flex items-center justify-center gap-[6px] w-full h-[38px] bg-white/95 dark:bg-dark-card/95 backdrop-blur-sm rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-phoenix-500 hover:text-white transition-colors shadow-md">
                                    <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @lang('phonix::app.listing.quick_view')
                                </a>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="p-[14px] md:p-[16px]">
                            <a href="{{ $pUrl }}" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors line-clamp-2 mb-[8px] leading-snug">{{ $product->name }}</a>

                            @if ($avgRating > 0)
                                <div class="flex items-center gap-[2px] mb-[8px]">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-[12px] h-[12px] {{ $i <= $avgRating ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                    <span class="text-[11px] text-slate-400 ms-[2px]">({{ $product->reviews->count() }})</span>
                                </div>
                            @endif

                            <div class="flex items-baseline gap-[6px] mb-[12px]">
                                <span class="text-base font-bold text-phoenix-600 dark:text-phoenix-400">
                                    {{ $hasDiscount ? core()->currency($salePrice) : core()->currency($product->price) }}
                                </span>
                                @if ($hasDiscount)
                                    <span class="text-xs text-slate-400 line-through">{{ core()->currency($product->price) }}</span>
                                @endif
                            </div>

                            <button
                                type="button"
                                @click="addToCart({{ $product->id }}, '{{ $pUrl }}')"
                                :disabled="cartLoading === {{ $product->id }}"
                                class="w-full flex items-center justify-center gap-[6px] py-[9px] rounded-xl text-xs font-bold bg-phoenix-500 hover:bg-phoenix-600 text-white transition-colors shadow-sm shadow-phoenix-500/30 disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <svg x-show="cartLoading !== {{ $product->id }}" class="w-[14px] h-[14px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                                <svg x-show="cartLoading === {{ $product->id }}" x-cloak class="w-[14px] h-[14px] animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span x-show="cartLoading !== {{ $product->id }}">@lang('phonix::app.product.add_to_cart')</span>
                                <span x-show="cartLoading === {{ $product->id }}" x-cloak>@lang('phonix::app.general.loading')</span>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- ── LIST VIEW ────────────────────────── --}}
                <div x-show="viewMode === 'list'" x-cloak class="space-y-[12px]">
                    @foreach ($products as $product)
                    @php
                        $pImg        = product_image()->getProductBaseImage($product);
                        $hasDiscount = $product->getTypeInstance()->haveDiscount();
                        $salePrice   = $hasDiscount ? $product->getTypeInstance()->getMinimalPrice() : null;
                        $discPct     = ($hasDiscount && $product->price > 0)
                                       ? round((($product->price - $salePrice) / $product->price) * 100) : 0;
                        $avgRating   = $product->reviews->count() > 0 ? round($product->reviews->avg('rating')) : 0;
                        $pUrl        = route('phonix.products.view', ['slug' => $product->url_key]);
                    @endphp
                    <article class="flex gap-0 bg-white dark:bg-dark-card rounded-2xl border border-slate-100 dark:border-dark-border shadow-sm hover:shadow-md hover:border-phoenix-200 dark:hover:border-phoenix-700 transition-all duration-300 overflow-hidden group">
                        <a href="{{ $pUrl }}" class="relative shrink-0 w-[160px] sm:w-[200px] aspect-square bg-slate-50 dark:bg-dark-surface overflow-hidden">
                            @if ($pImg['medium_image_url'])
                                <img src="{{ $pImg['medium_image_url'] }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" />
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                    <svg class="w-[36px] h-[36px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                                </div>
                            @endif
                            @if ($hasDiscount && $discPct > 0)
                                <span class="absolute top-[8px] start-[8px] px-[8px] py-[3px] rounded-lg text-[10px] font-bold bg-coral text-white">-{{ $discPct }}%</span>
                            @endif
                        </a>
                        <div class="flex-1 p-[16px] sm:p-[20px] flex flex-col justify-between min-w-0">
                            <div>
                                <a href="{{ $pUrl }}" class="block text-base font-semibold text-slate-800 dark:text-slate-200 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors line-clamp-2 mb-[6px]">{{ $product->name }}</a>
                                @if ($avgRating > 0)
                                    <div class="flex items-center gap-[3px] mb-[8px]">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="w-[13px] h-[13px] {{ $i <= $avgRating ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        @endfor
                                        <span class="text-xs text-slate-400 ms-[4px]">({{ $product->reviews->count() }})</span>
                                    </div>
                                @endif
                                @if ($product->short_description)
                                    <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">{{ strip_tags($product->short_description) }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-[10px] pt-[12px] mt-[12px] border-t border-slate-100 dark:border-dark-border">
                                <div class="flex items-baseline gap-[8px]">
                                    <span class="text-lg font-bold text-phoenix-600 dark:text-phoenix-400">{{ $hasDiscount ? core()->currency($salePrice) : core()->currency($product->price) }}</span>
                                    @if ($hasDiscount)
                                        <span class="text-sm text-slate-400 line-through">{{ core()->currency($product->price) }}</span>
                                        <span class="px-[6px] py-[2px] rounded text-[10px] font-bold bg-coral/10 text-coral">-{{ $discPct }}%</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-[8px]">
                                    <button
                                        type="button"
                                        @click="toggleWishlist({{ $product->id }})"
                                        :class="wishlistItems.includes({{ $product->id }}) ? 'text-red-500 border-red-300' : 'text-slate-400 border-slate-200 dark:border-dark-border hover:border-coral hover:text-coral'"
                                        class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border transition-colors"
                                    >
                                        <svg class="w-[16px] h-[16px]" :fill="wishlistItems.includes({{ $product->id }}) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click="addToCart({{ $product->id }}, '{{ $pUrl }}')"
                                        :disabled="cartLoading === {{ $product->id }}"
                                        class="flex items-center gap-[6px] px-[18px] h-[38px] rounded-xl text-xs font-bold bg-phoenix-500 hover:bg-phoenix-600 text-white transition-colors shadow-sm disabled:opacity-60"
                                    >
                                        <svg x-show="cartLoading !== {{ $product->id }}" class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                                        <svg x-show="cartLoading === {{ $product->id }}" x-cloak class="w-[14px] h-[14px] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        @lang('phonix::app.product.add_to_cart')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>

                @endif {{-- end empty check --}}

                {{-- ── PAGINATION ───────────────────────── --}}
                @if (method_exists($products, 'hasPages') && $products->hasPages())
                    @php
                        $curPage  = $products->currentPage();
                        $lastPage = $products->lastPage();
                    @endphp
                    <nav class="flex flex-col sm:flex-row items-center justify-between gap-[16px] mt-[40px] pt-[24px] border-t border-slate-200 dark:border-dark-border">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            @lang('phonix::app.listing.pagination.showing_of', ['from' => $paginatorFrom, 'to' => $paginatorTo, 'total' => $paginatorTotal])
                        </p>
                        <div class="flex items-center gap-[4px]">
                            @if ($products->onFirstPage())
                                <span class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border border-slate-200 dark:border-dark-border text-slate-300 cursor-not-allowed">
                                    <svg class="w-[15px] h-[15px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                                </span>
                            @else
                                <a href="{{ $products->appends(request()->query())->previousPageUrl() }}" class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border border-slate-200 dark:border-dark-border text-slate-500 hover:bg-white dark:hover:bg-dark-card hover:border-phoenix-300 hover:text-phoenix-600 transition-all shadow-sm">
                                    <svg class="w-[15px] h-[15px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                                </a>
                            @endif

                            @for ($p = 1; $p <= $lastPage; $p++)
                                @if ($p === 1 || $p === $lastPage || abs($p - $curPage) <= 1)
                                    @if ($p === $curPage)
                                        <span class="flex items-center justify-center min-w-[38px] h-[38px] px-[10px] rounded-xl text-sm font-bold bg-phoenix-500 text-white shadow-sm shadow-phoenix-500/30">{{ $p }}</span>
                                    @else
                                        <a href="{{ $products->appends(request()->query())->url($p) }}" class="flex items-center justify-center min-w-[38px] h-[38px] px-[10px] rounded-xl border border-slate-200 dark:border-dark-border text-sm text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-dark-card hover:border-phoenix-300 hover:text-phoenix-600 transition-all shadow-sm">{{ $p }}</a>
                                    @endif
                                @elseif (($p === 2 && $curPage > 3) || ($p === $lastPage - 1 && $curPage < $lastPage - 2))
                                    <span class="flex items-center justify-center w-[38px] h-[38px] text-slate-400">…</span>
                                @endif
                            @endfor

                            @if ($products->hasMorePages())
                                <a href="{{ $products->appends(request()->query())->nextPageUrl() }}" class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border border-slate-200 dark:border-dark-border text-slate-500 hover:bg-white dark:hover:bg-dark-card hover:border-phoenix-300 hover:text-phoenix-600 transition-all shadow-sm">
                                    <svg class="w-[15px] h-[15px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            @else
                                <span class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border border-slate-200 dark:border-dark-border text-slate-300 cursor-not-allowed">
                                    <svg class="w-[15px] h-[15px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </span>
                            @endif
                        </div>
                    </nav>
                @endif

            </div>{{-- end product area --}}
        </div>{{-- end flex --}}
    </div>{{-- end container --}}
</div>{{-- end bg wrapper --}}

</div>{{-- end x-data --}}

</x-phonix::layouts.index>
