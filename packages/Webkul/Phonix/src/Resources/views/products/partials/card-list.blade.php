@php
    $pImg        = product_image()->getProductBaseImage($product);
    $hasDiscount = $product->getTypeInstance()->haveDiscount();
    $salePrice   = $hasDiscount ? $product->getTypeInstance()->getMinimalPrice() : null;
    $discPct     = ($hasDiscount && $product->price > 0)
                   ? (int) round((($product->price - $salePrice) / $product->price) * 100) : 0;
    $avgRating   = $product->reviews->count() > 0 ? round($product->reviews->avg('rating')) : 0;
    $pUrl        = route('phonix.products.view', ['slug' => $product->url_key]);
@endphp

<article class="flex bg-white dark:bg-dark-card rounded-2xl border border-slate-100 dark:border-dark-border shadow-sm hover:shadow-md hover:border-phoenix-200 dark:hover:border-phoenix-700 transition-all overflow-hidden group">
    <a href="{{ $pUrl }}" class="relative shrink-0 w-[140px] sm:w-[200px] aspect-square bg-slate-50 dark:bg-dark-surface overflow-hidden">
        @if ($pImg['medium_image_url'])
            <img src="{{ $pImg['medium_image_url'] }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy"/>
        @else
            <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                <svg class="w-[36px] h-[36px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
            </div>
        @endif
        @if ($discPct > 0)
            <span class="absolute top-[8px] start-[8px] px-[8px] py-[3px] rounded-lg text-[10px] font-bold gradient-plasma text-white shadow">-{{ $discPct }}%</span>
        @endif
    </a>

    <div class="flex-1 p-[14px] sm:p-[18px] flex flex-col justify-between min-w-0">
        <div>
            <a href="{{ $pUrl }}" class="block text-base font-semibold text-slate-900 dark:text-white hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors line-clamp-2 mb-[6px]">{{ $product->name }}</a>
            @if ($avgRating > 0)
                <div class="flex items-center gap-[3px] mb-[8px]">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-[13px] h-[13px] {{ $i <= $avgRating ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                    <span class="text-xs text-slate-500 dark:text-slate-400 ms-[4px]">({{ $product->reviews->count() }})</span>
                </div>
            @endif
            @if ($product->short_description)
                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">{{ strip_tags($product->short_description) }}</p>
            @endif
        </div>

        <div class="flex flex-wrap items-center justify-between gap-[10px] pt-[12px] mt-[12px] border-t border-slate-100 dark:border-dark-border">
            <div class="flex items-baseline gap-[8px]">
                <span class="font-display text-lg font-bold text-slate-900 dark:text-white">
                    {{ $hasDiscount ? core()->currency($salePrice) : core()->currency($product->price) }}
                </span>
                @if ($hasDiscount)
                    <span class="text-sm text-slate-400 line-through">{{ core()->currency($product->price) }}</span>
                    <span class="px-[6px] py-[2px] rounded text-[10px] font-bold bg-plasma-500/10 text-plasma-600">-{{ $discPct }}%</span>
                @endif
            </div>
            <div class="flex items-center gap-[8px]">
                <a href="{{ $pUrl }}" class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border border-slate-200 dark:border-dark-border text-slate-500 hover:border-phoenix-400 hover:text-phoenix-500 transition-colors" aria-label="@lang('phonix::app.listing.quick_view')" title="@lang('phonix::app.listing.quick_view')">
                    <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </a>
                <button type="button"
                        @click="document.dispatchEvent(new CustomEvent('phonix:compare:toggle', { detail: { id: {{ $product->id }}, name: {{ json_encode($product->name) }}, imageUrl: {{ json_encode($pImg['medium_image_url']) }} } }))"
                        class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border border-slate-200 dark:border-dark-border text-slate-500 hover:border-phoenix-400 hover:text-phoenix-500 transition-colors"
                        aria-label="@lang('phonix::app.product.compare')" title="@lang('phonix::app.product.compare')">
                    <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                </button>
                <button type="button"
                        @click="toggleWishlist({{ $product->id }})"
                        :class="wishlistItems.includes({{ $product->id }}) ? 'text-plasma-500 border-plasma-300' : 'text-slate-500 border-slate-200 dark:border-dark-border hover:border-plasma-400 hover:text-plasma-500'"
                        class="flex items-center justify-center w-[38px] h-[38px] rounded-xl border transition-colors"
                        aria-label="@lang('phonix::app.product.add_to_wishlist')" title="@lang('phonix::app.product.add_to_wishlist')">
                    <svg class="w-[16px] h-[16px]" :fill="wishlistItems.includes({{ $product->id }}) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                </button>
                <button type="button"
                        @click="addToCart({{ $product->id }}, '{{ $pUrl }}')"
                        :disabled="cartLoading === {{ $product->id }}"
                        class="flex items-center gap-[6px] px-[18px] h-[38px] rounded-xl text-xs font-bold bg-slate-900 hover:bg-phoenix-500 dark:bg-dark-surface dark:hover:bg-phoenix-500 text-white transition-colors disabled:opacity-60">
                    <svg x-show="cartLoading !== {{ $product->id }}" class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .956-.343 1.087-.835l2.25-8.482a.75.75 0 00-.725-.952H5.106m0 0L4.32 2.272M7.5 14.25a3 3 0 00-3 3h15.75m-8.25 3.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm7.5 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                    <svg x-show="cartLoading === {{ $product->id }}" x-cloak class="w-[14px] h-[14px] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    @lang('phonix::app.product.add_to_cart')
                </button>
            </div>
        </div>
    </div>
</article>
