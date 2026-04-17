@php
    $productRepository = app(\Webkul\Product\Repositories\ProductRepository::class);
    $products = $productRepository->getAll();
@endphp

<x-phonix::layouts.index :title="__('phonix::app.listing.title')">

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
            ['label' => __('phonix::app.general.shop')],
        ]" />

        {{-- Page Header --}}
        <div class="mb-[24px]" data-gsap="fade-up">
            <h1 class="text-fluid-2xl font-bold text-slate-900 dark:text-white mb-[8px]">
                @lang('phonix::app.general.shop')
            </h1>
        </div>

        {{-- Toolbar --}}
        <div class="flex flex-wrap items-center justify-between gap-[12px] mb-[24px] pb-[16px] border-b border-slate-200 dark:border-dark-border" data-gsap="fade-in">
            {{-- Left: Results count --}}
            <div class="flex items-center gap-[12px]">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    @lang('phonix::app.listing.showing')
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $products->count() }}</span>
                    @lang('phonix::app.listing.results_count', ['count' => ''])
                </p>
            </div>

            {{-- Right: View Toggle --}}
            <div class="flex items-center gap-[12px]">
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

        {{-- Product Grid --}}
        <div
            x-show="viewMode === 'grid'"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-[16px]"
            data-gsap="stagger"
        >
            @foreach ($products as $product)
                @php
                    $productImage = product_image()->getProductBaseImage($product);
                    $hasSpecialPrice = $product->getTypeInstance()->haveDiscount();
                    $avgRating = $product->reviews->count() > 0 ? round($product->reviews->avg('rating')) : 0;
                @endphp
                <x-phonix::product-card
                    :name="$product->name"
                    :price="$hasSpecialPrice ? core()->currency($product->getTypeInstance()->getMinimalPrice()) : core()->currency($product->price)"
                    :originalPrice="$hasSpecialPrice ? core()->currency($product->price) : null"
                    :rating="$avgRating"
                    :reviewsCount="$product->reviews->count()"
                    :badge="$hasSpecialPrice ? 'sale' : ($product->new ? 'new' : null)"
                    :url="route('phonix.products.view', ['slug' => $product->url_key])"
                    :imageUrl="$productImage['medium_image_url']"
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
                @php
                    $productImage = product_image()->getProductBaseImage($product);
                    $hasSpecialPrice = $product->getTypeInstance()->haveDiscount();
                    $avgRating = $product->reviews->count() > 0 ? round($product->reviews->avg('rating')) : 0;
                @endphp
                <div class="card-phoenix flex flex-col sm:flex-row overflow-hidden" data-gsap="fade-up">
                    {{-- Image --}}
                    <a
                        href="{{ route('phonix.products.view', ['slug' => $product->url_key]) }}"
                        class="relative sm:w-[220px] shrink-0 aspect-square sm:aspect-auto bg-slate-50 dark:bg-dark-surface overflow-hidden group"
                    >
                        <img
                            src="{{ $productImage['medium_image_url'] }}"
                            alt="{{ $product->name }}"
                            class="w-full h-full min-h-[180px] object-cover transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                    </a>

                    {{-- Details --}}
                    <div class="flex-1 p-[16px] sm:p-[20px] flex flex-col justify-between">
                        <div>
                            <a
                                href="{{ route('phonix.products.view', ['slug' => $product->url_key]) }}"
                                class="block text-base font-semibold text-slate-800 dark:text-slate-200 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors mt-[2px]"
                            >
                                {{ $product->name }}
                            </a>

                            {{-- Rating --}}
                            @if($avgRating > 0)
                                <div class="flex items-center gap-[4px] mb-[8px] mt-[8px]">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg
                                            class="w-[14px] h-[14px] {{ $i <= $avgRating ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                            fill="currentColor" viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    <span class="text-xs text-slate-400 ms-[4px]">
                                        ({{ $product->reviews->count() }} @lang('phonix::app.product.reviews'))
                                    </span>
                                </div>
                            @endif

                            @if($product->short_description)
                                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mb-[12px]">
                                    {{ $product->short_description }}
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-[8px]">
                                @if($hasSpecialPrice)
                                    <span class="text-lg font-bold text-phoenix-600 dark:text-phoenix-400">
                                        {{ core()->currency($product->getTypeInstance()->getMinimalPrice()) }}
                                    </span>
                                    <span class="text-sm text-slate-400 line-through">
                                        {{ core()->currency($product->price) }}
                                    </span>
                                @else
                                    <span class="text-lg font-bold text-phoenix-600 dark:text-phoenix-400">
                                        {{ core()->currency($product->price) }}
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

        @if($products->isEmpty())
            <div class="text-center py-[48px]">
                <p class="text-slate-500 dark:text-slate-400">@lang('phonix::app.general.no_products')</p>
            </div>
        @endif

        {{-- Pagination --}}
        @if(method_exists($products, 'hasPages') && $products->hasPages())
            <div class="mt-[32px]">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</x-phonix::layouts.index>
