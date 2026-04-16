@php
    $product = [
        'name' => 'iPhone 15 Pro Max',
        'brand' => 'Apple',
        'sku' => 'APL-IP15PM-256-BLK',
        'category' => 'Smartphones',
        'price' => '$1,199',
        'original_price' => '$1,399',
        'discount_percent' => 14,
        'rating' => 4.8,
        'reviews_count' => 234,
        'short_description' => 'The most powerful iPhone ever. Featuring the A17 Pro chip, a 48MP camera system, titanium design, and the longest battery life ever in an iPhone. Available in Natural Titanium.',
        'in_stock' => true,
        'badge' => 'hot',
        'slug' => $slug ?? 'iphone-15-pro-max',
    ];

    $galleryImages = [
        ['color' => 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)'],
        ['color' => 'linear-gradient(135deg, #0f3460 0%, #533483 100%)'],
        ['color' => 'linear-gradient(135deg, #2d3436 0%, #636e72 100%)'],
        ['color' => 'linear-gradient(135deg, #c0c0c0 0%, #a0a0a0 100%)'],
        ['color' => 'linear-gradient(135deg, #ffeaa7 0%, #dfe6e9 100%)'],
    ];

    $colorVariants = [
        ['name' => 'Natural Titanium', 'hex' => '#8A8683'],
        ['name' => 'Blue Titanium', 'hex' => '#3B4D5C'],
        ['name' => 'White Titanium', 'hex' => '#E3E1DB'],
        ['name' => 'Black Titanium', 'hex' => '#3C3C3D'],
    ];

    $storageVariants = [
        ['label' => '256GB', 'price' => '$1,199'],
        ['label' => '512GB', 'price' => '$1,399'],
        ['label' => '1TB', 'price' => '$1,599'],
    ];

    $specifications = [
        ['label' => __('phonix::app.product.brand'), 'value' => 'Apple'],
        ['label' => __('phonix::app.product.model'), 'value' => 'iPhone 15 Pro Max'],
        ['label' => __('phonix::app.product.processor'), 'value' => 'A17 Pro (3nm)'],
        ['label' => __('phonix::app.product.ram'), 'value' => '8GB'],
        ['label' => __('phonix::app.product.storage'), 'value' => '256GB / 512GB / 1TB'],
        ['label' => __('phonix::app.product.screen_size'), 'value' => '6.7" Super Retina XDR OLED'],
        ['label' => __('phonix::app.product.battery'), 'value' => '4,441 mAh'],
        ['label' => 'OS', 'value' => 'iOS 17'],
        ['label' => __('phonix::app.product.color'), 'value' => 'Natural Titanium, Blue Titanium, White Titanium, Black Titanium'],
        ['label' => 'Weight', 'value' => '221g'],
    ];

    $productDescription = '
        <h3>The Ultimate iPhone Experience</h3>
        <p>iPhone 15 Pro Max is the most powerful iPhone ever made, forged in titanium with the groundbreaking A17 Pro chip. It features a 48MP Main camera with a new 5x Telephoto camera for incredible zoom capabilities.</p>

        <h4>Titanium Design</h4>
        <p>iPhone 15 Pro Max features a strong and light aerospace-grade titanium design with a textured matte glass back. It also features a Ceramic Shield front that\'s tougher than any smartphone glass. And it\'s splash, water, and dust resistant.</p>

        <h4>A17 Pro Chip</h4>
        <p>A17 Pro is an entirely new class of iPhone chip that delivers the best graphics performance by far of any chip ever in iPhone. Mobile games will look and feel so immersive, with incredibly detailed environments and realistic characters.</p>

        <h4>Pro Camera System</h4>
        <p>Get incredible framing flexibility with 7 pro lenses. Capture super-high-resolution photos with more color and detail using the 48MP Main camera. And now you can take 48MP photos in HEIF with up to 4x the resolution.</p>
    ';

    $reviews = [
        [
            'name' => 'Ahmed Al-Rashid',
            'date' => 'March 15, 2026',
            'rating' => 5,
            'text' => 'Absolutely amazing phone! The camera quality is outstanding and the titanium design feels incredibly premium. Battery life lasts me a full day of heavy usage. Best iPhone yet.',
        ],
        [
            'name' => 'Sarah Johnson',
            'date' => 'February 28, 2026',
            'rating' => 5,
            'text' => 'The A17 Pro chip makes everything so smooth and fast. The 5x zoom camera is a game-changer for photography. Worth every penny for the upgrade from iPhone 14 Pro.',
        ],
        [
            'name' => 'Mohammed Al-Faisal',
            'date' => 'January 10, 2026',
            'rating' => 4,
            'text' => 'Great phone overall. The titanium build is lighter than expected. Only downside is the price, but if you can afford it, it\'s the best smartphone on the market right now.',
        ],
        [
            'name' => 'Lisa Chen',
            'date' => 'December 22, 2025',
            'rating' => 5,
            'text' => 'I\'ve been an Android user for years and this phone finally convinced me to switch. The ecosystem integration is seamless and the camera quality blows my old phone away.',
        ],
    ];

    $ratingBreakdown = [5 => 156, 4 => 52, 3 => 18, 2 => 5, 1 => 3];

    $relatedProducts = [
        ['name' => 'Samsung Galaxy S24 Ultra', 'brand' => 'Samsung', 'price' => '$1,099', 'original_price' => '$1,299', 'rating' => 5, 'reviews' => 189, 'badge' => 'new', 'slug' => 'samsung-galaxy-s24-ultra', 'color' => 'linear-gradient(135deg, #0f3460 0%, #533483 100%)'],
        ['name' => 'iPhone 15 Pro', 'brand' => 'Apple', 'price' => '$999', 'original_price' => null, 'rating' => 5, 'reviews' => 178, 'badge' => null, 'slug' => 'iphone-15-pro', 'color' => 'linear-gradient(135deg, #2d3436 0%, #636e72 100%)'],
        ['name' => 'Xiaomi 14 Ultra', 'brand' => 'Xiaomi', 'price' => '$899', 'original_price' => '$999', 'rating' => 4, 'reviews' => 145, 'badge' => 'sale', 'slug' => 'xiaomi-14-ultra', 'color' => 'linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%)'],
        ['name' => 'AirPods Pro 2nd Gen', 'brand' => 'Apple', 'price' => '$229', 'original_price' => '$249', 'rating' => 5, 'reviews' => 428, 'badge' => 'hot', 'slug' => 'airpods-pro-2', 'color' => 'linear-gradient(135deg, #ffeaa7 0%, #dfe6e9 100%)'],
    ];
