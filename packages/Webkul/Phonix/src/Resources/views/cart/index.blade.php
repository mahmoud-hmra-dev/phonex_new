@php
    $cart = \Webkul\Checkout\Facades\Cart::getCart();

    $cartItems = $cart
        ? $cart->items->map(function ($item) {
            $imageData = product_image()->getProductBaseImage($item->product);

            $variantLabels = [];
            if (! empty($item->additional['attributes'])) {
                foreach ($item->additional['attributes'] as $attribute) {
                    $variantLabels[] = ($attribute['attribute_name'] ?? '') . ': ' . ($attribute['option_label'] ?? '');
                }
            }

            return [
                'id'          => $item->id,
                'name'        => $item->name,
                'slug'        => $item->product->url_key ?? null,
                'sku'         => $item->sku,
                'variant'     => implode(' / ', array_filter($variantLabels)),
                'price'       => (float) $item->price,
                'base_price'  => (float) ($item->base_price ?? $item->price),
                'quantity'    => (int) $item->quantity,
                'total'       => (float) $item->total,
                'image'       => $imageData['small_image_url'] ?? null,
                'saleable'    => $item->product->getTypeInstance()->isSaleable(),
            ];
        })->values()->all()
        : [];

    $cartSubTotal    = $cart ? (float) ($cart->sub_total ?? 0) : 0;
    $cartGrandTotal  = $cart ? (float) ($cart->grand_total ?? 0) : 0;
    $cartTax         = $cart ? (float) ($cart->tax_total ?? 0) : 0;
    $cartDiscount    = $cart ? (float) ($cart->discount_amount ?? 0) : 0;
    $cartShipping    = $cart ? (float) ($cart->shipping_amount ?? 0) : 0;
    $couponCode      = $cart?->coupon_code ?? '';
    $freeShippingThreshold = 100;
@endphp

