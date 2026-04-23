@inject('reviewHelper', 'Webkul\Product\Helpers\Review')
@inject('productViewHelper', 'Webkul\Product\Helpers\View')

@php
    $avgRatings = $reviewHelper->getAverageRating($product);
    $percentageRatings = $reviewHelper->getPercentageRating($product);
    $customAttributeValues = $productViewHelper->getAdditionalData($product);
    $attributeData = collect($customAttributeValues)->filter(fn ($item) => ! empty($item['value']));

    $productBaseImage = product_image()->getProductBaseImage($product);
    $galleryImages = product_image()->getGalleryImages($product);

    // Build gallery array for the product-gallery component
    $galleryData = collect($galleryImages)->map(function($image) {
        return [
            'url' => $image['medium_image_url'] ?? $image['original_image_url'] ?? '',
            'color' => null,
        ];
    })->toArray();

    // If no gallery images, use the base image
    if (empty($galleryData)) {
        $galleryData = [
            ['url' => $productBaseImage['medium_image_url'] ?? '', 'color' => null],
        ];
    }

    $hasSpecialPrice = $product->getTypeInstance()->haveDiscount();
    $specialPrice = $hasSpecialPrice ? $product->getTypeInstance()->getMinimalPrice() : null;

    // Get reviews
    $reviews = $product->reviews()->where('status', 'approved')->latest()->get();

    // Rating breakdown
    $ratingBreakdown = [];
    for ($i = 5; $i >= 1; $i--) {
        $ratingBreakdown[$i] = $reviews->where('rating', $i)->count();
    }

    // Get related products
    $relatedProducts = collect();
    if ($product->related_products && $product->related_products->count()) {
        $relatedProducts = $product->related_products->take(4);
    } elseif ($product->up_sells && $product->up_sells->count()) {
        $relatedProducts = $product->up_sells->take(4);
    }

    // Fallback: get products from same categories
    if ($relatedProducts->isEmpty()) {
        $categoryIds = $product->categories->pluck('id')->toArray();
        if (!empty($categoryIds)) {
            $relatedProducts = app(\Webkul\Product\Repositories\ProductRepository::class)
                ->scopeQuery(function($query) use ($product, $categoryIds) {
                    return $query->distinct()
                        ->addSelect('products.*')
                        ->leftJoin('product_categories', 'products.id', '=', 'product_categories.product_id')
                        ->whereIn('product_categories.category_id', $categoryIds)
                        ->where('products.id', '!=', $product->id)
                        ->inRandomOrder()
                        ->limit(4);
                })->get();
        }
    }

    // Determine badge
    $badge = null;
    if ($hasSpecialPrice) {
        $badge = 'sale';
    } elseif ($product->new) {
        $badge = 'new';
    }

    // Configurable product variant data
    $isConfigurable = $product->type === 'configurable';
    $variantList    = [];
    $hasRamAttr     = false;
    $hasStorageAttr = false;

    if ($isConfigurable) {
        $ramAttr     = DB::table('attributes')->where('code', 'ram')->first();
        $storageAttr = DB::table('attributes')->where('code', 'storage')->first();

        $superAttrIds = DB::table('product_super_attributes')
            ->where('product_id', $product->id)
            ->pluck('attribute_id');

        $hasRamAttr     = $ramAttr && $superAttrIds->contains($ramAttr->id);
        $hasStorageAttr = $storageAttr && $superAttrIds->contains($storageAttr->id);

        $childProducts = DB::table('products')
            ->where('parent_id', $product->id)
            ->orderBy('id')
            ->get();

        foreach ($childProducts as $child) {
            $priceVal = DB::table('product_attribute_values')
                ->where('product_id', $child->id)
                ->where('attribute_id', 11)
                ->value('float_value') ?? 0;

            $ramLabel     = null;
            $storageLabel = null;

            if ($hasRamAttr) {
                $ramOptId = DB::table('product_attribute_values')
                    ->where('product_id', $child->id)
                    ->where('attribute_id', $ramAttr->id)
                    ->value('integer_value');
                if ($ramOptId) {
                    $ramLabel = DB::table('attribute_option_translations')
                        ->where('attribute_option_id', $ramOptId)
                        ->value('label');
                }
            }

            if ($hasStorageAttr) {
                $storOptId = DB::table('product_attribute_values')
                    ->where('product_id', $child->id)
                    ->where('attribute_id', $storageAttr->id)
                    ->value('integer_value');
                if ($storOptId) {
                    $storageLabel = DB::table('attribute_option_translations')
                        ->where('attribute_option_id', $storOptId)
                        ->value('label');
                }
            }

            $variantList[] = [
                'id'             => $child->id,
                'price'          => $priceVal,
                'priceFormatted' => core()->currency($priceVal),
                'ram'            => $ramLabel,
                'storage'        => $storageLabel,
            ];
        }

        // Unique option sets for chip rendering
        $ramOptions     = collect($variantList)->pluck('ram')->filter()->unique()->values()->toArray();
        $storageOptions = collect($variantList)->pluck('storage')->filter()->unique()->values()->toArray();
    }
