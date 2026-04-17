@php
    $productRepository   = app(\Webkul\Product\Repositories\ProductRepository::class);
    $categoryRepository  = app(\Webkul\Category\Repositories\CategoryRepository::class);
    $attributeRepository = app(\Webkul\Attribute\Repositories\AttributeRepository::class);

    // Filterable attributes (for extensibility)
    $filterableAttributes = $attributeRepository->findWhere(['is_filterable' => 1]);

    // Category tree for sidebar
    $allCategories = $categoryRepository->getVisibleCategoryTree(
        core()->getCurrentChannel()->root_category_id
    );

    // Build clean search params
    $searchParams = array_merge(
        ['status' => 1, 'visible_individually' => 1],
        request()->only(['category_id', 'price', 'sort', 'limit', 'page', 'query'])
    );

    // Get paginated products
    $products = $productRepository->getAll($searchParams);

    // Current filter state
    $currentSort     = request('sort', 'created_at-desc');
    $currentLimit    = (int) request('limit', 12);
    $priceRange      = request('price', '');
    $selectedCategory = request('category_id', '');
    $inStockOnly     = request('in_stock', '');
    $minRating       = request('rating', '');

    // Parse price range string "min,max"
    $priceMin = 0;
    $priceMax = 20000;
    if ($priceRange && str_contains($priceRange, ',')) {
        [$priceMin, $priceMax] = explode(',', $priceRange, 2);
        $priceMin = (int) $priceMin;
        $priceMax = (int) $priceMax;
    }

    // Detect active filters for pills
    $activeFilters = [];
    if ($selectedCategory) {
        $cat = $categoryRepository->find($selectedCategory);
        if ($cat) {
            $activeFilters[] = ['key' => 'category_id', 'label' => $cat->name, 'value' => $selectedCategory];
        }
    }
    if ($priceRange) {
        $activeFilters[] = [
            'key'   => 'price',
            'label' => core()->currency($priceMin) . ' – ' . core()->currency($priceMax),
            'value' => $priceRange,
        ];
    }
    if ($inStockOnly) {
        $activeFilters[] = ['key' => 'in_stock', 'label' => __('phonix::app.product.in_stock'), 'value' => '1'];
    }
    if ($minRating) {
        $activeFilters[] = ['key' => 'rating', 'label' => $minRating . '+ ★', 'value' => $minRating];
    }

    // Pagination meta
    $paginatorFrom  = $products->firstItem() ?? 0;
    $paginatorTo    = $products->lastItem()  ?? 0;
    $paginatorTotal = $products->total();

    // Sort options map (keys must match Bagisto's Toolbar::getAvailableOrders values)
    $sortOptions = [
        'created_at-desc' => __('phonix::app.listing.sort.newest'),
        'price-asc'       => __('phonix::app.listing.sort.price_low'),
        'price-desc'      => __('phonix::app.listing.sort.price_high'),
        'name-asc'        => __('phonix::app.listing.sort.name_asc'),
        'name-desc'       => __('phonix::app.listing.sort.name_desc'),
    ];

    // Limit options
    $limitOptions = [12, 24, 48];
@endphp

{{-- Suppress x-cloak flash --}}
@pushOnce('styles')
<style>[x-cloak]{display:none!important}</style>
@endPushOnce

<x-phonix::layouts.index :title="__('phonix::app.listing.title')">

<div
    x-data="{
        viewMode: 'grid',
        filtersOpen: false,
        compareList: [],
        priceMin: {{ $priceMin }},
        priceMax: {{ $priceMax }},
        toggleCompare(id) {
            const idx = this.compareList.indexOf(id);
            if (idx === -1) {
                if (this.compareList.length < 4) {
                    this.compareList.push(id);
                }
            } else {
                this.compareList.splice(idx, 1);
            }
        },
        inCompare(id) {
            return this.compareList.includes(id);
        },
        submitFilterForm() {
            this.$refs.filterForm.submit();
        }
    }"
    class="container mx-auto section-padding"
