@props([
    'product' => null,
    'productId' => null,
    'imageUrl' => null,
    'name' => '',
    'price' => '',
    'originalPrice' => null,
    'rating' => 0,
    'reviewsCount' => 0,
    'badge' => null,
    'url' => '#',
    'wishlistUrl' => '#',
    'addToCartUrl' => '#',
])

<div
    x-data="{
        hovered: false,
        cartLoading: false,
        wishlistLoading: false,
        inWishlist: false,
        productId: {{ $productId ?? 'null' }},
        csrfToken: document.querySelector('meta[name=csrf-token]')?.content ?? '',

        async addToCart() {
            if (!this.productId) { window.location.href = {{ json_encode($url) }}; return; }
            this.cartLoading = true;
            try {
                const res = await fetch('/api/checkout/cart', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ product_id: this.productId, quantity: 1 })
                });
                if (res.ok) { window.location.href = '{{ route('phonix.cart.index') }}'; }
                else { window.location.href = {{ json_encode($url) }}; }
            } catch(e) { window.location.href = {{ json_encode($url) }}; }
            finally { this.cartLoading = false; }
        },

        async toggleWishlist() {
            if (!this.productId) return;
            this.wishlistLoading = true;
            try {
                const method = this.inWishlist ? 'DELETE' : 'POST';
                const url = this.inWishlist
                    ? '/api/customer/wishlist/' + this.productId
                    : '/api/customer/wishlist';
                const body = this.inWishlist ? null : JSON.stringify({ product_id: this.productId });
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body
                });
                if (res.ok) { this.inWishlist = !this.inWishlist; }
            } catch(e) {}
            finally { this.wishlistLoading = false; }
        }
    }"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
    class="card-phoenix group overflow-hidden"
    data-gsap="fade-up"
>
    {{-- Image Container --}}
    <div class="relative overflow-hidden aspect-square bg-slate-50 dark:bg-dark-surface">
        <a href="{{ $url }}" class="block w-full h-full" aria-label="{{ $name }}">
            @if ($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $name }}"
                    class="w-full h-full object-cover transition-transform duration-500 ease-phoenix group-hover:scale-110"
                    loading="lazy"
                />
            @else
                <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                    <svg class="w-[48px] h-[48px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                    </svg>
                </div>
            @endif
        </a>

        {{-- Badge --}}
        @if ($badge)
            <div class="absolute top-[8px] start-[8px] z-10">
                <x-phonix::badge :type="$badge">
                    @lang('phonix::app.product.' . $badge)
                </x-phonix::badge>
            </div>
        @endif

        {{-- Quick Action Overlay --}}
        <div
            x-show="hovered"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="absolute bottom-[12px] inset-x-[12px] flex items-center justify-center gap-[8px] z-10"
            x-cloak
        >
            {{-- Wishlist --}}
            <button
                @click.stop="toggleWishlist()"
                :disabled="wishlistLoading"
                :class="inWishlist ? 'bg-red-500 text-white' : 'bg-white dark:bg-dark-card text-slate-600 dark:text-slate-300 hover:bg-phoenix-500 hover:text-white'"
                class="flex items-center justify-center w-[40px] h-[40px] rounded-md shadow-md transition-colors disabled:opacity-50"
                aria-label="@lang('phonix::app.product.add_to_wishlist')"
            >
                <svg x-show="!wishlistLoading" class="w-[18px] h-[18px]" :fill="inWishlist ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                <svg x-show="wishlistLoading" x-cloak class="w-[16px] h-[16px] animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </button>

            {{-- Compare --}}
            @if($productId)
            <button
                @click.stop="document.dispatchEvent(new CustomEvent('phonix:compare:toggle', { detail: { id: {{ $productId }}, name: {{ json_encode($name) }}, imageUrl: {{ json_encode($imageUrl) }} } }))"
                class="flex items-center justify-center w-[40px] h-[40px] bg-white dark:bg-dark-card rounded-md shadow-md hover:bg-phoenix-500 hover:text-white text-slate-600 dark:text-slate-300 transition-colors"
                aria-label="@lang('phonix::app.product.compare')"
                title="@lang('phonix::app.product.compare')"
            >
                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
            </button>
            @endif

            {{-- Quick View --}}
            <button
                class="flex items-center justify-center w-[40px] h-[40px] bg-white dark:bg-dark-card rounded-md shadow-md hover:bg-phoenix-500 hover:text-white text-slate-600 dark:text-slate-300 transition-colors"
                aria-label="@lang('phonix::app.general.view_all')"
            >
                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Product Info --}}
    <div class="p-[16px]">
        {{-- Name --}}
        <a
            href="{{ $url }}"
            class="block text-sm font-medium text-slate-800 dark:text-slate-200 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors line-clamp-2 mb-[8px]"
        >
            {{ $name }}
        </a>

        {{-- Rating --}}
        @if ($rating > 0)
            <div class="flex items-center gap-[4px] mb-[8px]">
                @for ($i = 1; $i <= 5; $i++)
                    <svg
                        class="w-[14px] h-[14px] {{ $i <= $rating ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                @endfor
                @if ($reviewsCount > 0)
                    <span class="text-xs text-slate-400 dark:text-slate-500 ms-[4px]">
                        ({{ $reviewsCount }})
                    </span>
                @endif
            </div>
        @endif

        {{-- Price --}}
        <div class="flex items-center gap-[8px] mb-[12px]">
            <span class="text-base font-bold text-phoenix-600 dark:text-phoenix-400">
                {{ $price }}
            </span>
            @if ($originalPrice)
                <span class="text-sm text-slate-400 line-through">
                    {{ $originalPrice }}
                </span>
            @endif
        </div>

        {{-- Add to Cart --}}
        <button
            @click="addToCart()"
            :disabled="cartLoading"
            class="btn-phoenix w-full text-sm py-[10px] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-[6px]"
            aria-label="@lang('phonix::app.product.add_to_cart') - {{ $name }}"
        >
            <svg x-show="!cartLoading" class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            <svg x-show="cartLoading" x-cloak class="w-[16px] h-[16px] animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span x-show="!cartLoading">@lang('phonix::app.product.add_to_cart')</span>
            <span x-show="cartLoading" x-cloak>@lang('phonix::app.general.loading')</span>
        </button>
    </div>
</div>