@endphp

@push('meta')
    <meta name="description" content="{{ trim($product->meta_description) != '' ? $product->meta_description : \Illuminate\Support\Str::limit(strip_tags($product->description), 120, '') }}"/>
    <meta name="keywords" content="{{ $product->meta_keywords }}"/>

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $product->name }}" />
    <meta name="twitter:description" content="{!! htmlspecialchars(trim(strip_tags($product->description))) !!}" />
    <meta name="twitter:image" content="{{ $productBaseImage['medium_image_url'] }}" />

    <meta property="og:type" content="og:product" />
    <meta property="og:title" content="{{ $product->name }}" />
    <meta property="og:image" content="{{ $productBaseImage['medium_image_url'] }}" />
    <meta property="og:description" content="{!! htmlspecialchars(trim(strip_tags($product->description))) !!}" />
    <meta property="og:url" content="{{ route('phonix.products.view', ['slug' => $product->url_key]) }}" />
@endPush

<x-phonix::layouts.index :title="$product->name . ' - Phonix'">

    <div class="container mx-auto section-padding">
        {{-- Breadcrumb --}}
        @php
            $productCategory = $product->categories->first();
        @endphp
        <x-phonix::breadcrumb :items="array_filter([
            ['label' => __('phonix::app.general.home'), 'url' => route('phonix.home')],
            ['label' => __('phonix::app.general.shop'), 'url' => route('phonix.products.index')],
            $productCategory ? ['label' => $productCategory->name, 'url' => route('phonix.products.index', ['category_ids' => [$productCategory->id]])] : null,
            ['label' => $product->name],
        ])" />

        {{-- Product Main Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-[32px] lg:gap-[48px] mb-[48px] lg:mb-[64px]">
            {{-- Gallery --}}
            <x-phonix::product-gallery
                :images="$galleryData"
                :badge="$badge"
                :productName="$product->name"
                data-gsap="fade-in"
            />

            {{-- Product Info --}}
            <div data-gsap="fade-up">
                {{-- Brand --}}
                @if($product->brand)
                    <span class="text-xs font-semibold text-phoenix-600 dark:text-phoenix-400 uppercase tracking-wider">
                        {{ $product->brand }}
                    </span>
                @endif

                {{-- Name --}}
                <h1 class="text-fluid-xl font-bold text-slate-900 dark:text-white mt-[4px] mb-[12px]">
                    {{ $product->name }}
                </h1>

                {{-- Rating --}}
                @if($reviews->count() > 0)
                    <div class="flex items-center gap-[8px] mb-[16px]">
                        <div class="flex items-center gap-[2px]">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg
                                    class="w-[16px] h-[16px] {{ $i <= round($avgRatings) ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                    fill="currentColor" viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ number_format($avgRatings, 1) }}</span>
                        <a href="#panel-reviews" class="text-sm text-phoenix-600 dark:text-phoenix-400 hover:underline">
                            @lang('phonix::app.product.reviews_count', ['count' => $reviews->count()])
                        </a>
                    </div>
                @endif

                {{-- Price (reactive for configurable products) --}}
                <div class="flex items-center gap-[12px] mb-[16px]">
                    @if($isConfigurable)
                        <span
                            x-text="displayPrice"
                            class="text-fluid-lg font-bold text-phoenix-600 dark:text-phoenix-400"
                        ></span>
                    @elseif($hasSpecialPrice)
                        <span class="text-fluid-lg font-bold text-phoenix-600 dark:text-phoenix-400">
                            {{ core()->currency($specialPrice) }}
                        </span>
                        <span class="text-base text-slate-400 line-through">
                            {{ core()->currency($product->price) }}
                        </span>
                        @php
                            $discountPercent = round((($product->price - $specialPrice) / $product->price) * 100);
                        @endphp
                        <span class="badge-sale">
                            @lang('phonix::app.product.save_percent', ['percent' => $discountPercent])
                        </span>
                    @else
                        <span class="text-fluid-lg font-bold text-phoenix-600 dark:text-phoenix-400">
                            {{ core()->currency($product->price) }}
                        </span>
                    @endif
                </div>

                {{-- Short Description --}}
                @if($product->short_description)
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-[20px]">
                        {{ $product->short_description }}
                    </p>
                @endif

                {{-- Quantity + Actions --}}
                <div
                    x-data="{
                        quantity: 1,
                        csrfToken: document.querySelector('meta[name=csrf-token]').content,
                        cartLoading: false,
                        wishlistLoading: false,
                        inWishlist: false,
                        isConfigurable: {{ $isConfigurable ? 'true' : 'false' }},
                        hasRam: {{ $hasRamAttr ? 'true' : 'false' }},
                        hasStorage: {{ $hasStorageAttr ? 'true' : 'false' }},
                        variants: @json($variantList ?? []),
                        selectedRam: null,
                        selectedStorage: null,
                        selectedVariant: null,
                        displayPrice: '{{ $hasSpecialPrice ? core()->currency($specialPrice) : core()->currency($product->price) }}',
                        selectVariant() {
                            this.selectedVariant = this.variants.find(v =>
                                (!this.hasRam || v.ram === this.selectedRam) &&
                                (!this.hasStorage || v.storage === this.selectedStorage)
                            ) ?? null;
                            if (this.selectedVariant) {
                                this.displayPrice = this.selectedVariant.priceFormatted;
                            }
                        },
                        get computedProductId() {
                            return this.selectedVariant ? this.selectedVariant.id : {{ $product->id }};
                        },
                        get canAddToCart() {
                            return !this.isConfigurable || this.selectedVariant !== null;
                        },
                        async addToCart() {
                            if (this.cartLoading || !this.canAddToCart) return;
                            this.cartLoading = true;
                            try {
                                const res = await fetch('/api/checkout/cart', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': this.csrfToken
                                    },
                                    body: JSON.stringify({ product_id: this.computedProductId, quantity: this.quantity })
                                });
                                if (res.ok) {
                                    window.location.href = '{{ route("phonix.cart.index") }}';
                                }
                            } catch(e) { console.error(e); }
                            finally { this.cartLoading = false; }
                        },
                        async toggleWishlist() {
                            if (this.wishlistLoading) return;
                            this.wishlistLoading = true;
                            try {
                                const res = await fetch(this.inWishlist
                                    ? '/api/customer/wishlist/{{ $product->id }}'
                                    : '/api/customer/wishlist',
                                    {
                                        method: this.inWishlist ? 'DELETE' : 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': this.csrfToken
                                        },
                                        body: this.inWishlist ? null : JSON.stringify({ product_id: {{ $product->id }} })
                                    }
                                );
                                if (res.ok) {
                                    this.inWishlist = !this.inWishlist;
                                } else if (res.status === 401) {
                                    window.location.href = '{{ route("phonix.auth.login") }}';
                                }
                            } catch(e) { console.error(e); }
                            finally { this.wishlistLoading = false; }
                        }
                    }"
                >
                    {{-- Variant Selector (configurable products) --}}
                    @if($isConfigurable)
                        <div class="mb-[20px] space-y-[14px]">

                            @if($hasRamAttr && count($ramOptions ?? []) > 0)
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-[8px]">
                                    RAM
                                    <span x-show="selectedRam" x-text="'— ' + selectedRam" class="text-phoenix-500 font-normal"></span>
                                </label>
                                <div class="flex flex-wrap gap-[8px]">
                                    @foreach($ramOptions as $opt)
                                    <button
                                        type="button"
                                        @click="selectedRam = '{{ $opt }}'; selectVariant()"
                                        :class="selectedRam === '{{ $opt }}'
                                            ? 'bg-phoenix-500 text-white border-phoenix-500 shadow-md'
                                            : 'bg-white dark:bg-dark-card text-slate-700 dark:text-slate-300 border-slate-200 dark:border-dark-border hover:border-phoenix-400'"
                                        class="px-[16px] py-[7px] rounded-lg border-2 text-sm font-semibold transition-all duration-150"
                                    >{{ $opt }}</button>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if($hasStorageAttr && count($storageOptions ?? []) > 0)
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-[8px]">
                                    @lang('phonix::app.product.storage')
                                    <span x-show="selectedStorage" x-text="'— ' + selectedStorage" class="text-phoenix-500 font-normal"></span>
                                </label>
                                <div class="flex flex-wrap gap-[8px]">
                                    @foreach($storageOptions as $opt)
                                    <button
                                        type="button"
                                        @click="selectedStorage = '{{ $opt }}'; selectVariant()"
                                        :class="selectedStorage === '{{ $opt }}'
                                            ? 'bg-phoenix-500 text-white border-phoenix-500 shadow-md'
                                            : 'bg-white dark:bg-dark-card text-slate-700 dark:text-slate-300 border-slate-200 dark:border-dark-border hover:border-phoenix-400'"
                                        class="px-[16px] py-[7px] rounded-lg border-2 text-sm font-semibold transition-all duration-150"
                                    >{{ $opt }}</button>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <p x-show="isConfigurable && !selectedVariant" class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-[6px]">
                                <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                                @lang('phonix::app.product.select_variant')
                            </p>
                        </div>
                    @endif

                    {{-- Stock Status --}}
                    <div class="flex items-center gap-[6px] mb-[20px]">
                        @if ($product->haveSufficientQuantity(1))
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
                        <button class="btn-phoenix flex-1 px-[24px] py-[14px] text-sm flex items-center justify-center gap-[8px] disabled:opacity-50 disabled:cursor-not-allowed"
                            @click="addToCart()"
                            :disabled="cartLoading || !canAddToCart"
                        >
                            <template x-if="cartLoading">
                                <svg class="w-[18px] h-[18px] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </template>
                            <template x-if="!cartLoading">
                                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </template>
                            @lang('phonix::app.product.add_to_cart')
                        </button>
                        <button
                            @click="toggleWishlist()"
                            :disabled="wishlistLoading"
                            :class="{ 'text-red-500 border-red-400 bg-red-50 dark:bg-red-900/20': inWishlist }"
                            class="flex items-center justify-center w-[48px] h-[48px] border-2 border-slate-200 dark:border-dark-border rounded-md text-slate-500 dark:text-slate-400 hover:border-coral hover:text-coral transition-colors shrink-0"
                            aria-label="@lang('phonix::app.product.add_to_wishlist')"
                        >
                            <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        </button>
                        <button
                            @click="navigator.share ? navigator.share({ title: '{{ addslashes($product->name) }}', url: window.location.href }) : null"
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
                    @if($product->sku)
                        <p><span class="font-semibold text-slate-600 dark:text-slate-300">@lang('phonix::app.product.sku'):</span> {{ $product->sku }}</p>
                    @endif
                    @if($productCategory)
                        <p><span class="font-semibold text-slate-600 dark:text-slate-300">@lang('phonix::app.listing.filters.category'):</span> {{ $productCategory->name }}</p>
                    @endif
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
        @php
            // Build specifications from additional attributes
            $specifications = [];
            foreach ($attributeData as $attr) {
                $specifications[] = ['label' => $attr['label'], 'value' => $attr['value']];
            }

            // Build reviews array for the tabs component
            $reviewsForTabs = $reviews->map(function($review) {
                return [
                    'name' => $review->name,
                    'date' => $review->created_at->format('F j, Y'),
                    'rating' => $review->rating,
                    'text' => $review->comment,
                ];
            })->toArray();
        @endphp

        <x-phonix::product-tabs
            :description="$product->description ?? ''"
            :specifications="$specifications"
            :reviews="$reviewsForTabs"
            :averageRating="$avgRatings"
            :totalReviews="$reviews->count()"
            :ratingBreakdown="$ratingBreakdown"
            class="mb-[48px] lg:mb-[64px]"
            data-gsap="fade-up"
        />

        {{-- Related Products --}}
        @if($relatedProducts->isNotEmpty())
            <section data-gsap="fade-up">
                <x-phonix::section-heading
                    :title="__('phonix::app.product.related_products')"
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-[16px]" data-gsap="stagger">
                    @foreach ($relatedProducts as $related)
                        @php
                            $relatedImage = product_image()->getProductBaseImage($related);
                            $relatedHasSpecial = $related->getTypeInstance()->haveDiscount();
                            $relatedAvgRating = $related->reviews->count() > 0 ? round($related->reviews->avg('rating')) : 0;
                        @endphp
                        <x-phonix::product-card
                            :name="$related->name"
                            :price="$relatedHasSpecial ? core()->currency($related->getTypeInstance()->getMinimalPrice()) : core()->currency($related->price)"
                            :originalPrice="$relatedHasSpecial ? core()->currency($related->price) : null"
                            :rating="$relatedAvgRating"
                            :reviewsCount="$related->reviews->count()"
                            :badge="$relatedHasSpecial ? 'sale' : ($related->new ? 'new' : null)"
                            :url="route('phonix.products.view', ['slug' => $related->url_key])"
                            :imageUrl="$relatedImage['medium_image_url']"
                        />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

</x-phonix::layouts.index>