@endphp

<x-phonix::layouts.index :title="$product['name'] . ' - Phonix'">

    <div class="container mx-auto section-padding">
        {{-- Breadcrumb --}}
        <x-phonix::breadcrumb :items="[
            ['label' => __('phonix::app.general.home'), 'url' => '/'],
            ['label' => __('phonix::app.general.shop'), 'url' => route('phonix.products.index')],
            ['label' => $product['category'], 'url' => route('phonix.products.index')],
            ['label' => $product['name']],
        ]" />

        {{-- Product Main Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-[32px] lg:gap-[48px] mb-[48px] lg:mb-[64px]">
            {{-- Gallery --}}
            <x-phonix::product-gallery
                :images="$galleryImages"
                :badge="$product['badge']"
                :productName="$product['name']"
                data-gsap="fade-in"
            />

            {{-- Product Info --}}
            <div data-gsap="fade-up">
                {{-- Brand --}}
                <span class="text-xs font-semibold text-phoenix-600 dark:text-phoenix-400 uppercase tracking-wider">
                    {{ $product['brand'] }}
                </span>

                {{-- Name --}}
                <h1 class="text-fluid-xl font-bold text-slate-900 dark:text-white mt-[4px] mb-[12px]">
                    {{ $product['name'] }}
                </h1>

                {{-- Rating --}}
                <div class="flex items-center gap-[8px] mb-[16px]">
                    <div class="flex items-center gap-[2px]">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg
                                class="w-[16px] h-[16px] {{ $i <= round($product['rating']) ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                fill="currentColor" viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $product['rating'] }}</span>
                    <a href="#panel-reviews" class="text-sm text-phoenix-600 dark:text-phoenix-400 hover:underline">
                        @lang('phonix::app.product.reviews_count', ['count' => $product['reviews_count']])
                    </a>
                </div>

                {{-- Price --}}
                <div class="flex items-center gap-[12px] mb-[16px]">
                    <span class="text-fluid-lg font-bold text-phoenix-600 dark:text-phoenix-400">
                        {{ $product['price'] }}
                    </span>
                    @if ($product['original_price'])
                        <span class="text-base text-slate-400 line-through">
                            {{ $product['original_price'] }}
                        </span>
                        <span class="badge-sale">
                            @lang('phonix::app.product.save_percent', ['percent' => $product['discount_percent']])
                        </span>
                    @endif
                </div>

                {{-- Short Description --}}
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-[20px]">
                    {{ $product['short_description'] }}
                </p>

                {{-- Variant Selectors --}}
                <div
                    x-data="{
                        selectedColor: 0,
                        selectedStorage: 0,
                        quantity: 1,
                        colors: {{ json_encode($colorVariants) }},
                        storages: {{ json_encode($storageVariants) }},
                    }"
                >
                    {{-- Color Selector --}}
                    <div class="mb-[20px]">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-[8px]">
                            @lang('phonix::app.product.select_color'):
                            <span class="font-normal text-slate-500 dark:text-slate-400" x-text="colors[selectedColor].name"></span>
                        </label>
                        <div class="flex gap-[8px]">
                            @foreach ($colorVariants as $index => $variant)
                                <button
                                    @click="selectedColor = {{ $index }}"
                                    :class="selectedColor === {{ $index }} ? 'ring-2 ring-phoenix-500 ring-offset-2 dark:ring-offset-dark-bg' : 'ring-1 ring-slate-200 dark:ring-dark-border'"
                                    class="w-[36px] h-[36px] rounded-full transition-all duration-200 hover:scale-110"
                                    style="background-color: {{ $variant['hex'] }}"
                                    :aria-pressed="(selectedColor === {{ $index }}).toString()"
                                    aria-label="{{ $variant['name'] }}"
                                    title="{{ $variant['name'] }}"
                                ></button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Storage Selector --}}
                    <div class="mb-[20px]">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-[8px]">
                            @lang('phonix::app.product.select_storage')
                        </label>
                        <div class="flex flex-wrap gap-[8px]">
                            @foreach ($storageVariants as $index => $variant)
                                <button
                                    @click="selectedStorage = {{ $index }}"
                                    :class="selectedStorage === {{ $index }}
                                        ? 'bg-phoenix-500 text-white border-phoenix-500 shadow-sm'
                                        : 'bg-white dark:bg-dark-card text-slate-600 dark:text-slate-400 border-slate-200 dark:border-dark-border hover:border-phoenix-400'"
                                    class="px-[16px] py-[10px] text-sm font-medium rounded-md border-2 transition-all duration-200"
                                    :aria-pressed="(selectedStorage === {{ $index }}).toString()"
                                >
                                    {{ $variant['label'] }}
                                    <span class="block text-xs mt-[2px] opacity-80">{{ $variant['price'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Stock Status --}}
                    <div class="flex items-center gap-[6px] mb-[20px]">
                        @if ($product['in_stock'])
                            <svg class="w-[16px] h-[16px] text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">
                                @lang('phonix::app.product.in_stock')
                            </span>
                        @else
                            <svg class="w-[16px] h-[16px] text-coral" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium text-coral">
                                @lang('phonix::app.product.out_of_stock')
                            </span>
                        @endif
                    </div>

                    {{-- Quantity Selector --}}
                    <div class="mb-[20px]">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-[8px]">
                            @lang('phonix::app.product.quantity')
                        </label>
                        <div class="inline-flex items-center border border-slate-200 dark:border-dark-border rounded-md overflow-hidden">
                            <button
                                @click="quantity = Math.max(1, quantity - 1)"
                                class="w-[40px] h-[40px] flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-card transition-colors"
                                aria-label="Decrease quantity"
                            >
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                </svg>
                            </button>
                            <input
                                type="number"
                                x-model.number="quantity"
                                min="1"
                                max="10"
                                class="w-[56px] h-[40px] text-center text-sm font-medium border-x border-slate-200 dark:border-dark-border bg-transparent text-slate-800 dark:text-slate-200 focus:outline-none"
                                aria-label="@lang('phonix::app.product.quantity')"
                            />
                            <button
                                @click="quantity = Math.min(10, quantity + 1)"
                                class="w-[40px] h-[40px] flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-card transition-colors"
                                aria-label="Increase quantity"
                            >
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-[12px] mb-[24px]">
                        <button class="btn-phoenix flex-1 px-[24px] py-[14px] text-sm">
                            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            @lang('phonix::app.product.add_to_cart')
                        </button>
                        <button class="btn-phoenix-outline px-[24px] py-[14px] text-sm">
                            @lang('phonix::app.product.buy_now')
                        </button>
                        <button
                            class="flex items-center justify-center w-[48px] h-[48px] border-2 border-slate-200 dark:border-dark-border rounded-md text-slate-500 dark:text-slate-400 hover:border-coral hover:text-coral transition-colors shrink-0"
                            aria-label="@lang('phonix::app.product.add_to_wishlist')"
                        >
                            <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        </button>
                        <button
                            class="flex items-center justify-center w-[48px] h-[48px] border-2 border-slate-200 dark:border-dark-border rounded-md text-slate-500 dark:text-slate-400 hover:border-phoenix-400 hover:text-phoenix-500 transition-colors shrink-0"
                            aria-label="@lang('phonix::app.product.share')"
                        >
                            <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Product Meta --}}
                <div class="space-y-[6px] text-xs text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-dark-border pt-[16px] mb-[20px]">
                    <p><span class="font-semibold text-slate-600 dark:text-slate-300">@lang('phonix::app.product.sku'):</span> {{ $product['sku'] }}</p>
                    <p><span class="font-semibold text-slate-600 dark:text-slate-300">@lang('phonix::app.product.brand'):</span> {{ $product['brand'] }}</p>
                    <p><span class="font-semibold text-slate-600 dark:text-slate-300">@lang('phonix::app.listing.filters.category'):</span> {{ $product['category'] }}</p>
                </div>

                {{-- Trust Badges --}}
                <div class="grid grid-cols-3 gap-[12px]">
                    <div class="flex flex-col items-center text-center p-[12px] rounded-md bg-slate-50 dark:bg-dark-surface">
                        <svg class="w-[24px] h-[24px] text-phoenix-500 mb-[4px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">@lang('phonix::app.product.free_shipping')</span>
                    </div>
                    <div class="flex flex-col items-center text-center p-[12px] rounded-md bg-slate-50 dark:bg-dark-surface">
                        <svg class="w-[24px] h-[24px] text-phoenix-500 mb-[4px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">@lang('phonix::app.features.warranty.title')</span>
                    </div>
                    <div class="flex flex-col items-center text-center p-[12px] rounded-md bg-slate-50 dark:bg-dark-surface">
                        <svg class="w-[24px] h-[24px] text-phoenix-500 mb-[4px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                        </svg>
                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">@lang('phonix::app.features.money_back.title')</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Product Tabs --}}
        <x-phonix::product-tabs
            :description="$productDescription"
            :specifications="$specifications"
            :reviews="$reviews"
            :averageRating="$product['rating']"
            :totalReviews="$product['reviews_count']"
            :ratingBreakdown="$ratingBreakdown"
            class="mb-[48px] lg:mb-[64px]"
            data-gsap="fade-up"
        />

        {{-- Related Products --}}
        <section data-gsap="fade-up">
            <x-phonix::section-heading
                :title="__('phonix::app.product.related_products')"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-[16px]" data-gsap="stagger">
                @foreach ($relatedProducts as $related)
                    <x-phonix::product-card
                        :name="$related['name']"
                        :price="$related['price']"
                        :originalPrice="$related['original_price']"
                        :rating="$related['rating']"
                        :reviewsCount="$related['reviews']"
                        :badge="$related['badge']"
                        :url="route('phonix.products.view', ['slug' => $related['slug']])"
                    />
                @endforeach
            </div>
        </section>
    </div>

</x-phonix::layouts.index>