<x-phonix::layouts.index :title="__('phonix::app.cart.title')">

    {{-- ===================================================
         FLASH MESSAGES
    =================================================== --}}
    @session('success')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="fixed top-[80px] inset-x-0 z-50 flex justify-center pointer-events-none px-4"
            role="status"
            aria-live="polite"
        >
            <div class="pointer-events-auto flex items-center gap-3 bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium max-w-md">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $value }}</span>
                <button @click="show = false" class="ms-auto text-white/80 hover:text-white transition-colors" :aria-label="'{{ __('phonix::app.general.close') }}'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endsession

    @session('error')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 6000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="fixed top-[80px] inset-x-0 z-50 flex justify-center pointer-events-none px-4"
            role="alert"
            aria-live="assertive"
        >
            <div class="pointer-events-auto flex items-center gap-3 bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium max-w-md">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <span>{{ $value }}</span>
                <button @click="show = false" class="ms-auto text-white/80 hover:text-white transition-colors" :aria-label="'{{ __('phonix::app.general.close') }}'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endsession

    {{-- ===================================================
         PAGE WRAPPER — Alpine.js root
    =================================================== --}}
    <div
        x-data="phonixCart()"
        x-cloak
        class="container mx-auto section-padding"
    >
        {{-- -----------------------------------------------
             BREADCRUMB
        ----------------------------------------------- --}}
        <nav aria-label="@lang('phonix::app.general.breadcrumb')" class="mb-8" data-gsap="fade-up">
            <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                <li>
                    <a
                        href="{{ route('phonix.home') }}"
                        class="hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors duration-200"
                    >
                        @lang('phonix::app.general.home')
                    </a>
                </li>
                <li aria-hidden="true">
                    <svg class="w-4 h-4 ltr:rotate-0 rtl:rotate-180 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </li>
                <li class="font-medium text-slate-800 dark:text-slate-200" aria-current="page">
                    @lang('phonix::app.cart.title')
                </li>
            </ol>
        </nav>

        {{-- -----------------------------------------------
             INLINE FEEDBACK BANNER (API responses)
        ----------------------------------------------- --}}
        <div
            x-show="feedback.message"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            :class="feedback.type === 'success'
                ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300'
                : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300'"
            class="flex items-center gap-3 px-4 py-3 mb-6 rounded-xl border text-sm font-medium"
            role="status"
            aria-live="polite"
        >
            <template x-if="feedback.type === 'success'">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </template>
            <template x-if="feedback.type === 'error'">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
            </template>
            <span x-text="feedback.message"></span>
            <button @click="feedback.message = ''" class="ms-auto opacity-60 hover:opacity-100 transition-opacity" :aria-label="'{{ __('phonix::app.general.close') }}'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- -----------------------------------------------
             EMPTY CART STATE
        ----------------------------------------------- --}}
        <template x-if="items.length === 0 && !loading">
            <div
                class="flex flex-col items-center justify-center py-20 text-center"
                data-gsap="fade-up"
            >
                {{-- Illustration --}}
                <div
                    class="w-32 h-32 rounded-full bg-phoenix-50 dark:bg-phoenix-900/20 flex items-center justify-center mb-8"
                    aria-hidden="true"
                >
                    <svg class="w-16 h-16 text-phoenix-400 dark:text-phoenix-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.25">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">
                    @lang('phonix::app.cart.empty')
                </h1>
                <p class="text-slate-500 dark:text-slate-400 max-w-sm mb-8 leading-relaxed">
                    @lang('phonix::app.cart.empty_message')
                </p>

                <a
                    href="{{ route('phonix.products.index') }}"
                    class="btn-phoenix px-8 py-4 text-base font-semibold inline-flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                    </svg>
                    @lang('phonix::app.cart.continue_shopping')
                </a>
            </div>
        </template>

        {{-- -----------------------------------------------
             LOADING SKELETON (initial fetch)
        ----------------------------------------------- --}}
        <template x-if="loading">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <div class="lg:col-span-3 space-y-4">
                    <template x-for="i in 3" :key="i">
                        <div class="card-phoenix p-5 animate-pulse">
                            <div class="flex gap-4">
                                <div class="w-20 h-20 rounded-lg bg-slate-200 dark:bg-dark-border flex-shrink-0"></div>
                                <div class="flex-1 space-y-3">
                                    <div class="h-4 bg-slate-200 dark:bg-dark-border rounded w-3/4"></div>
                                    <div class="h-3 bg-slate-200 dark:bg-dark-border rounded w-1/2"></div>
                                    <div class="h-4 bg-slate-200 dark:bg-dark-border rounded w-1/4"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="lg:col-span-2">
                    <div class="card-phoenix p-6 animate-pulse space-y-4">
                        <div class="h-5 bg-slate-200 dark:bg-dark-border rounded w-1/2"></div>
                        <div class="h-3 bg-slate-200 dark:bg-dark-border rounded"></div>
                        <div class="h-3 bg-slate-200 dark:bg-dark-border rounded w-5/6"></div>
                        <div class="h-10 bg-slate-200 dark:bg-dark-border rounded-lg mt-4"></div>
                        <div class="h-12 bg-slate-200 dark:bg-dark-border rounded-xl mt-2"></div>
                    </div>
                </div>
            </div>
        </template>

        {{-- -----------------------------------------------
             CART CONTENT (items + summary)
        ----------------------------------------------- --}}
        <template x-if="items.length > 0 && !loading">
            <div>
                {{-- Page heading row --}}
                <div class="flex flex-wrap items-center justify-between gap-4 mb-8" data-gsap="fade-up">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                            @lang('phonix::app.cart.title')
                        </h1>
                        <span
                            class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 text-xs font-bold text-white rounded-full bg-phoenix-500 dark:bg-phoenix-400 dark:text-phoenix-950"
                            aria-label="@lang('phonix::app.cart.items')"
                        >
                            <span x-text="items.length"></span>
                        </span>
                    </div>

                    {{-- Select-all / clear cart --}}
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                x-model="allSelected"
                                @change="toggleSelectAll()"
                                class="rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 bg-transparent"
                            />
                            @lang('phonix::app.cart.select_all')
                        </label>
                        <button
                            x-show="selectedItems.length > 0"
                            @click="removeSelected()"
                            class="text-sm font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200 flex items-center gap-1"
                            x-transition
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            @lang('phonix::app.cart.remove_selected') (<span x-text="selectedItems.length"></span>)
                        </button>
                    </div>
                </div>

                {{-- Two-column grid: 3/5 items + 2/5 summary --}}
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">

                    {{-- ===========================================
                         LEFT COLUMN — CART ITEMS
                    =========================================== --}}
                    <div class="lg:col-span-3 space-y-4" data-gsap="fade-up">

                        {{-- Desktop column headers --}}
                        <div class="hidden md:grid md:grid-cols-12 gap-4 px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-dark-border">
                            <div class="col-span-1"></div>
                            <div class="col-span-5">@lang('phonix::app.cart.item')</div>
                            <div class="col-span-2 text-center">@lang('phonix::app.product.price')</div>
                            <div class="col-span-2 text-center">@lang('phonix::app.cart.quantity')</div>
                            <div class="col-span-1 text-end">@lang('phonix::app.cart.subtotal')</div>
                            <div class="col-span-1"></div>
                        </div>

                        {{-- Cart item rows --}}
                        <template x-for="(item, index) in items" :key="item.id">
                            <div
                                class="card-phoenix overflow-hidden transition-all duration-300"
                                :class="{ 'ring-2 ring-phoenix-400/50 dark:ring-phoenix-500/50': selectedItems.includes(item.id) }"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                            >
                                {{-- Out-of-stock ribbon --}}
                                <template x-if="!item.saleable">
                                    <div class="bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800 px-5 py-2 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-red-700 dark:text-red-400">
                                            @lang('phonix::app.product.out_of_stock')
                                        </span>
                                    </div>
                                </template>

                                <div class="p-4 md:p-5">
                                    {{-- ---- DESKTOP LAYOUT ---- --}}
                                    <div class="hidden md:grid md:grid-cols-12 gap-4 items-center">

                                        {{-- Checkbox --}}
                                        <div class="col-span-1 flex justify-center">
                                            <input
                                                type="checkbox"
                                                :value="item.id"
                                                x-model="selectedItems"
                                                class="rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 bg-transparent cursor-pointer"
                                                :aria-label="'{{ __('phonix::app.cart.select_item') }}'"
                                            />
                                        </div>

                                        {{-- Product image + info --}}
                                        <div class="col-span-5 flex items-center gap-4">
                                            {{-- Image --}}
                                            <a
                                                :href="item.slug ? '{{ url('/') }}/' + item.slug : '#'"
                                                class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden border border-slate-200 dark:border-dark-border bg-slate-50 dark:bg-dark-card block"
                                                tabindex="-1"
                                                aria-hidden="true"
                                            >
                                                <template x-if="item.image">
                                                    <img
                                                        :src="item.image"
                                                        :alt="item.name"
                                                        class="w-full h-full object-cover"
                                                        loading="lazy"
                                                        width="80"
                                                        height="80"
                                                    />
                                                </template>
                                                <template x-if="!item.image">
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                                        </svg>
                                                    </div>
                                                </template>
                                            </a>

                                            {{-- Name + variant + SKU --}}
                                            <div class="min-w-0 space-y-1">
                                                <a
                                                    :href="item.slug ? '{{ url('/') }}/' + item.slug : '#'"
                                                    class="block text-sm font-semibold text-slate-800 dark:text-slate-100 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors duration-200 leading-snug line-clamp-2"
                                                    x-text="item.name"
                                                ></a>
                                                <p
                                                    x-show="item.variant"
                                                    class="text-xs text-slate-500 dark:text-slate-400"
                                                    x-text="item.variant"
                                                ></p>
                                                <p class="text-xs text-slate-400 dark:text-slate-500 font-mono">
                                                    <span x-text="item.sku"></span>
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Unit price --}}
                                        <div class="col-span-2 text-center">
                                            <span
                                                class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                                x-text="formatPrice(item.price)"
                                            ></span>
                                        </div>

                                        {{-- Quantity stepper --}}
                                        <div class="col-span-2 flex justify-center">
                                            <div
                                                class="inline-flex items-center rounded-lg border border-slate-200 dark:border-dark-border overflow-hidden"
                                                :class="{ 'opacity-50 pointer-events-none': updatingItems.includes(item.id) }"
                                            >
                                                <button
                                                    type="button"
                                                    @click="updateQuantity(item.id, item.quantity - 1)"
                                                    :disabled="item.quantity <= 1 || updatingItems.includes(item.id)"
                                                    class="w-9 h-9 flex items-center justify-center text-slate-500 hover:text-phoenix-600 hover:bg-phoenix-50 dark:text-slate-400 dark:hover:text-phoenix-300 dark:hover:bg-dark-card disabled:opacity-40 disabled:cursor-not-allowed transition-colors duration-150"
                                                    :aria-label="'{{ __('phonix::app.cart.decrease_quantity') }}'"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                                        <path stroke-linecap="round" d="M5 12h14"/>
                                                    </svg>
                                                </button>

                                                <input
                                                    type="number"
                                                    :value="item.quantity"
                                                    min="1"
                                                    max="99"
                                                    @change="updateQuantity(item.id, parseInt($event.target.value) || 1)"
                                                    @keydown.enter.prevent="updateQuantity(item.id, parseInt($event.target.value) || 1)"
                                                    class="w-11 h-9 text-center text-sm font-semibold border-x border-slate-200 dark:border-dark-border bg-transparent text-slate-800 dark:text-slate-100 focus:outline-none focus:bg-phoenix-50/50 dark:focus:bg-phoenix-900/10 transition-colors [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                                    :aria-label="'{{ __('phonix::app.cart.quantity') }}'"
                                                />

                                                <button
                                                    type="button"
                                                    @click="updateQuantity(item.id, item.quantity + 1)"
                                                    :disabled="item.quantity >= 99 || updatingItems.includes(item.id)"
                                                    class="w-9 h-9 flex items-center justify-center text-slate-500 hover:text-phoenix-600 hover:bg-phoenix-50 dark:text-slate-400 dark:hover:text-phoenix-300 dark:hover:bg-dark-card disabled:opacity-40 disabled:cursor-not-allowed transition-colors duration-150"
                                                    :aria-label="'{{ __('phonix::app.cart.increase_quantity') }}'"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                                        <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Line total --}}
                                        <div class="col-span-1 text-end">
                                            <span
                                                class="text-sm font-bold text-phoenix-600 dark:text-phoenix-400"
                                                x-text="formatPrice(item.price * item.quantity)"
                                            ></span>
                                        </div>

                                        {{-- Remove button --}}
                                        <div class="col-span-1 flex justify-end">
                                            <button
                                                type="button"
                                                @click="removeItem(item.id)"
                                                :disabled="updatingItems.includes(item.id)"
                                                class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:text-slate-500 dark:hover:text-red-400 dark:hover:bg-red-900/20 disabled:opacity-40 disabled:cursor-not-allowed transition-colors duration-200"
                                                :aria-label="'{{ __('phonix::app.cart.remove') }}'"
                                            >
                                                <svg class="w-4.5 h-4.5" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- ---- MOBILE LAYOUT ---- --}}
                                    <div class="md:hidden">
                                        <div class="flex gap-3">
                                            {{-- Checkbox --}}
                                            <div class="flex-shrink-0 pt-1">
                                                <input
                                                    type="checkbox"
                                                    :value="item.id"
                                                    x-model="selectedItems"
                                                    class="rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 bg-transparent cursor-pointer"
                                                    :aria-label="'{{ __('phonix::app.cart.select_item') }}'"
                                                />
                                            </div>

                                            {{-- Product image --}}
                                            <a
                                                :href="item.slug ? '{{ url('/') }}/' + item.slug : '#'"
                                                class="w-[72px] h-[72px] flex-shrink-0 rounded-lg overflow-hidden border border-slate-200 dark:border-dark-border bg-slate-50 dark:bg-dark-card block"
                                                tabindex="-1"
                                                aria-hidden="true"
                                            >
                                                <template x-if="item.image">
                                                    <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" loading="lazy" width="72" height="72" />
                                                </template>
                                                <template x-if="!item.image">
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                                        </svg>
                                                    </div>
                                                </template>
                                            </a>

                                            {{-- Info block --}}
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <a
                                                        :href="item.slug ? '{{ url('/') }}/' + item.slug : '#'"
                                                        class="text-sm font-semibold text-slate-800 dark:text-slate-100 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors leading-snug line-clamp-2"
                                                        x-text="item.name"
                                                    ></a>
                                                    <button
                                                        type="button"
                                                        @click="removeItem(item.id)"
                                                        :disabled="updatingItems.includes(item.id)"
                                                        class="flex-shrink-0 p-1.5 rounded-md text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 disabled:opacity-40 transition-colors duration-200"
                                                        :aria-label="'{{ __('phonix::app.cart.remove') }}'"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <p x-show="item.variant" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="item.variant"></p>

                                                <div class="flex items-center justify-between mt-3">
                                                    {{-- Qty stepper --}}
                                                    <div
                                                        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-dark-border overflow-hidden"
                                                        :class="{ 'opacity-50 pointer-events-none': updatingItems.includes(item.id) }"
                                                    >
                                                        <button
                                                            type="button"
                                                            @click="updateQuantity(item.id, item.quantity - 1)"
                                                            :disabled="item.quantity <= 1"
                                                            class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-phoenix-600 hover:bg-phoenix-50 dark:text-slate-400 dark:hover:text-phoenix-300 dark:hover:bg-dark-card disabled:opacity-40 transition-colors"
                                                            :aria-label="'{{ __('phonix::app.cart.decrease_quantity') }}'"
                                                        >
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                                                <path stroke-linecap="round" d="M5 12h14"/>
                                                            </svg>
                                                        </button>
                                                        <span
                                                            class="w-9 text-center text-sm font-semibold text-slate-800 dark:text-slate-100 border-x border-slate-200 dark:border-dark-border leading-8"
                                                            x-text="item.quantity"
                                                        ></span>
                                                        <button
                                                            type="button"
                                                            @click="updateQuantity(item.id, item.quantity + 1)"
                                                            :disabled="item.quantity >= 99"
                                                            class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-phoenix-600 hover:bg-phoenix-50 dark:text-slate-400 dark:hover:text-phoenix-300 dark:hover:bg-dark-card disabled:opacity-40 transition-colors"
                                                            :aria-label="'{{ __('phonix::app.cart.increase_quantity') }}'"
                                                        >
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                                                <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    {{-- Line total (mobile) --}}
                                                    <span
                                                        class="text-sm font-bold text-phoenix-600 dark:text-phoenix-400"
                                                        x-text="formatPrice(item.price * item.quantity)"
                                                    ></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Per-item updating overlay --}}
                                <template x-if="updatingItems.includes(item.id)">
                                    <div class="absolute inset-0 bg-white/60 dark:bg-dark-bg/60 flex items-center justify-center rounded-xl pointer-events-none" aria-hidden="true">
                                        <svg class="animate-spin w-5 h-5 text-phoenix-500" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Continue shopping link --}}
                        <div class="pt-2">
                            <a
                                href="{{ route('phonix.products.index') }}"
                                class="inline-flex items-center gap-2 text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors duration-200 group"
                            >
                                <svg class="w-4 h-4 ltr:group-hover:-translate-x-1 rtl:group-hover:translate-x-1 transition-transform duration-200 ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                                @lang('phonix::app.cart.continue_shopping')
                            </a>
                        </div>

                        {{-- -----------------------------------------------
                             COUPON CODE SECTION (below items, above summary on mobile)
                        ----------------------------------------------- --}}
                        <div class="card-phoenix p-5 mt-2" data-gsap="fade-up">
                            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-phoenix-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008V13.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                                @lang('phonix::app.cart.coupon.title')
                            </h2>

                            {{-- Coupon not yet applied --}}
                            <template x-if="!couponApplied">
                                <form @submit.prevent="applyCoupon()" class="flex gap-2" novalidate>
                                    <div class="flex-1 relative">
                                        <input
                                            type="text"
                                            id="coupon-code"
                                            name="code"
                                            x-model="couponCode"
                                            :disabled="couponLoading"
                                            placeholder="@lang('phonix::app.cart.coupon.placeholder')"
                                            class="input-phoenix w-full py-2.5 pr-10 text-sm"
                                            autocomplete="off"
                                            autocapitalize="characters"
                                        />
                                        <template x-if="couponLoading">
                                            <div class="absolute inset-y-0 end-3 flex items-center pointer-events-none" aria-hidden="true">
                                                <svg class="animate-spin w-4 h-4 text-phoenix-400" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </div>
                                        </template>
                                    </div>
                                    <button
                                        type="submit"
                                        :disabled="!couponCode.trim() || couponLoading"
                                        class="btn-phoenix-outline px-4 py-2.5 text-sm font-medium whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        @lang('phonix::app.cart.coupon.apply')
                                    </button>
                                </form>
                            </template>

                            {{-- Coupon applied --}}
                            <template x-if="couponApplied">
                                <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="block text-xs text-emerald-600 dark:text-emerald-400 font-medium">@lang('phonix::app.cart.coupon.applied')</span>
                                            <span class="block text-sm font-bold text-emerald-700 dark:text-emerald-300 font-mono tracking-wider" x-text="couponCode"></span>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeCoupon()"
                                        :disabled="couponLoading"
                                        class="flex items-center gap-1 text-xs font-semibold text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 disabled:opacity-50 transition-colors duration-200"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        @lang('phonix::app.cart.coupon.remove')
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ===========================================
                         RIGHT COLUMN — ORDER SUMMARY SIDEBAR
                    =========================================== --}}
                    <div class="lg:col-span-2" data-gsap="fade-up">
                        <div class="card-phoenix p-6 lg:sticky lg:top-[88px] space-y-5">

                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                                @lang('phonix::app.cart.summary')
                            </h2>

                            {{-- ---- FREE SHIPPING PROGRESS BAR ---- --}}
                            <div class="p-4 rounded-xl bg-gradient-to-br from-phoenix-50 to-phoenix-100/60 dark:from-phoenix-900/20 dark:to-phoenix-900/10 border border-phoenix-100 dark:border-phoenix-800/50">
                                <div class="flex items-start gap-2.5 mb-3">
                                    <svg class="w-4 h-4 text-phoenix-600 dark:text-phoenix-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.079-.504 1.079-1.125V11.25M18 1.5l3 3.75M18 1.5l-3 3.75m3-3.75V11.25"/>
                                    </svg>
                                    <p class="text-xs font-medium text-phoenix-700 dark:text-phoenix-300 leading-snug">
                                        <template x-if="freeShippingRemaining > 0">
                                            <span>
                                                @lang('phonix::app.cart.free_shipping_add')
                                                <strong x-text="formatPrice(freeShippingRemaining)"></strong>
                                                @lang('phonix::app.cart.free_shipping_suffix')
                                            </span>
                                        </template>
                                        <template x-if="freeShippingRemaining <= 0">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                                </svg>
                                                @lang('phonix::app.cart.free_shipping_unlocked')
                                            </span>
                                        </template>
                                    </p>
                                </div>
                                <div
                                    class="w-full bg-phoenix-200/70 dark:bg-phoenix-900/40 rounded-full h-2 overflow-hidden"
                                    role="progressbar"
                                    :aria-valuenow="Math.min(100, Math.round((subtotal / freeShippingThreshold) * 100))"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    :aria-label="'{{ __('phonix::app.cart.free_shipping_progress') }}'"
                                >
                                    <div
                                        class="h-full rounded-full transition-all duration-700 ease-out"
                                        :class="freeShippingRemaining <= 0 ? 'bg-emerald-500' : 'bg-gradient-to-r from-phoenix-500 to-gold'"
                                        :style="'width:' + Math.min(100, (subtotal / freeShippingThreshold) * 100) + '%'"
                                    ></div>
                                </div>
                                <div class="flex justify-between mt-1.5">
                                    <span class="text-[10px] text-phoenix-500/70 dark:text-phoenix-400/70">{{ core()->currency(0) }}</span>
                                    <span class="text-[10px] text-phoenix-500/70 dark:text-phoenix-400/70">{{ core()->currency($freeShippingThreshold) }}</span>
                                </div>
                            </div>

                            {{-- ---- TOTALS BREAKDOWN ---- --}}
                            <div class="space-y-3 pt-1 border-t border-slate-100 dark:border-dark-border">

                                {{-- Subtotal --}}
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600 dark:text-slate-400">
                                        @lang('phonix::app.cart.subtotal')
                                        <template x-if="selectedItems.length > 0 && selectedItems.length < items.length">
                                            <span class="text-xs text-slate-400 dark:text-slate-500 ms-1">
                                                (<span x-text="selectedItems.length"></span> @lang('phonix::app.cart.selected'))
                                            </span>
                                        </template>
                                    </span>
                                    <span
                                        class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums"
                                        x-text="formatPrice(subtotal)"
                                    ></span>
                                </div>

                                {{-- Shipping --}}
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.shipping')</span>
                                    <span
                                        class="font-semibold tabular-nums"
                                        :class="shippingCost === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-800 dark:text-slate-200'"
                                        x-text="shippingCost === 0 ? '{{ __('phonix::app.checkout.shipping.free') }}' : formatPrice(shippingCost)"
                                    ></span>
                                </div>

                                {{-- Tax --}}
                                <div class="flex items-center justify-between text-sm" x-show="tax > 0">
                                    <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.tax')</span>
                                    <span
                                        class="font-semibold text-slate-800 dark:text-slate-200 tabular-nums"
                                        x-text="formatPrice(tax)"
                                    ></span>
                                </div>

                                {{-- Discount --}}
                                <div class="flex items-center justify-between text-sm" x-show="discount > 0" x-transition>
                                    <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/>
                                        </svg>
                                        @lang('phonix::app.cart.discount')
                                    </span>
                                    <span
                                        class="font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums"
                                        x-text="'-' + formatPrice(discount)"
                                    ></span>
                                </div>
                            </div>

                            {{-- ---- GRAND TOTAL ---- --}}
                            <div class="flex items-center justify-between pt-4 border-t-2 border-slate-200 dark:border-dark-border">
                                <span class="text-base font-bold text-slate-900 dark:text-white">
                                    @lang('phonix::app.cart.grand_total')
                                </span>
                                <div class="text-end">
                                    <span
                                        class="text-2xl font-extrabold text-phoenix-600 dark:text-phoenix-400 tabular-nums"
                                        x-text="formatPrice(grandTotal)"
                                    ></span>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                        @lang('phonix::app.cart.vat_included')
                                    </p>
                                </div>
                            </div>

                            {{-- ---- CHECKOUT BUTTON ---- --}}
                            <a
                                href="{{ route('phonix.checkout.index') }}"
                                class="btn-phoenix w-full justify-center py-3.5 text-base font-semibold flex items-center gap-2.5"
                                data-gsap="pulse-cta"
                            >
                                @lang('phonix::app.cart.proceed_to_checkout')
                                <svg class="w-5 h-5 ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                </svg>
                            </a>

                            {{-- ---- CONTINUE SHOPPING ---- --}}
                            <a
                                href="{{ route('phonix.products.index') }}"
                                class="btn-phoenix-outline w-full justify-center py-3 text-sm font-medium flex items-center gap-2"
                            >
                                <svg class="w-4 h-4 ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                                @lang('phonix::app.cart.continue_shopping')
                            </a>

                            {{-- ---- PAYMENT BADGES ---- --}}
                            <div class="pt-4 border-t border-slate-100 dark:border-dark-border space-y-3">
                                {{-- Accepted payments --}}
                                <p class="text-xs text-center text-slate-400 dark:text-slate-500 font-medium uppercase tracking-wider">
                                    @lang('phonix::app.footer.payment.accepted_payments')
                                </p>
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    {{-- Visa --}}
                                    <div
                                        class="h-7 px-2.5 rounded-md border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-card flex items-center justify-center"
                                        title="Visa"
                                        role="img"
                                        aria-label="Visa"
                                    >
                                        <svg viewBox="0 0 48 16" height="12" width="36" aria-hidden="true">
                                            <text x="0" y="13" fill="#1A1F71" font-family="Arial, sans-serif" font-weight="bold" font-size="14" letter-spacing="-0.5">VISA</text>
                                        </svg>
                                    </div>
                                    {{-- Mastercard --}}
                                    <div
                                        class="h-7 px-2 rounded-md border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-card flex items-center justify-center gap-0.5"
                                        title="Mastercard"
                                        role="img"
                                        aria-label="Mastercard"
                                    >
                                        <div class="w-4 h-4 rounded-full bg-[#EB001B] opacity-90"></div>
                                        <div class="w-4 h-4 rounded-full bg-[#F79E1B] opacity-90 -ms-2"></div>
                                    </div>
                                    {{-- PayPal --}}
                                    <div
                                        class="h-7 px-2.5 rounded-md border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-card flex items-center justify-center"
                                        title="PayPal"
                                        role="img"
                                        aria-label="PayPal"
                                    >
                                        <svg viewBox="0 0 60 16" height="12" width="44" aria-hidden="true">
                                            <text x="0" y="13" fill="#003087" font-family="Arial, sans-serif" font-weight="bold" font-size="12">Pay</text>
                                            <text x="22" y="13" fill="#009CDE" font-family="Arial, sans-serif" font-weight="bold" font-size="12">Pal</text>
                                        </svg>
                                    </div>
                                    {{-- Amex --}}
                                    <div
                                        class="h-7 px-2 rounded-md border border-slate-200 dark:border-dark-border bg-[#016FD0] flex items-center justify-center"
                                        title="American Express"
                                        role="img"
                                        aria-label="American Express"
                                    >
                                        <svg viewBox="0 0 48 14" height="10" width="36" aria-hidden="true">
                                            <text x="0" y="11" fill="white" font-family="Arial, sans-serif" font-weight="bold" font-size="10" letter-spacing="0.5">AMEX</text>
                                        </svg>
                                    </div>
                                </div>

                                {{-- Security badges --}}
                                <div class="flex items-center justify-center gap-4 pt-1">
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                        </svg>
                                        @lang('phonix::app.cart.ssl_secured')
                                    </div>
                                    <div class="w-px h-4 bg-slate-200 dark:bg-dark-border" aria-hidden="true"></div>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        <svg class="w-4 h-4 text-phoenix-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                        </svg>
                                        @lang('phonix::app.cart.safe_checkout')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- end grid --}}
            </div>
        </template>

        {{-- -----------------------------------------------
             REMOVE CONFIRMATION MODAL
        ----------------------------------------------- --}}
        <div
            x-show="confirmModal.open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="'{{ __('phonix::app.cart.confirm_remove_title') }}'"
            @keydown.escape.window="confirmModal.open = false"
        >
            {{-- Backdrop --}}
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="confirmModal.open = false"
                aria-hidden="true"
            ></div>

            {{-- Modal panel --}}
            <div
                x-show="confirmModal.open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative z-10 bg-white dark:bg-dark-surface rounded-2xl shadow-2xl p-6 max-w-sm w-full"
                x-trap.inert.noscroll="confirmModal.open"
            >
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            @lang('phonix::app.cart.confirm_remove_title')
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            @lang('phonix::app.cart.confirm_remove_message')
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button
                        type="button"
                        @click="confirmModal.open = false"
                        class="btn-phoenix-outline flex-1 py-2.5 text-sm font-medium"
                    >
                        @lang('phonix::app.general.cancel')
                    </button>
                    <button
                        type="button"
                        @click="executeRemove()"
                        class="flex-1 py-2.5 text-sm font-semibold rounded-xl bg-red-600 hover:bg-red-700 text-white transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-dark-surface"
                    >
                        @lang('phonix::app.cart.confirm_remove_action')
                    </button>
                </div>
            </div>
        </div>

    </div>{{-- end x-data --}}

    {{-- ===================================================
         ALPINE.JS COMPONENT
    =================================================== --}}
    @pushOnce('scripts')
    <script>
        function phonixCart() {
            return {
                // ── State ─────────────────────────────────────────────────────────
                items: @json($cartItems),
                selectedItems: [],
                allSelected: false,

                shippingCost: {{ $cartShipping }},
                tax: {{ $cartTax }},
                discount: {{ $cartDiscount }},

                couponCode: @json($couponCode),
                couponApplied: {{ !empty($couponCode) ? 'true' : 'false' }},
                couponLoading: false,

                freeShippingThreshold: {{ $freeShippingThreshold }},
                currency: @json(core()->getCurrentCurrencyCode()),

                loading: false,
                updatingItems: [],   // ids of items currently being updated

                feedback: { message: '', type: 'success' },
                confirmModal: { open: false, itemId: null },

                // ── Computed ──────────────────────────────────────────────────────
                get subtotal() {
                    if (this.selectedItems.length > 0 && this.selectedItems.length < this.items.length) {
                        return this.items
                            .filter(i => this.selectedItems.includes(i.id))
                            .reduce((sum, i) => sum + (i.price * i.quantity), 0);
                    }
                    return this.items.reduce((sum, i) => sum + (i.price * i.quantity), 0);
                },

                get grandTotal() {
                    return Math.max(0, this.subtotal + this.shippingCost + this.tax - this.discount);
                },

                get freeShippingRemaining() {
                    return Math.max(0, this.freeShippingThreshold - this.subtotal);
                },

                // ── Formatting ────────────────────────────────────────────────────
                formatPrice(amount) {
                    try {
                        return new Intl.NumberFormat(undefined, {
                            style: 'currency',
                            currency: this.currency,
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2,
                        }).format(amount);
                    } catch (e) {
                        return this.currency + ' ' + Number(amount).toFixed(2);
                    }
                },

                // ── Selection helpers ─────────────────────────────────────────────
                toggleSelectAll() {
                    if (this.allSelected) {
                        this.selectedItems = this.items.map(i => i.id);
                    } else {
                        this.selectedItems = [];
                    }
                },

                syncAllSelected() {
                    this.allSelected = this.items.length > 0 && this.selectedItems.length === this.items.length;
                },

                // ── API helpers ───────────────────────────────────────────────────
                csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
                },

                showFeedback(message, type = 'success') {
                    this.feedback = { message, type };
                    setTimeout(() => { this.feedback.message = ''; }, 4500);
                },

                // ── Cart actions ──────────────────────────────────────────────────
                async updateQuantity(itemId, newQty) {
                    if (newQty < 1) {
                        this.removeItem(itemId);
                        return;
                    }

                    if (this.updatingItems.includes(itemId)) return;
                    this.updatingItems.push(itemId);

                    // Optimistic update
                    const item = this.items.find(i => i.id === itemId);
                    const prevQty = item ? item.quantity : newQty;
                    if (item) item.quantity = newQty;

                    try {
                        const body = { qty: {} };
                        body.qty[itemId] = newQty;

                        const res = await fetch('{{ route('shop.api.checkout.cart.update') }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                            body: JSON.stringify(body),
                        });

                        const data = await res.json();

                        if (!res.ok) {
                            // Rollback on error
                            if (item) item.quantity = prevQty;
                            this.showFeedback(data.message || '{{ __('phonix::app.messages.error.update_failed') }}', 'error');
                            return;
                        }

                        this.syncFromApiResponse(data);
                        this.showFeedback('{{ __('phonix::app.cart.quantity_updated') }}', 'success');

                    } catch (e) {
                        if (item) item.quantity = prevQty;
                        this.showFeedback('{{ __('phonix::app.messages.error.network') }}', 'error');
                    } finally {
                        this.updatingItems = this.updatingItems.filter(id => id !== itemId);
                    }
                },

                removeItem(itemId) {
                    this.confirmModal = { open: true, itemId };
                },

                async executeRemove() {
                    const itemId = this.confirmModal.itemId;
                    this.confirmModal.open = false;

                    if (!itemId) return;
                    if (this.updatingItems.includes(itemId)) return;
                    this.updatingItems.push(itemId);

                    try {
                        const res = await fetch('{{ route('shop.api.checkout.cart.destroy') }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                            body: JSON.stringify({ cart_item_id: itemId }),
                        });

                        const data = await res.json();

                        if (!res.ok) {
                            this.showFeedback(data.message || '{{ __('phonix::app.messages.error.remove_failed') }}', 'error');
                            return;
                        }

                        // Remove from local state
                        this.items = this.items.filter(i => i.id !== itemId);
                        this.selectedItems = this.selectedItems.filter(id => id !== itemId);
                        this.syncAllSelected();
                        this.syncFromApiResponse(data);
                        this.showFeedback('{{ __('phonix::app.cart.item_removed') }}', 'success');

                    } catch (e) {
                        this.showFeedback('{{ __('phonix::app.messages.error.network') }}', 'error');
                    } finally {
                        this.updatingItems = this.updatingItems.filter(id => id !== itemId);
                    }
                },

                async removeSelected() {
                    if (this.selectedItems.length === 0) return;

                    if (this.selectedItems.length === this.items.length) {
                        // Remove all via single call
                        this.loading = true;
                        try {
                            const ids = [...this.selectedItems];
                            const res = await fetch('{{ route('shop.api.checkout.cart.destroy_selected') }}', {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken(),
                                },
                                body: JSON.stringify({ ids }),
                            });

                            const data = await res.json();

                            if (!res.ok) {
                                this.showFeedback(data.message || '{{ __('phonix::app.messages.error.remove_failed') }}', 'error');
                                return;
                            }

                            this.items = [];
                            this.selectedItems = [];
                            this.syncFromApiResponse(data);
                            this.showFeedback('{{ __('phonix::app.cart.items_removed') }}', 'success');

                        } catch (e) {
                            this.showFeedback('{{ __('phonix::app.messages.error.network') }}', 'error');
                        } finally {
                            this.loading = false;
                        }
                    } else {
                        // Remove each selected individually
                        const ids = [...this.selectedItems];
                        for (const id of ids) {
                            await this._removeSingle(id);
                        }
                        this.showFeedback('{{ __('phonix::app.cart.items_removed') }}', 'success');
                    }
                },

                async _removeSingle(itemId) {
                    this.updatingItems.push(itemId);
                    try {
                        const res = await fetch('{{ route('shop.api.checkout.cart.destroy') }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                            body: JSON.stringify({ cart_item_id: itemId }),
                        });

                        if (res.ok) {
                            this.items = this.items.filter(i => i.id !== itemId);
                            this.selectedItems = this.selectedItems.filter(id => id !== itemId);
                        }
                    } catch (e) {
                        // Silent — outer handler shows feedback
                    } finally {
                        this.updatingItems = this.updatingItems.filter(id => id !== itemId);
                    }
                },

                // ── Coupon actions ────────────────────────────────────────────────
                async applyCoupon() {
                    const code = this.couponCode.trim();
                    if (!code) return;
                    this.couponLoading = true;

                    try {
                        const res = await fetch('{{ route('shop.api.checkout.cart.coupon.apply') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                            body: JSON.stringify({ code }),
                        });

                        const data = await res.json();

                        if (!res.ok) {
                            this.showFeedback(data.message || '{{ __('phonix::app.cart.coupon.invalid') }}', 'error');
                            return;
                        }

                        this.couponApplied = true;
                        this.syncFromApiResponse(data);
                        this.showFeedback(data.message || '{{ __('phonix::app.cart.coupon.success') }}', 'success');

                    } catch (e) {
                        this.showFeedback('{{ __('phonix::app.messages.error.network') }}', 'error');
                    } finally {
                        this.couponLoading = false;
                    }
                },

                async removeCoupon() {
                    this.couponLoading = true;

                    try {
                        const res = await fetch('{{ route('shop.api.checkout.cart.coupon.remove') }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                        });

                        const data = await res.json();

                        if (!res.ok) {
                            this.showFeedback(data.message || '{{ __('phonix::app.messages.error.generic') }}', 'error');
                            return;
                        }

                        this.couponApplied = false;
                        this.couponCode = '';
                        this.discount = 0;
                        this.syncFromApiResponse(data);
                        this.showFeedback(data.message || '{{ __('phonix::app.cart.coupon.removed') }}', 'success');

                    } catch (e) {
                        this.showFeedback('{{ __('phonix::app.messages.error.network') }}', 'error');
                    } finally {
                        this.couponLoading = false;
                    }
                },

                // ── Sync totals from API response ─────────────────────────────────
                syncFromApiResponse(data) {
                    const cart = data?.data;
                    if (!cart) return;

                    if (typeof cart.shipping_amount !== 'undefined') this.shippingCost = parseFloat(cart.shipping_amount) || 0;
                    if (typeof cart.tax_total !== 'undefined') this.tax = parseFloat(cart.tax_total) || 0;
                    if (typeof cart.discount_amount !== 'undefined') this.discount = parseFloat(cart.discount_amount) || 0;

                    // Sync item quantities from server response if provided
                    if (Array.isArray(cart.items)) {
                        cart.items.forEach(serverItem => {
                            const localItem = this.items.find(i => i.id === serverItem.id);
                            if (localItem && serverItem.quantity) {
                                localItem.quantity = parseInt(serverItem.quantity);
                            }
                        });
                    }
                },
            };
        }
    </script>
    @endPushOnce

</x-phonix::layouts.index>
