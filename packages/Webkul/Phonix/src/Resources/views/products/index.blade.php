@php
    $categoryName = 'Smartphones';
    $categoryDescription = 'Discover the latest flagship smartphones from top brands. Find the perfect device with cutting-edge cameras, powerful processors, and stunning displays.';
    $totalResults = 156;
    $showingFrom = 1;
    $showingTo = 24;

    $products = [
        ['name' => 'iPhone 15 Pro Max', 'brand' => 'Apple', 'price' => '$1,199', 'original_price' => '$1,399', 'rating' => 5, 'reviews' => 234, 'badge' => 'hot', 'slug' => 'iphone-15-pro-max', 'color' => 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)'],
        ['name' => 'Samsung Galaxy S24 Ultra', 'brand' => 'Samsung', 'price' => '$1,099', 'original_price' => '$1,299', 'rating' => 5, 'reviews' => 189, 'badge' => 'new', 'slug' => 'samsung-galaxy-s24-ultra', 'color' => 'linear-gradient(135deg, #0f3460 0%, #533483 100%)'],
        ['name' => 'MacBook Pro 16"', 'brand' => 'Apple', 'price' => '$2,499', 'original_price' => null, 'rating' => 5, 'reviews' => 156, 'badge' => null, 'slug' => 'macbook-pro-16', 'color' => 'linear-gradient(135deg, #c0c0c0 0%, #a0a0a0 100%)'],
        ['name' => 'Sony WH-1000XM5', 'brand' => 'Sony', 'price' => '$298', 'original_price' => '$399', 'rating' => 4, 'reviews' => 312, 'badge' => 'sale', 'slug' => 'sony-wh-1000xm5', 'color' => 'linear-gradient(135deg, #2d3436 0%, #636e72 100%)'],
        ['name' => 'Dell XPS 15', 'brand' => 'Dell', 'price' => '$1,799', 'original_price' => '$1,999', 'rating' => 4, 'reviews' => 98, 'badge' => null, 'slug' => 'dell-xps-15', 'color' => 'linear-gradient(135deg, #dfe6e9 0%, #b2bec3 100%)'],
        ['name' => 'iPad Pro 12.9"', 'brand' => 'Apple', 'price' => '$1,099', 'original_price' => null, 'rating' => 5, 'reviews' => 201, 'badge' => 'new', 'slug' => 'ipad-pro-12-9', 'color' => 'linear-gradient(135deg, #2d3436 0%, #000000 100%)'],
        ['name' => 'Xiaomi 14 Ultra', 'brand' => 'Xiaomi', 'price' => '$899', 'original_price' => '$999', 'rating' => 4, 'reviews' => 145, 'badge' => 'sale', 'slug' => 'xiaomi-14-ultra', 'color' => 'linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%)'],
        ['name' => 'ASUS ROG Strix G16', 'brand' => 'Asus', 'price' => '$1,599', 'original_price' => null, 'rating' => 4, 'reviews' => 87, 'badge' => 'hot', 'slug' => 'asus-rog-strix-g16', 'color' => 'linear-gradient(135deg, #d63031 0%, #e17055 100%)'],
        ['name' => 'Samsung Galaxy Watch 6', 'brand' => 'Samsung', 'price' => '$299', 'original_price' => '$349', 'rating' => 4, 'reviews' => 176, 'badge' => 'sale', 'slug' => 'samsung-galaxy-watch-6', 'color' => 'linear-gradient(135deg, #0984e3 0%, #74b9ff 100%)'],
        ['name' => 'HP Spectre x360', 'brand' => 'HP', 'price' => '$1,449', 'original_price' => '$1,599', 'rating' => 4, 'reviews' => 64, 'badge' => null, 'slug' => 'hp-spectre-x360', 'color' => 'linear-gradient(135deg, #2d3436 0%, #636e72 100%)'],
        ['name' => 'Lenovo ThinkPad X1 Carbon', 'brand' => 'Lenovo', 'price' => '$1,649', 'original_price' => null, 'rating' => 5, 'reviews' => 112, 'badge' => null, 'slug' => 'lenovo-thinkpad-x1', 'color' => 'linear-gradient(135deg, #1e272e 0%, #485460 100%)'],
        ['name' => 'AirPods Pro 2nd Gen', 'brand' => 'Apple', 'price' => '$229', 'original_price' => '$249', 'rating' => 5, 'reviews' => 428, 'badge' => 'hot', 'slug' => 'airpods-pro-2', 'color' => 'linear-gradient(135deg, #ffeaa7 0%, #dfe6e9 100%)'],
    ];
