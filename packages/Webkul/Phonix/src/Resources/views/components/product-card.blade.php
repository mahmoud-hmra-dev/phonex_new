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
])

@php
    $discountPercent = null;
    if ($originalPrice && $price) {
        $p = (float) preg_replace('/[^0-9.]/', '', (string) $price);
        $o = (float) preg_replace('/[^0-9.]/', '', (string) $originalPrice);
        if ($o > 0 && $p > 0 && $o > $p) {
            $discountPercent = (int) round((($o - $p) / $o) * 100);
        }
    }
@endphp

<article
    x-data="{
        hovered: false,
        cartLoading: false,
        wishlistLoading: false,
        inWishlist: false,
        productId: {{ $productId ?? 'null' }},
        csrfToken: document.querySelector('meta[name=csrf-token]')?.content ?? '',

        addToCart() {
            if (!this.productId) { window.location.href = {{ json_encode($url) }}; return; }
            this.cartLoading = true;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('phonix.cart.add') }}';
            form.style.display = 'none';
            form.innerHTML = `
                <input type='hidden' name='_token'     value='${this.csrfToken}'>
                <input type='hidden' name='product_id' value='${this.productId}'>
                <input type='hidden' name='quantity'   value='1'>
            `;
            document.body.appendChild(form);
            form.submit();
        },

        async toggleWishlist() {
            if (!this.productId) return;
            this.wishlistLoading = true;
            try {
                const res = await fetch('{{ route('phonix.wishlist.toggle') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ product_id: this.productId })
                });
                const data = await res.json();
                if (res.status === 401 && data.redirect) { window.location.href = data.redirect; return; }
                if (data.success) { this.inWishlist = data.in_wishlist; }
            } catch(e) {}
            finally { this.wishlistLoading = false; }
        }
    }"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
    class="group relative card-phoenix !rounded-2xl overflow-hidden flex flex-col"
    data-gsap="fade-up"
>
    {{-- Image Container --}}
    <div class="relative overflow-hidden aspect-square bg-gradient-to-br from-slate-50 to-slate-100 dark:from-dark-surface dark:to-dark-card">
        <a href="{{ $url }}" class="block w-full h-full" aria-label="{{ $name }}">
            @if ($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $name }}"
                    class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.08]"
                    loading="lazy"
                />
            @else
                <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                    <svg class="w-[48px] h-[48px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                    </svg>
                </div>
            @endif
        </a>

        {{-- Discount ribbon --}}
        @if ($discountPercent)
            <div class="absolute top-[12px] start-[12px] z-10 px-[10px] py-[4px] rounded-full gradient-plasma text-white text-[11px] font-bold shadow-lg shadow-plasma-500/30">
                -{{ $discountPercent }}%
            </div>
        @elseif ($badge)
            <div class="absolute top-[12px] start-[12px] z-10">
                <span class="badge-{{ $badge }}">
                    @lang('phonix::app.product.' . $badge)
                </span>
            </div>
        @endif

        {{-- Wishlist (always visible, top-right) --}}
        <button
            @click.stop.prevent="toggleWishlist()"
            :disabled="wishlistLoading"
            :class="inWishlist ? 'bg-plasma-500 text-white' : 'bg-white/90 dark:bg-dark-card/90 text-slate-700 dark:text-slate-300 hover:bg-plasma-500 hover:text-white'"
            class="absolute top-[12px] end-[12px] z-10 flex items-center justify-center w-[36px] h-[36px] rounded-full backdrop-blur shadow-lg transition-all disabled:opacity-60"
            aria-label="@lang('phonix::app.product.add_to_wishlist')"
        >
            <svg x-show="!wishlistLoading" class="w-[16px] h-[16px]" :fill="inWishlist ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
            <svg x-show="wishlistLoading" x-cloak class="w-[14px] h-[14px] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </button>

        {{-- Quick actions on hover --}}
        <div
            x-show="hovered" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="absolute bottom-[12px] inset-x-[12px] z-10 flex items-center justify-center gap-[6px]"
        >
            <a href="{{ $url }}" class="flex items-center justify-center w-[36px] h-[36px] rounded-full bg-white/95 dark:bg-dark-card/95 backdrop-blur text-slate-700 dark:text-slate-300 hover:bg-phoenix-500 hover:text-white shadow-md transition-colors" aria-label="@lang('phonix::app.listing.quick_view')">
                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </a>
            @if ($productId)
                <button
                    @click.stop.prevent="document.dispatchEvent(new CustomEvent('phonix:compare:toggle', { detail: { id: {{ $productId }}, name: {{ json_encode($name) }}, imageUrl: {{ json_encode($imageUrl) }} } }))"
                    class="flex items-center justify-center w-[36px] h-[36px] rounded-full bg-white/95 dark:bg-dark-card/95 backdrop-blur text-slate-700 dark:text-slate-300 hover:bg-phoenix-500 hover:text-white shadow-md transition-colors"
                    aria-label="@lang('phonix::app.product.compare')"
                >
                    <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                </button>
            @endif
            @if ($imageUrl)
                <a href="https://lens.google.com/uploadbyurl?url={{ urlencode($imageUrl) }}" target="_blank" rel="noopener noreferrer" @click.stop class="flex items-center justify-center w-[36px] h-[36px] rounded-full bg-white/95 dark:bg-dark-card/95 backdrop-blur text-slate-700 dark:text-slate-300 hover:bg-phoenix-500 hover:text-white shadow-md transition-colors" aria-label="@lang('phonix::app.product.search_by_image')" title="@lang('phonix::app.product.search_by_image')">
                    <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                </a>
            @endif
        </div>
    </div>

    {{-- Product Info --}}
    <div class="p-[16px] flex-1 flex flex-col">
        {{-- Rating --}}
        @if ($rating > 0)
            <div class="flex items-center gap-[6px] mb-[8px]">
                <div class="flex items-center gap-[2px]">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-[12px] h-[12px] {{ $i <= $rating ? 'text-gold' : 'text-slate-200 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                @if ($reviewsCount > 0)
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">({{ $reviewsCount }})</span>
                @endif
            </div>
        @endif

        {{-- Name --}}
        <a href="{{ $url }}" class="block text-sm font-semibold text-slate-900 dark:text-white hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors line-clamp-2 mb-[10px] min-h-[40px]">
            {{ $name }}
        </a>

        {{-- Price --}}
        <div class="mt-auto flex items-baseline gap-[8px] mb-[12px]">
            <span class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ $price }}</span>
            @if ($originalPrice)
                <span class="text-xs text-slate-400 line-through">{{ $originalPrice }}</span>
            @endif
        </div>

        {{-- Add to Cart --}}
        <button
            @click="addToCart()"
            :disabled="cartLoading"
            class="w-full inline-flex items-center justify-center gap-[8px] py-[10px] px-[16px] text-[13px] font-semibold rounded-xl bg-slate-900 hover:bg-phoenix-500 dark:bg-dark-surface dark:hover:bg-phoenix-500 text-white border border-transparent transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label="@lang('phonix::app.product.add_to_cart') - {{ $name }}"
        >
            <svg x-show="!cartLoading" class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .956-.343 1.087-.835l2.25-8.482a.75.75 0 00-.725-.952H5.106m0 0L4.32 2.272M7.5 14.25a3 3 0 00-3 3h15.75m-8.25 3.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm7.5 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
            <svg x-show="cartLoading" x-cloak class="w-[14px] h-[14px] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span x-show="!cartLoading">@lang('phonix::app.product.add_to_cart')</span>
            <span x-show="cartLoading" x-cloak>@lang('phonix::app.general.loading')</span>
        </button>
    </div>
</article>