>

    {{-- ================================================================
         BREADCRUMB
    ================================================================ --}}
    <x-phonix::breadcrumb :items="[
        ['label' => __('phonix::app.general.home'), 'url' => route('phonix.home')],
        ['label' => __('phonix::app.general.shop')],
    ]" />

    {{-- ================================================================
         PAGE HEADER
    ================================================================ --}}
    <div class="mb-[24px]" data-gsap="fade-up">
        <h1 class="text-fluid-2xl font-bold text-slate-900 dark:text-white mb-[6px]">
            @lang('phonix::app.listing.title')
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            @lang('phonix::app.listing.subtitle')
        </p>
    </div>

    {{-- ================================================================
         ACTIVE FILTER PILLS
    ================================================================ --}}
    @if (count($activeFilters))
        <div class="flex flex-wrap items-center gap-[8px] mb-[20px]" data-gsap="fade-in">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                @lang('phonix::app.listing.filters.active')
            </span>
            @foreach ($activeFilters as $filter)
                <a
                    href="{{ request()->fullUrlWithoutQuery([$filter['key']]) }}"
                    class="inline-flex items-center gap-[6px] px-[10px] py-[4px] rounded-full text-xs font-medium bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-700 dark:text-phoenix-300 border border-phoenix-200 dark:border-phoenix-700 hover:bg-phoenix-100 dark:hover:bg-phoenix-900/50 transition-colors"
                    aria-label="@lang('phonix::app.listing.filters.remove') {{ $filter['label'] }}"
                >
                    {{ $filter['label'] }}
                    <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            @endforeach
            <a
                href="{{ route('phonix.products.index') }}"
                class="inline-flex items-center gap-[4px] text-xs font-medium text-coral hover:text-red-600 dark:hover:text-red-400 transition-colors ms-[4px]"
            >
                <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                @lang('phonix::app.listing.filters.clear_all')
            </a>
        </div>
    @endif

    {{-- ================================================================
         MAIN CONTENT: SIDEBAR + PRODUCT AREA
    ================================================================ --}}
    <div class="flex gap-[24px] items-start">

        {{-- ==============================================================
             SIDEBAR FILTERS
        ============================================================== --}}

        {{-- Mobile overlay backdrop --}}
        <div
            x-show="filtersOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="filtersOpen = false"
            class="fixed inset-0 z-[50] bg-black/50 backdrop-blur-sm lg:hidden"
            x-cloak
            aria-hidden="true"
        ></div>

        {{-- Sidebar --}}
        <aside
            :class="filtersOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed top-0 start-0 z-[51] h-full w-[280px] overflow-y-auto
                   bg-white dark:bg-dark-surface border-e border-slate-200 dark:border-dark-border
                   transition-transform duration-300 ease-out
                   lg:static lg:z-auto lg:h-auto lg:w-[240px] lg:shrink-0
                   lg:border-e-0 lg:bg-transparent lg:dark:bg-transparent
                   lg:sticky lg:top-[80px] lg:self-start
                   scrollbar-thin"
            aria-label="@lang('phonix::app.listing.filters.title')"
        >
            {{-- Drawer header (mobile only) --}}
            <div class="flex items-center justify-between p-[16px] border-b border-slate-200 dark:border-dark-border lg:hidden">
                <h3 class="text-base font-semibold text-slate-800 dark:text-white">
                    @lang('phonix::app.listing.filters.title')
                </h3>
                <button
                    @click="filtersOpen = false"
                    class="p-[8px] text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors rounded-md hover:bg-slate-100 dark:hover:bg-dark-card"
                    aria-label="@lang('phonix::app.general.close')"
                >
                    <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Filter form — GET, appends to URL --}}
            <form
                x-ref="filterForm"
                method="GET"
                action="{{ route('phonix.products.index') }}"
                class="p-[16px] lg:p-0 space-y-[12px]"
                data-gsap="fade-in"
            >
                {{-- Preserve non-filter params --}}
                @if (request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                @if (request('limit'))
                    <input type="hidden" name="limit" value="{{ request('limit') }}">
                @endif

                {{-- Desktop header --}}
                <div class="hidden lg:flex items-center justify-between mb-[4px]">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">
                        @lang('phonix::app.listing.filters.title')
                    </h3>
                    <a
                        href="{{ route('phonix.products.index', array_filter(['sort' => request('sort'), 'limit' => request('limit')])) }}"
                        class="text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors"
                    >
                        @lang('phonix::app.listing.filters.clear_all')
                    </a>
                </div>

                {{-- ---- 1. CATEGORIES ---- --}}
                <div
                    x-data="{ open: true }"
                    class="card-phoenix overflow-hidden"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center justify-between w-full p-[14px] text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-dark-card transition-colors"
                        :aria-expanded="open.toString()"
                    >
                        <span>@lang('phonix::app.listing.filters.category')</span>
                        <svg
                            :class="open ? 'rotate-180' : ''"
                            class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-collapse
                        class="border-t border-slate-100 dark:border-dark-border"
                    >
                        <fieldset class="p-[14px] pt-[10px]">
                            <legend class="sr-only">@lang('phonix::app.listing.filters.category')</legend>
                            <div class="space-y-[6px] max-h-[220px] overflow-y-auto scrollbar-thin pe-[4px]">
                                <label class="flex items-center gap-[8px] cursor-pointer group">
                                    <input
                                        type="radio"
                                        name="category_id"
                                        value=""
                                        {{ $selectedCategory === '' ? 'checked' : '' }}
                                        class="w-[14px] h-[14px] border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400"
                                        @change="submitFilterForm()"
                                    />
                                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300 group-hover:text-phoenix-600 dark:group-hover:text-phoenix-400 transition-colors">
                                        @lang('phonix::app.listing.filters.all_categories')
                                    </span>
                                </label>

                                @foreach ($allCategories as $category)
                                    @php $hasChildren = $category->children && $category->children->count() > 0; @endphp
                                    <div>
                                        <label class="flex items-center gap-[8px] cursor-pointer group">
                                            <input
                                                type="radio"
                                                name="category_id"
                                                value="{{ $category->id }}"
                                                {{ (string)$selectedCategory === (string)$category->id ? 'checked' : '' }}
                                                class="w-[14px] h-[14px] border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400"
                                                @change="submitFilterForm()"
                                            />
                                            <span class="text-xs text-slate-600 dark:text-slate-400 group-hover:text-phoenix-600 dark:group-hover:text-phoenix-400 transition-colors">
                                                {{ $category->name }}
                                            </span>
                                        </label>

                                        @if ($hasChildren)
                                            <div class="ms-[22px] mt-[4px] space-y-[4px]">
                                                @foreach ($category->children as $child)
                                                    <label class="flex items-center gap-[8px] cursor-pointer group">
                                                        <input
                                                            type="radio"
                                                            name="category_id"
                                                            value="{{ $child->id }}"
                                                            {{ (string)$selectedCategory === (string)$child->id ? 'checked' : '' }}
                                                            class="w-[14px] h-[14px] border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400"
                                                            @change="submitFilterForm()"
                                                        />
                                                        <span class="text-xs text-slate-500 dark:text-slate-500 group-hover:text-phoenix-600 dark:group-hover:text-phoenix-400 transition-colors">
                                                            {{ $child->name }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>
                </div>

                {{-- ---- 2. PRICE RANGE ---- --}}
                <div
                    x-data="{ open: true }"
                    class="card-phoenix overflow-hidden"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center justify-between w-full p-[14px] text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-dark-card transition-colors"
                        :aria-expanded="open.toString()"
                    >
                        <span>@lang('phonix::app.listing.filters.price_range')</span>
                        <svg
                            :class="open ? 'rotate-180' : ''"
                            class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-collapse
                        class="border-t border-slate-100 dark:border-dark-border"
                    >
                        <fieldset class="p-[14px] pt-[10px]">
                            <legend class="sr-only">@lang('phonix::app.listing.filters.price_range')</legend>

                            {{-- Hidden price input that gets submitted --}}
                            <input
                                type="hidden"
                                name="price"
                                :value="priceMin + ',' + priceMax"
                            />

                            {{-- Dual range track visual --}}
                            <div class="relative mb-[16px]">
                                {{-- Track background --}}
                                <div class="h-[4px] rounded-full bg-slate-200 dark:bg-dark-border relative">
                                    {{-- Active range highlight --}}
                                    <div
                                        class="absolute h-full rounded-full bg-phoenix-500"
                                        :style="'left:' + (priceMin / 20000 * 100) + '%;right:' + (100 - priceMax / 20000 * 100) + '%'"
                                    ></div>
                                </div>
                                {{-- Min handle --}}
                                <input
                                    type="range"
                                    min="0"
                                    max="20000"
                                    step="50"
                                    x-model.number="priceMin"
                                    @input="if(priceMin > priceMax - 50) priceMin = priceMax - 50"
                                    class="absolute top-[-6px] w-full h-[16px] appearance-none bg-transparent cursor-pointer
                                           [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-[18px]
                                           [&::-webkit-slider-thumb]:h-[18px] [&::-webkit-slider-thumb]:rounded-full
                                           [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:border-2
                                           [&::-webkit-slider-thumb]:border-phoenix-500 [&::-webkit-slider-thumb]:shadow-md
                                           [&::-moz-range-thumb]:w-[18px] [&::-moz-range-thumb]:h-[18px]
                                           [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-white
                                           [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-phoenix-500
                                           [&::-webkit-slider-runnable-track]:bg-transparent
                                           [&::-moz-range-track]:bg-transparent"
                                    style="pointer-events: none"
                                    :style="'pointer-events:' + (priceMin >= priceMax - 50 ? 'all' : 'none')"
                                    aria-label="@lang('phonix::app.listing.filters.price_min')"
                                />
                                {{-- Max handle --}}
                                <input
                                    type="range"
                                    min="0"
                                    max="20000"
                                    step="50"
                                    x-model.number="priceMax"
                                    @input="if(priceMax < priceMin + 50) priceMax = priceMin + 50"
                                    class="absolute top-[-6px] w-full h-[16px] appearance-none bg-transparent cursor-pointer
                                           [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-[18px]
                                           [&::-webkit-slider-thumb]:h-[18px] [&::-webkit-slider-thumb]:rounded-full
                                           [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:border-2
                                           [&::-webkit-slider-thumb]:border-phoenix-500 [&::-webkit-slider-thumb]:shadow-md
                                           [&::-moz-range-thumb]:w-[18px] [&::-moz-range-thumb]:h-[18px]
                                           [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-white
                                           [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-phoenix-500
                                           [&::-webkit-slider-runnable-track]:bg-transparent
                                           [&::-moz-range-track]:bg-transparent"
                                    aria-label="@lang('phonix::app.listing.filters.price_max')"
                                />
                            </div>

                            {{-- Manual inputs --}}
                            <div class="flex items-center gap-[8px]">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-medium text-slate-400 dark:text-slate-500 mb-[4px] uppercase tracking-wider">
                                        @lang('phonix::app.listing.filters.price_min')
                                    </label>
                                    <input
                                        type="number"
                                        x-model.number="priceMin"
                                        min="0"
                                        max="20000"
                                        step="50"
                                        @input="if(priceMin > priceMax - 50) priceMin = priceMax - 50"
                                        class="input-phoenix text-xs py-[8px] px-[10px] w-full"
                                        aria-label="@lang('phonix::app.listing.filters.price_min')"
                                    />
                                </div>
                                <span class="text-slate-300 dark:text-dark-border text-sm mt-[14px]">–</span>
                                <div class="flex-1">
                                    <label class="block text-[10px] font-medium text-slate-400 dark:text-slate-500 mb-[4px] uppercase tracking-wider">
                                        @lang('phonix::app.listing.filters.price_max')
                                    </label>
                                    <input
                                        type="number"
                                        x-model.number="priceMax"
                                        min="0"
                                        max="20000"
                                        step="50"
                                        @input="if(priceMax < priceMin + 50) priceMax = priceMin + 50"
                                        class="input-phoenix text-xs py-[8px] px-[10px] w-full"
                                        aria-label="@lang('phonix::app.listing.filters.price_max')"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[10px] text-slate-400 dark:text-slate-500 mt-[6px]">
                                <span x-text="'$' + priceMin.toLocaleString()"></span>
                                <span x-text="'$' + priceMax.toLocaleString()"></span>
                            </div>

                            <button
                                type="submit"
                                class="mt-[12px] w-full btn-phoenix-outline py-[8px] text-xs font-semibold"
                            >
                                @lang('phonix::app.listing.filters.apply_price')
                            </button>
                        </fieldset>
                    </div>
                </div>

                {{-- ---- 3. AVAILABILITY ---- --}}
                <div
                    x-data="{ open: false }"
                    class="card-phoenix overflow-hidden"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center justify-between w-full p-[14px] text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-dark-card transition-colors"
                        :aria-expanded="open.toString()"
                    >
                        <span>@lang('phonix::app.listing.filters.availability')</span>
                        <svg
                            :class="open ? 'rotate-180' : ''"
                            class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-collapse
                        class="border-t border-slate-100 dark:border-dark-border"
                    >
                        <fieldset class="p-[14px] pt-[10px]">
                            <legend class="sr-only">@lang('phonix::app.listing.filters.availability')</legend>
                            <label class="flex items-center gap-[8px] cursor-pointer group">
                                <input
                                    type="checkbox"
                                    name="in_stock"
                                    value="1"
                                    {{ $inStockOnly ? 'checked' : '' }}
                                    class="w-[15px] h-[15px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400"
                                    @change="submitFilterForm()"
                                />
                                <span class="text-xs text-slate-600 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors flex items-center gap-[6px]">
                                    <span class="inline-block w-[8px] h-[8px] rounded-full bg-emerald-500"></span>
                                    @lang('phonix::app.product.in_stock')
                                </span>
                            </label>
                        </fieldset>
                    </div>
                </div>

                {{-- ---- 4. RATING ---- --}}
                <div
                    x-data="{ open: false }"
                    class="card-phoenix overflow-hidden"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center justify-between w-full p-[14px] text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-dark-card transition-colors"
                        :aria-expanded="open.toString()"
                    >
                        <span>@lang('phonix::app.listing.filters.rating')</span>
                        <svg
                            :class="open ? 'rotate-180' : ''"
                            class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-collapse
                        class="border-t border-slate-100 dark:border-dark-border"
                    >
                        <fieldset class="p-[14px] pt-[10px] space-y-[4px]">
                            <legend class="sr-only">@lang('phonix::app.listing.filters.rating')</legend>
                            @for ($stars = 4; $stars >= 1; $stars--)
                                <label class="flex items-center gap-[8px] cursor-pointer group px-[6px] py-[5px] rounded-md hover:bg-slate-50 dark:hover:bg-dark-card transition-colors">
                                    <input
                                        type="radio"
                                        name="rating"
                                        value="{{ $stars }}"
                                        {{ (string)$minRating === (string)$stars ? 'checked' : '' }}
                                        class="w-[14px] h-[14px] border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400"
                                        @change="submitFilterForm()"
                                    />
                                    <div class="flex items-center gap-[3px]">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg
                                                class="w-[13px] h-[13px] {{ $i <= $stars ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                                fill="currentColor" viewBox="0 0 20 20"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 ms-[2px]">
                                            @lang('phonix::app.listing.filters.rating_and_up')
                                        </span>
                                    </div>
                                </label>
                            @endfor
                            @if ($minRating)
                                <a
                                    href="{{ request()->fullUrlWithoutQuery(['rating']) }}"
                                    class="block text-xs text-phoenix-600 dark:text-phoenix-400 hover:underline mt-[4px] ps-[6px]"
                                >
                                    @lang('phonix::app.listing.filters.clear_rating')
                                </a>
                            @endif
                        </fieldset>
                    </div>
                </div>

                {{-- ---- Mobile Apply / Clear buttons ---- --}}
                <div class="flex gap-[8px] pt-[4px] lg:hidden">
                    <a
                        href="{{ route('phonix.products.index') }}"
                        class="flex-1 btn-phoenix-ghost px-[16px] py-[10px] text-xs text-center"
                    >
                        @lang('phonix::app.listing.filters.clear_all')
                    </a>
                    <button
                        type="submit"
                        class="flex-1 btn-phoenix px-[16px] py-[10px] text-xs"
                        @click="filtersOpen = false"
                    >
                        @lang('phonix::app.listing.filters.apply')
                    </button>
                </div>
            </form>
        </aside>

        {{-- ==============================================================
             MAIN PRODUCT AREA
        ============================================================== --}}
        <div class="flex-1 min-w-0">

            {{-- ============================================================
                 TOOLBAR
            ============================================================ --}}
            <div
                class="flex flex-wrap items-center justify-between gap-[12px] mb-[20px] pb-[16px] border-b border-slate-200 dark:border-dark-border"
                data-gsap="fade-in"
            >
                {{-- Left: mobile filter toggle + results count --}}
                <div class="flex items-center gap-[12px]">
                    {{-- Mobile filter open button --}}
                    <button
                        type="button"
                        @click="filtersOpen = true"
                        class="lg:hidden inline-flex items-center gap-[6px] px-[12px] py-[8px] rounded-lg border border-slate-200 dark:border-dark-border text-sm text-slate-600 dark:text-slate-400 hover:border-phoenix-400 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors"
                        aria-label="@lang('phonix::app.listing.filters.title')"
                    >
                        <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                        </svg>
                        @lang('phonix::app.listing.filters.title')
                        @if (count($activeFilters))
                            <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-full bg-phoenix-500 text-white text-[10px] font-bold">
                                {{ count($activeFilters) }}
                            </span>
                        @endif
                    </button>

                    {{-- Results count --}}
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        @if ($paginatorTotal > 0)
                            @lang('phonix::app.listing.pagination.showing_of', [
                                'from'  => $paginatorFrom,
                                'to'    => $paginatorTo,
                                'total' => $paginatorTotal,
                            ])
                        @else
                            @lang('phonix::app.listing.no_results')
                        @endif
                    </p>
                </div>

                {{-- Right: sort + limit + view toggle --}}
                <div class="flex items-center gap-[10px]">

                    {{-- Sort dropdown --}}
                    <form method="GET" action="{{ route('phonix.products.index') }}" class="contents">
                        @foreach (request()->except(['sort', 'page']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ is_array($val) ? implode(',', $val) : $val }}">
                        @endforeach
                        <label for="sort-select" class="sr-only">@lang('phonix::app.listing.sort.label')</label>
                        <select
                            id="sort-select"
                            name="sort"
                            class="input-phoenix text-xs py-[8px] ps-[12px] pe-[32px] min-w-[160px] cursor-pointer"
                            onchange="this.form.submit()"
                            aria-label="@lang('phonix::app.listing.sort.label')"
                        >
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" {{ $currentSort === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Per-page limit --}}
                    <form method="GET" action="{{ route('phonix.products.index') }}" class="contents">
                        @foreach (request()->except(['limit', 'page']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ is_array($val) ? implode(',', $val) : $val }}">
                        @endforeach
                        <label for="limit-select" class="sr-only">@lang('phonix::app.listing.per_page')</label>
                        <select
                            id="limit-select"
                            name="limit"
                            class="input-phoenix text-xs py-[8px] ps-[12px] pe-[32px] cursor-pointer hidden sm:block"
                            onchange="this.form.submit()"
                            aria-label="@lang('phonix::app.listing.per_page')"
                        >
                            @foreach ($limitOptions as $opt)
                                <option value="{{ $opt }}" {{ $currentLimit === $opt ? 'selected' : '' }}>
                                    {{ $opt }} / @lang('phonix::app.listing.per_page_short')
                                </option>
                            @endforeach
                        </select>
                    </form>

                    {{-- View mode toggle --}}
                    <div
                        class="hidden sm:flex items-center border border-slate-200 dark:border-dark-border rounded-lg overflow-hidden"
                        role="group"
                        aria-label="@lang('phonix::app.listing.view.toggle')"
                    >
                        <button
                            type="button"
                            @click="viewMode = 'grid'"
                            :class="viewMode === 'grid'
                                ? 'bg-phoenix-500 text-white'
                                : 'bg-white dark:bg-dark-card text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-surface'"
                            class="p-[9px] transition-colors"
                            aria-label="@lang('phonix::app.listing.view.grid')"
                            :aria-pressed="(viewMode === 'grid').toString()"
                        >
                            <svg class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            @click="viewMode = 'list'"
                            :class="viewMode === 'list'
                                ? 'bg-phoenix-500 text-white'
                                : 'bg-white dark:bg-dark-card text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-surface'"
                            class="p-[9px] transition-colors border-s border-slate-200 dark:border-dark-border"
                            aria-label="@lang('phonix::app.listing.view.list')"
                            :aria-pressed="(viewMode === 'list').toString()"
                        >
                            <svg class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 COMPARE BAR (appears when compareList has items)
            ============================================================ --}}
            <div
                x-show="compareList.length > 0"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="flex items-center justify-between gap-[12px] mb-[16px] px-[16px] py-[10px] rounded-lg bg-phoenix-50 dark:bg-phoenix-900/30 border border-phoenix-200 dark:border-phoenix-700"
                x-cloak
            >
                <p class="text-sm font-medium text-phoenix-700 dark:text-phoenix-300">
                    <span x-text="compareList.length"></span>
                    @lang('phonix::app.listing.compare.selected')
                    <span class="text-xs text-phoenix-500">(max 4)</span>
                </p>
                <div class="flex items-center gap-[8px]">
                    <button
                        type="button"
                        class="btn-phoenix text-xs px-[16px] py-[7px]"
                    >
                        @lang('phonix::app.listing.compare.go')
                    </button>
                    <button
                        type="button"
                        @click="compareList = []"
                        class="text-xs text-phoenix-600 dark:text-phoenix-400 hover:underline"
                    >
                        @lang('phonix::app.listing.compare.clear')
                    </button>
                </div>
            </div>

            {{-- ============================================================
                 EMPTY STATE
            ============================================================ --}}
            @if ($products->isEmpty())
                <div
                    class="flex flex-col items-center justify-center py-[72px] px-[24px] text-center"
                    data-gsap="fade-up"
                >
                    {{-- SVG illustration --}}
                    <svg
                        class="w-[96px] h-[96px] text-slate-300 dark:text-slate-600 mb-[24px]"
                        fill="none" viewBox="0 0 120 120"
                        aria-hidden="true"
                    >
                        <circle cx="60" cy="60" r="56" stroke="currentColor" stroke-width="3" stroke-dasharray="8 4" opacity="0.4"/>
                        <path d="M40 48h40M40 60h28M40 72h20" stroke="currentColor" stroke-width="3" stroke-linecap="round" opacity="0.5"/>
                        <circle cx="82" cy="72" r="14" stroke="currentColor" stroke-width="3"/>
                        <path d="M92 82l8 8" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        <path d="M55 35l5 5m0-5l-5 5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" opacity="0.6"/>
                    </svg>

                    <h2 class="text-xl font-bold text-slate-700 dark:text-slate-300 mb-[8px]">
                        @lang('phonix::app.listing.empty.title')
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-[24px] max-w-[360px]">
                        @lang('phonix::app.listing.empty.description')
                    </p>

                    <a
                        href="{{ route('phonix.products.index') }}"
                        class="btn-phoenix px-[24px] py-[10px] text-sm"
                    >
                        @lang('phonix::app.listing.empty.clear_filters')
                    </a>
                </div>

            @else

                {{-- ============================================================
                     GRID VIEW
                ============================================================ --}}
                <div
                    x-show="viewMode === 'grid'"
                    class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[16px]"
                    data-gsap="stagger"
                >
                    @foreach ($products as $product)
                        @php
                            $productImage   = product_image()->getProductBaseImage($product);
                            $hasDiscount    = $product->getTypeInstance()->haveDiscount();
                            $salePrice      = $hasDiscount ? $product->getTypeInstance()->getMinimalPrice() : null;
                            $avgRating      = $product->reviews->count() > 0
                                              ? round($product->reviews->avg('rating'))
                                              : 0;
                            $badge          = $hasDiscount ? 'sale' : ($product->new ? 'new' : null);
                            $discountPct    = ($hasDiscount && $product->price > 0)
                                              ? round((($product->price - $salePrice) / $product->price) * 100)
                                              : 0;
                        @endphp

                        <div
                            x-data="{ hovered: false }"
                            @mouseenter="hovered = true"
                            @mouseleave="hovered = false"
                            class="card-phoenix group overflow-hidden relative"
                            data-gsap="fade-up"
                        >
                            {{-- Image area --}}
                            <div class="relative overflow-hidden aspect-square bg-slate-50 dark:bg-dark-surface">
                                <a
                                    href="{{ route('phonix.products.view', ['slug' => $product->url_key]) }}"
                                    class="block w-full h-full"
                                    aria-label="{{ $product->name }}"
                                    tabindex="0"
                                >
                                    @if ($productImage['medium_image_url'])
                                        <img
                                            src="{{ $productImage['medium_image_url'] }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-110"
                                            loading="lazy"
                                            width="400"
                                            height="400"
                                        />
                                    @else
                                        {{-- Placeholder when no image --}}
                                        <div class="w-full h-full flex flex-col items-center justify-center gap-[8px] text-slate-300 dark:text-slate-600">
                                            <svg class="w-[48px] h-[48px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                            </svg>
                                            <span class="text-[10px] uppercase tracking-wider">@lang('phonix::app.general.no_image')</span>
                                        </div>
                                    @endif
                                </a>

                                {{-- Badges --}}
                                <div class="absolute top-[8px] start-[8px] z-10 flex flex-col gap-[4px]">
                                    @if ($badge === 'sale')
                                        <span class="inline-flex items-center px-[8px] py-[3px] rounded-md text-[10px] font-bold bg-coral text-white tracking-wide">
                                            -{{ $discountPct }}%
                                        </span>
                                    @elseif ($badge === 'new')
                                        <span class="inline-flex items-center px-[8px] py-[3px] rounded-md text-[10px] font-bold bg-phoenix-500 text-white tracking-wide">
                                            @lang('phonix::app.product.new')
                                        </span>
                                    @endif
                                    @if ($product->featured)
                                        <span class="inline-flex items-center px-[8px] py-[3px] rounded-md text-[10px] font-bold bg-gold text-white tracking-wide">
                                            @lang('phonix::app.product.featured')
                                        </span>
                                    @endif
                                </div>

                                {{-- Wishlist button (top-end corner) --}}
                                <button
                                    type="button"
                                    class="absolute top-[8px] end-[8px] z-10 flex items-center justify-center w-[34px] h-[34px] rounded-full bg-white/90 dark:bg-dark-card/90 backdrop-blur-sm shadow-sm text-slate-400 hover:text-coral dark:hover:text-coral transition-colors"
                                    aria-label="@lang('phonix::app.product.add_to_wishlist') - {{ $product->name }}"
                                >
                                    <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </button>

                                {{-- Quick action overlay on hover --}}
                                <div
                                    x-show="hovered"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-[8px]"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-[8px]"
                                    class="absolute bottom-[12px] inset-x-[12px] flex items-center justify-center gap-[6px] z-10"
                                    x-cloak
                                >
                                    {{-- Quick view --}}
                                    <a
                                        href="{{ route('phonix.products.view', ['slug' => $product->url_key]) }}"
                                        class="flex-1 flex items-center justify-center gap-[6px] h-[36px] bg-white dark:bg-dark-card rounded-lg shadow-md text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-phoenix-500 hover:text-white dark:hover:bg-phoenix-500 transition-colors"
                                        aria-label="@lang('phonix::app.listing.quick_view') - {{ $product->name }}"
                                    >
                                        <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        @lang('phonix::app.listing.quick_view')
                                    </a>

                                    {{-- Compare --}}
                                    <button
                                        type="button"
                                        @click.stop="toggleCompare({{ $product->id }})"
                                        :class="inCompare({{ $product->id }})
                                            ? 'bg-phoenix-500 text-white shadow-md'
                                            : 'bg-white dark:bg-dark-card text-slate-600 dark:text-slate-300 shadow-md hover:bg-phoenix-500 hover:text-white dark:hover:bg-phoenix-500'"
                                        class="flex items-center justify-center w-[36px] h-[36px] rounded-lg transition-colors"
                                        :aria-pressed="inCompare({{ $product->id }}).toString()"
                                        aria-label="@lang('phonix::app.listing.compare.add') - {{ $product->name }}"
                                    >
                                        <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Product info --}}
                            <div class="p-[14px]">
                                {{-- Name --}}
                                <a
                                    href="{{ route('phonix.products.view', ['slug' => $product->url_key]) }}"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-200 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors line-clamp-2 mb-[6px] leading-snug"
                                >
                                    {{ $product->name }}
                                </a>

                                {{-- Rating --}}
                                @if ($avgRating > 0)
                                    <div class="flex items-center gap-[3px] mb-[8px]">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg
                                                class="w-[13px] h-[13px] {{ $i <= $avgRating ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                                fill="currentColor" viewBox="0 0 20 20"
                                                aria-hidden="true"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500 ms-[3px]">
                                            ({{ $product->reviews->count() }})
                                        </span>
                                    </div>
                                @endif

                                {{-- Price --}}
                                <div class="flex items-baseline gap-[6px] mb-[12px]">
                                    <span class="text-base font-bold text-phoenix-600 dark:text-phoenix-400">
                                        {{ $hasDiscount ? core()->currency($salePrice) : core()->currency($product->price) }}
                                    </span>
                                    @if ($hasDiscount)
                                        <span class="text-xs text-slate-400 line-through">
                                            {{ core()->currency($product->price) }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Add to cart --}}
                                <button
                                    type="button"
                                    class="btn-phoenix w-full py-[9px] text-xs font-semibold flex items-center justify-center gap-[6px]"
                                    aria-label="@lang('phonix::app.product.add_to_cart') - {{ $product->name }}"
                                >
                                    <svg class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    @lang('phonix::app.product.add_to_cart')
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ============================================================
                     LIST VIEW
                ============================================================ --}}
                <div
                    x-show="viewMode === 'list'"
                    class="space-y-[12px]"
                    data-gsap="stagger"
                    x-cloak
                >
                    @foreach ($products as $product)
                        @php
                            $productImage   = product_image()->getProductBaseImage($product);
                            $hasDiscount    = $product->getTypeInstance()->haveDiscount();
                            $salePrice      = $hasDiscount ? $product->getTypeInstance()->getMinimalPrice() : null;
                            $avgRating      = $product->reviews->count() > 0
                                              ? round($product->reviews->avg('rating'))
                                              : 0;
                            $badge          = $hasDiscount ? 'sale' : ($product->new ? 'new' : null);
                            $discountPct    = ($hasDiscount && $product->price > 0)
                                              ? round((($product->price - $salePrice) / $product->price) * 100)
                                              : 0;
                        @endphp

                        <article
                            class="card-phoenix flex flex-col sm:flex-row overflow-hidden group"
                            data-gsap="fade-up"
                        >
                            {{-- Image --}}
                            <a
                                href="{{ route('phonix.products.view', ['slug' => $product->url_key]) }}"
                                class="relative sm:w-[200px] shrink-0 aspect-square sm:aspect-auto bg-slate-50 dark:bg-dark-surface overflow-hidden"
                                aria-label="{{ $product->name }}"
                                tabindex="0"
                            >
                                @if ($productImage['medium_image_url'])
                                    <img
                                        src="{{ $productImage['medium_image_url'] }}"
                                        alt="{{ $product->name }}"
                                        class="w-full h-full object-cover min-h-[160px] transition-transform duration-500 group-hover:scale-105"
                                        loading="lazy"
                                        width="200"
                                        height="200"
                                    />
                                @else
                                    <div class="w-full h-full min-h-[160px] flex items-center justify-center text-slate-300 dark:text-slate-600">
                                        <svg class="w-[40px] h-[40px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                        </svg>
                                    </div>
                                @endif

                                {{-- Badge overlay --}}
                                @if ($badge)
                                    <div class="absolute top-[8px] start-[8px] z-10">
                                        @if ($badge === 'sale')
                                            <span class="inline-flex items-center px-[8px] py-[3px] rounded-md text-[10px] font-bold bg-coral text-white">
                                                -{{ $discountPct }}%
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-[8px] py-[3px] rounded-md text-[10px] font-bold bg-phoenix-500 text-white">
                                                @lang('phonix::app.product.new')
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </a>

                            {{-- Details --}}
                            <div class="flex-1 p-[16px] sm:p-[20px] flex flex-col justify-between min-w-0">
                                <div>
                                    {{-- Name --}}
                                    <a
                                        href="{{ route('phonix.products.view', ['slug' => $product->url_key]) }}"
                                        class="block text-base font-semibold text-slate-800 dark:text-slate-200 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors line-clamp-2 mb-[6px]"
                                    >
                                        {{ $product->name }}
                                    </a>

                                    {{-- Rating --}}
                                    @if ($avgRating > 0)
                                        <div class="flex items-center gap-[3px] mb-[8px]">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg
                                                    class="w-[14px] h-[14px] {{ $i <= $avgRating ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                                    fill="currentColor" viewBox="0 0 20 20"
                                                    aria-hidden="true"
                                                >
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                            <span class="text-xs text-slate-400 dark:text-slate-500 ms-[4px]">
                                                ({{ $product->reviews->count() }} @lang('phonix::app.product.reviews'))
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Short description --}}
                                    @if ($product->short_description)
                                        <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mb-[12px] leading-relaxed">
                                            {{ $product->short_description }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Price + actions row --}}
                                <div class="flex flex-wrap items-center justify-between gap-[10px] pt-[12px] border-t border-slate-100 dark:border-dark-border">
                                    {{-- Price --}}
                                    <div class="flex items-baseline gap-[8px]">
                                        <span class="text-lg font-bold text-phoenix-600 dark:text-phoenix-400">
                                            {{ $hasDiscount ? core()->currency($salePrice) : core()->currency($product->price) }}
                                        </span>
                                        @if ($hasDiscount)
                                            <span class="text-sm text-slate-400 line-through">
                                                {{ core()->currency($product->price) }}
                                            </span>
                                            <span class="inline-flex items-center px-[6px] py-[2px] rounded text-[10px] font-bold bg-coral/10 text-coral dark:bg-coral/20">
                                                -{{ $discountPct }}%
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex items-center gap-[8px]">
                                        {{-- Wishlist --}}
                                        <button
                                            type="button"
                                            class="flex items-center justify-center w-[36px] h-[36px] rounded-lg border border-slate-200 dark:border-dark-border text-slate-400 hover:border-coral hover:text-coral transition-colors"
                                            aria-label="@lang('phonix::app.product.add_to_wishlist') - {{ $product->name }}"
                                        >
                                            <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        </button>

                                        {{-- Compare --}}
                                        <button
                                            type="button"
                                            @click="toggleCompare({{ $product->id }})"
                                            :class="inCompare({{ $product->id }})
                                                ? 'border-phoenix-500 text-phoenix-600 dark:text-phoenix-400 bg-phoenix-50 dark:bg-phoenix-900/30'
                                                : 'border-slate-200 dark:border-dark-border text-slate-400 hover:border-phoenix-400 hover:text-phoenix-500'"
                                            class="flex items-center justify-center w-[36px] h-[36px] rounded-lg border transition-colors"
                                            :aria-pressed="inCompare({{ $product->id }}).toString()"
                                            aria-label="@lang('phonix::app.listing.compare.add') - {{ $product->name }}"
                                        >
                                            <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                            </svg>
                                        </button>

                                        {{-- Add to cart --}}
                                        <button
                                            type="button"
                                            class="btn-phoenix flex items-center gap-[6px] px-[16px] py-[8px] text-xs font-semibold"
                                            aria-label="@lang('phonix::app.product.add_to_cart') - {{ $product->name }}"
                                        >
                                            <svg class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                            @lang('phonix::app.product.add_to_cart')
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

            @endif {{-- end products->isEmpty() --}}

            {{-- ============================================================
                 PAGINATION
            ============================================================ --}}
            @if (method_exists($products, 'hasPages') && $products->hasPages())
                <nav
                    class="flex flex-col sm:flex-row items-center justify-between gap-[16px] mt-[40px] pt-[24px] border-t border-slate-200 dark:border-dark-border"
                    aria-label="@lang('phonix::app.listing.pagination.label')"
                    data-gsap="fade-in"
                >
                    {{-- Results info --}}
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        @lang('phonix::app.listing.pagination.showing_of', [
                            'from'  => $paginatorFrom,
                            'to'    => $paginatorTo,
                            'total' => $paginatorTotal,
                        ])
                    </p>

                    {{-- Page buttons --}}
                    <div class="flex items-center gap-[4px]">
                        {{-- Previous --}}
                        @if ($products->onFirstPage())
                            <span
                                class="flex items-center justify-center w-[36px] h-[36px] rounded-lg border border-slate-200 dark:border-dark-border text-slate-300 dark:text-slate-600 cursor-not-allowed"
                                aria-disabled="true"
                                aria-label="@lang('phonix::app.listing.pagination.previous')"
                            >
                                <svg class="w-[15px] h-[15px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </span>
                        @else
                            <a
                                href="{{ $products->appends(request()->query())->previousPageUrl() }}"
                                class="flex items-center justify-center w-[36px] h-[36px] rounded-lg border border-slate-200 dark:border-dark-border text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-card hover:border-phoenix-300 transition-colors"
                                aria-label="@lang('phonix::app.listing.pagination.previous')"
                                rel="prev"
                            >
                                <svg class="w-[15px] h-[15px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </a>
                        @endif

                        {{-- Page numbers --}}
                        @php
                            $currentPage = $products->currentPage();
                            $lastPage    = $products->lastPage();
                        @endphp

                        @for ($p = 1; $p <= $lastPage; $p++)
                            @if ($p === 1 || $p === $lastPage || abs($p - $currentPage) <= 1)
                                @if ($p === $currentPage)
                                    <span
                                        class="flex items-center justify-center min-w-[36px] h-[36px] px-[8px] rounded-lg text-sm font-semibold bg-phoenix-500 text-white shadow-sm"
                                        aria-current="page"
                                        aria-label="@lang('phonix::app.listing.pagination.page', ['page' => $p])"
                                    >
                                        {{ $p }}
                                    </span>
                                @else
                                    <a
                                        href="{{ $products->appends(request()->query())->url($p) }}"
                                        class="flex items-center justify-center min-w-[36px] h-[36px] px-[8px] rounded-lg border border-slate-200 dark:border-dark-border text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-card hover:border-phoenix-300 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors"
                                        aria-label="@lang('phonix::app.listing.pagination.page', ['page' => $p])"
                                    >
                                        {{ $p }}
                                    </a>
                                @endif
                            @elseif (
                                ($p === 2 && $currentPage > 3) ||
                                ($p === $lastPage - 1 && $currentPage < $lastPage - 2)
                            )
                                <span class="flex items-center justify-center w-[36px] h-[36px] text-sm text-slate-400 dark:text-slate-500" aria-hidden="true">
                                    &hellip;
                                </span>
                            @endif
                        @endfor

                        {{-- Next --}}
                        @if ($products->hasMorePages())
                            <a
                                href="{{ $products->appends(request()->query())->nextPageUrl() }}"
                                class="flex items-center justify-center w-[36px] h-[36px] rounded-lg border border-slate-200 dark:border-dark-border text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-card hover:border-phoenix-300 transition-colors"
                                aria-label="@lang('phonix::app.listing.pagination.next')"
                                rel="next"
                            >
                                <svg class="w-[15px] h-[15px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>
                        @else
                            <span
                                class="flex items-center justify-center w-[36px] h-[36px] rounded-lg border border-slate-200 dark:border-dark-border text-slate-300 dark:text-slate-600 cursor-not-allowed"
                                aria-disabled="true"
                                aria-label="@lang('phonix::app.listing.pagination.next')"
                            >
                                <svg class="w-[15px] h-[15px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        @endif
                    </div>
                </nav>
            @endif

        </div>{{-- end .flex-1 product area --}}
    </div>{{-- end .flex sidebar+main --}}

</div>{{-- end container --}}

</x-phonix::layouts.index>