@endphp

<x-phonix::layouts.index :title="@lang('phonix::app.listing.title')">

    <div
        x-data="{
            viewMode: 'grid',
            sortBy: 'newest',
            filtersOpen: false,
        }"
        class="container mx-auto section-padding"
    >
        {{-- Breadcrumb --}}
        <x-phonix::breadcrumb :items="[
            ['label' => __('phonix::app.general.home'), 'url' => '/'],
            ['label' => __('phonix::app.general.shop'), 'url' => '/phonix/products'],
            ['label' => $categoryName],
        ]" />

        {{-- Page Header --}}
        <div class="mb-[24px]" data-gsap="fade-up">
            <h1 class="text-fluid-2xl font-bold text-slate-900 dark:text-white mb-[8px]">
                {{ $categoryName }}
            </h1>
            @if ($categoryDescription)
                <p class="text-fluid-sm text-slate-500 dark:text-slate-400 max-w-[640px]">
                    {{ $categoryDescription }}
                </p>
            @endif
        </div>

        {{-- Toolbar --}}
        <div class="flex flex-wrap items-center justify-between gap-[12px] mb-[24px] pb-[16px] border-b border-slate-200 dark:border-dark-border" data-gsap="fade-in">
            {{-- Left: Results count + Mobile filter btn --}}
            <div class="flex items-center gap-[12px]">
                <x-phonix::filters-sidebar />

                <p class="text-sm text-slate-500 dark:text-slate-400">
                    @lang('phonix::app.listing.showing') <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $showingFrom }}-{{ $showingTo }}</span> @lang('phonix::app.listing.of') <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $totalResults }}</span> @lang('phonix::app.listing.results_count', ['count' => ''])
                </p>
            </div>

            {{-- Right: Sort + View Toggle --}}
            <div class="flex items-center gap-[12px]">
                {{-- Sort Dropdown --}}
                <div x-data="{ sortOpen: false }" class="relative">
                    <button
                        @click="sortOpen = !sortOpen"
                        @click.outside="sortOpen = false"
                        class="flex items-center gap-[6px] px-[12px] py-[8px] text-xs font-medium border border-slate-200 dark:border-dark-border rounded bg-white dark:bg-dark-card text-slate-600 dark:text-slate-400 hover:border-phoenix-400 transition-colors"
                        aria-haspopup="listbox"
                        :aria-expanded="sortOpen.toString()"
                    >
                        @lang('phonix::app.listing.sort.title')
                        <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div
                        x-show="sortOpen"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute end-0 top-full mt-[4px] w-[200px] bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border rounded-md shadow-lg z-20 py-[4px]"
                        role="listbox"
                        x-cloak
                    >
                        @php
                            $sortOptions = [
                                'newest' => 'phonix::app.listing.sort.newest',
                                'price_low' => 'phonix::app.listing.sort.price_low',
                                'price_high' => 'phonix::app.listing.sort.price_high',
                                'popular' => 'phonix::app.listing.sort.popular',
                                'rating' => 'phonix::app.listing.sort.rating',
                                'name_asc' => 'phonix::app.listing.sort.name_asc',
                                'name_desc' => 'phonix::app.listing.sort.name_desc',
                            ];
                        @endphp
                        @foreach ($sortOptions as $value => $label)
                            <button
                                @click="sortBy = '{{ $value }}'; sortOpen = false"
                                :class="sortBy === '{{ $value }}' ? 'bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-600 dark:text-phoenix-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-surface'"
                                class="w-full text-start px-[12px] py-[8px] text-xs transition-colors"
                                role="option"
                                :aria-selected="(sortBy === '{{ $value }}').toString()"
                            >
                                @lang($label)
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- View Toggle --}}
                <div class="hidden sm:flex items-center border border-slate-200 dark:border-dark-border rounded overflow-hidden">
                    <button
                        @click="viewMode = 'grid'"
                        :class="viewMode === 'grid' ? 'bg-phoenix-500 text-white' : 'bg-white dark:bg-dark-card text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-surface'"
                        class="p-[8px] transition-colors"
                        aria-label="@lang('phonix::app.listing.view.grid')"
                        :aria-pressed="(viewMode === 'grid').toString()"
                    >
                        <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    </button>
                    <button
                        @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-phoenix-500 text-white' : 'bg-white dark:bg-dark-card text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-surface'"
                        class="p-[8px] transition-colors"
                        aria-label="@lang('phonix::app.listing.view.list')"
                        :aria-pressed="(viewMode === 'list').toString()"
                    >
                        <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Content: Sidebar + Products --}}
        <div class="flex gap-[24px]">
            {{-- Desktop Sidebar --}}
            <div class="hidden lg:block w-[260px] shrink-0">
                <x-phonix::filters-sidebar />
            </div>

            {{-- Product Grid / List --}}
            <div class="flex-1">
                {{-- Grid View --}}
                <div
                    x-show="viewMode === 'grid'"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-[16px]"
                    data-gsap="stagger"
                >
                    @foreach ($products as $product)
                        <x-phonix::product-card
                            :name="$product['name']"
                            :price="$product['price']"
                            :originalPrice="$product['original_price']"
                            :rating="$product['rating']"
                            :reviewsCount="$product['reviews']"
                            :badge="$product['badge']"
                            :url="route('phonix.products.view', ['slug' => $product['slug']])"
                        />
                    @endforeach
                </div>

                {{-- List View --}}
                <div
                    x-show="viewMode === 'list'"
                    class="space-y-[16px]"
                    data-gsap="stagger"
                    x-cloak
                >
                    @foreach ($products as $product)
                        <div class="card-phoenix flex flex-col sm:flex-row overflow-hidden" data-gsap="fade-up">
                            {{-- Image --}}
                            <a
                                href="{{ route('phonix.products.view', ['slug' => $product['slug']]) }}"
                                class="relative sm:w-[220px] shrink-0 aspect-square sm:aspect-auto bg-slate-50 dark:bg-dark-surface overflow-hidden group"
                            >
                                <div
                                    class="w-full h-full min-h-[180px] flex items-center justify-center transition-transform duration-500 group-hover:scale-105"
                                    style="background: {{ $product['color'] }}"
                                >
                                    <svg class="w-[48px] h-[48px] text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                    </svg>
                                </div>
                                @if ($product['badge'])
                                    <div class="absolute top-[8px] start-[8px]">
                                        <x-phonix::badge :type="$product['badge']">
                                            @lang('phonix::app.product.' . $product['badge'])
                                        </x-phonix::badge>
                                    </div>
                                @endif
                            </a>

                            {{-- Details --}}
                            <div class="flex-1 p-[16px] sm:p-[20px] flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between mb-[8px]">
                                        <div>
                                            <span class="text-xs text-phoenix-600 dark:text-phoenix-400 font-medium">
                                                {{ $product['brand'] }}
                                            </span>
                                            <a
                                                href="{{ route('phonix.products.view', ['slug' => $product['slug']]) }}"
                                                class="block text-base font-semibold text-slate-800 dark:text-slate-200 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors mt-[2px]"
                                            >
                                                {{ $product['name'] }}
                                            </a>
                                        </div>
                                        <button
                                            class="p-[8px] text-slate-400 hover:text-coral transition-colors shrink-0"
                                            aria-label="@lang('phonix::app.product.add_to_wishlist')"
                                        >
                                            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Rating --}}
                                    <div class="flex items-center gap-[4px] mb-[8px]">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg
                                                class="w-[14px] h-[14px] {{ $i <= $product['rating'] ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                                fill="currentColor" viewBox="0 0 20 20"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                        <span class="text-xs text-slate-400 ms-[4px]">
                                            ({{ $product['reviews'] }} @lang('phonix::app.product.reviews'))
                                        </span>
                                    </div>

                                    <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mb-[12px]">
                                        Experience premium performance with the {{ $product['name'] }}. Featuring cutting-edge technology, stunning design, and exceptional build quality.
                                    </p>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-[8px]">
                                        <span class="text-lg font-bold text-phoenix-600 dark:text-phoenix-400">
                                            {{ $product['price'] }}
                                        </span>
                                        @if ($product['original_price'])
                                            <span class="text-sm text-slate-400 line-through">
                                                {{ $product['original_price'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <x-phonix::button variant="primary" size="sm">
                                        <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        @lang('phonix::app.product.add_to_cart')
                                    </x-phonix::button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <x-phonix::pagination
                    :currentPage="1"
                    :totalPages="7"
                    :from="$showingFrom"
                    :to="$showingTo"
                    :total="$totalResults"
                />
            </div>
        </div>
    </div>

</x-phonix::layouts.index>
