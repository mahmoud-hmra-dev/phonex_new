{{-- My Wishlist --}}
@php
    $customer = auth()->guard('customer')->user();

    $wishlistProducts = $customer
        ? $customer->wishlist_items()
            ->with('product')
            ->get()
            ->filter(fn ($wishlist) => $wishlist->product !== null)
            ->map(function ($wishlist) {
                $product = $wishlist->product;
                $imageData = product_image()->getProductBaseImage($product);
                $prices = $product->getTypeInstance()->getProductPrices();

                $finalPrice   = $prices['final']['price'] ?? ($prices['regular']['price'] ?? 0);
                $regularPrice = $prices['regular']['price'] ?? null;
                $onSale       = $regularPrice !== null && $finalPrice < $regularPrice;

                $badge = null;
                if ($product->new) {
                    $badge = 'new';
                } elseif ($onSale) {
                    $badge = 'sale';
                } elseif ($product->featured) {
                    $badge = 'bestseller';
                }

                return [
                    'id'            => $wishlist->id,
                    'name'          => $product->name,
                    'url'           => route('phonix.products.view', $product->url_key),
                    'image'         => $imageData['small_image_url'] ?? null,
                    'price'         => core()->currency($finalPrice),
                    'originalPrice' => $onSale ? core()->currency($regularPrice) : null,
                    'rating'        => 0,
                    'reviewsCount'  => $product->reviews->count() ?? 0,
                    'badge'         => $badge,
                ];
            })
            ->values()
            ->all()
        : [];
@endphp

<x-phonix::account.layout
    :title="__('phonix::app.account.wishlist.title')"
    :breadcrumbs="[['label' => __('phonix::app.account.wishlist.title')]]"
>
    <div
        class="space-y-[24px]"
        x-data="{
            items: {{ Js::from($wishlistProducts) }},
            csrfToken: '{{ csrf_token() }}',
            removing: null,
            movingToCart: null,

            removeItem(index) {
                const item = this.items[index];
                this.removing = item.id;
                fetch('/api/customer/wishlist/' + item.id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.data !== undefined || data.message) {
                        this.items.splice(index, 1);
                    }
                })
                .catch(() => this.items.splice(index, 1))
                .finally(() => this.removing = null);
            },

            moveToCart(index) {
                const item = this.items[index];
                this.movingToCart = item.id;
                fetch('/api/customer/wishlist/' + item.id + '/move-to-cart', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.data !== undefined || data.message) {
                        this.items.splice(index, 1);
                        window.location.href = '{{ route('phonix.cart.index') }}';
                    }
                })
                .catch(console.error)
                .finally(() => this.movingToCart = null);
            }
        }"
    >
        {{-- Page Title --}}
        <h1 class="text-fluid-xl font-bold text-slate-800 dark:text-slate-100" data-gsap="fade-up">
            @lang('phonix::app.account.wishlist.title')
        </h1>

        {{-- Products Grid --}}
        <template x-if="items.length > 0">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[16px]" data-gsap="fade-up">
                <template x-for="(product, index) in items" :key="index">
                    <div class="card-phoenix group overflow-hidden" data-gsap="fade-up">
                        {{-- Image --}}
                        <div class="relative overflow-hidden aspect-square bg-slate-50 dark:bg-dark-surface">
                            <a :href="product.url" class="block w-full h-full">
                                <template x-if="product.image">
                                    <img :src="product.image" :alt="product.name" class="w-full h-full object-cover" loading="lazy" />
                                </template>
                                <template x-if="!product.image">
                                    <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                        <svg class="w-[48px] h-[48px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                        </svg>
                                    </div>
                                </template>
                            </a>

                            {{-- Remove from Wishlist --}}
                            <button
                                @click="removeItem(index)"
                                :disabled="removing === product.id"
                                class="absolute top-[8px] end-[8px] z-10 w-[32px] h-[32px] flex items-center justify-center rounded-full bg-white/90 dark:bg-dark-card/90 text-red-500 hover:bg-red-500 hover:text-white transition-all duration-200 shadow-sm disabled:opacity-50"
                                :aria-label="'@lang('phonix::app.account.wishlist.remove')'"
                            >
                                <svg x-show="removing !== product.id" class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <svg x-show="removing === product.id" x-cloak class="w-[16px] h-[16px] animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                            </button>

                            {{-- Badge --}}
                            <template x-if="product.badge">
                                <div class="absolute top-[8px] start-[8px] z-10">
                                    <span
                                        class="inline-flex items-center px-[10px] py-[4px] text-xs font-bold uppercase tracking-wider rounded-sm text-white"
                                        :class="{
                                            'bg-[var(--coral)]': product.badge === 'sale',
                                            'bg-[var(--phoenix-500)]': product.badge === 'new',
                                            'bg-[var(--gold)] !text-slate-900': product.badge === 'hot' || product.badge === 'bestseller',
                                        }"
                                        x-text="product.badge"
                                    ></span>
                                </div>
                            </template>
                        </div>

                        {{-- Product Info --}}
                        <div class="p-[16px]">
                            <a :href="product.url" class="block text-sm font-medium text-slate-800 dark:text-slate-200 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors line-clamp-2 mb-[8px]" x-text="product.name"></a>

                            {{-- Rating --}}
                            <div class="flex items-center gap-[4px] mb-[8px]">
                                <template x-for="i in 5" :key="i">
                                    <svg
                                        class="w-[14px] h-[14px]"
                                        :class="i <= product.rating ? 'text-gold' : 'text-slate-300 dark:text-slate-600'"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </template>
                                <span class="text-xs text-slate-400 ms-[4px]" x-text="'(' + product.reviewsCount + ')'"></span>
                            </div>

                            {{-- Price --}}
                            <div class="flex items-center gap-[8px] mb-[12px]">
                                <span class="text-base font-bold text-phoenix-600 dark:text-phoenix-400" x-text="product.price"></span>
                                <template x-if="product.originalPrice">
                                    <span class="text-sm text-slate-400 line-through" x-text="product.originalPrice"></span>
                                </template>
                            </div>

                            {{-- Move to Cart --}}
                            <button
                                @click="moveToCart(index)"
                                :disabled="movingToCart === product.id"
                                class="btn-phoenix w-full text-sm py-[10px] disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg x-show="movingToCart !== product.id" class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                                </svg>
                                <svg x-show="movingToCart === product.id" x-cloak class="w-[16px] h-[16px] animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                @lang('phonix::app.account.wishlist.move_to_cart')
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Empty State --}}
        <template x-if="items.length === 0">
            <div class="card-phoenix py-[80px] text-center" data-gsap="fade-up">
                <svg class="w-[80px] h-[80px] mx-auto text-slate-300 dark:text-slate-600 mb-[24px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-[8px]">
                    @lang('phonix::app.account.wishlist.empty')
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-[24px] max-w-sm mx-auto">
                    @lang('phonix::app.account.wishlist.empty_message')
                </p>
                <a href="{{ route('phonix.products.index') }}" class="btn-phoenix">
                    @lang('phonix::app.account.wishlist.explore_products')
                </a>
            </div>
        </template>
    </div>
</x-phonix::account.layout>
