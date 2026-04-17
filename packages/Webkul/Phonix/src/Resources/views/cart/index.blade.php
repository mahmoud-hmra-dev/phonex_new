@php
    $cart = \Webkul\Checkout\Facades\Cart::getCart();

    $cartItems = $cart
        ? $cart->items->map(function ($item) {
            $imageData = product_image()->getProductBaseImage($item->product);

            $variantLabels = [];
            if (! empty($item->additional['attributes'])) {
                foreach ($item->additional['attributes'] as $attribute) {
                    $variantLabels[] = $attribute['option_label'] ?? ($attribute['attribute_name'] ?? null);
                }
            }

            return [
                'id'       => $item->id,
                'name'     => $item->name,
                'variant'  => implode(' / ', array_filter($variantLabels)),
                'price'    => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'image'    => $imageData['small_image_url'] ?? null,
            ];
        })->values()->all()
        : [];

    $cartTotals = [
        'shipping' => $cart ? (float) ($cart->shipping_amount ?? 0) : 0,
        'tax'      => $cart ? (float) ($cart->tax_total ?? 0) : 0,
        'discount' => $cart ? (float) ($cart->discount_amount ?? 0) : 0,
        'coupon'   => $cart?->coupon_code,
    ];
@endphp

<x-phonix::layouts.index :title="__('phonix::app.cart.title')">

    <div
        x-data="cartPage()"
        class="container mx-auto section-padding"
    >
        {{-- Breadcrumb --}}
        <nav aria-label="@lang('phonix::app.general.breadcrumb')" class="mb-[32px]" data-gsap="fade-up">
            <ol class="flex items-center gap-[8px] text-sm text-slate-500 dark:text-slate-400">
                <li><a href="/" class="hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors">@lang('phonix::app.general.home')</a></li>
                <li aria-hidden="true">
                    <svg class="w-[16px] h-[16px] ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </li>
                <li class="text-slate-800 dark:text-slate-200 font-medium" aria-current="page">@lang('phonix::app.cart.title')</li>
            </ol>
        </nav>

        {{-- Empty State --}}
        <template x-if="items.length === 0">
            <div class="flex flex-col items-center justify-center py-[80px] text-center" data-gsap="fade-up">
                <div class="w-[120px] h-[120px] rounded-full bg-phoenix-50 dark:bg-phoenix-900/30 flex items-center justify-center mb-[32px]">
                    <svg class="w-[56px] h-[56px] text-phoenix-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                </div>
                <h2 class="text-fluid-2xl font-semibold text-slate-800 dark:text-white mb-[12px]">@lang('phonix::app.cart.empty')</h2>
                <p class="text-slate-500 dark:text-slate-400 max-w-md mb-[32px]">@lang('phonix::app.cart.empty_message')</p>
                <x-phonix::button :href="'/'" variant="primary" size="lg">
                    @lang('phonix::app.cart.continue_shopping')
                </x-phonix::button>
            </div>
        </template>

        {{-- Cart Content --}}
        <template x-if="items.length > 0">
            <div>
                {{-- Page Title --}}
                <div class="flex items-center gap-[12px] mb-[32px]" data-gsap="fade-up">
                    <h1 class="text-fluid-2xl font-bold text-slate-900 dark:text-white">@lang('phonix::app.cart.title')</h1>
                    <span class="inline-flex items-center justify-center min-w-[28px] h-[28px] px-[8px] text-xs font-bold text-white rounded-full bg-phoenix-500 dark:bg-phoenix-400 dark:text-phoenix-950">
                        <span x-text="items.length"></span> <span class="ms-[4px]">@lang('phonix::app.cart.items')</span>
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-[32px]">
                    {{-- Cart Items --}}
                    <div class="lg:col-span-2 space-y-[16px]" data-gsap="fade-up">
                        {{-- Desktop Table Header --}}
                        <div class="hidden md:grid md:grid-cols-12 gap-[16px] px-[20px] py-[12px] text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-dark-border">
                            <div class="col-span-5">@lang('phonix::app.cart.item')</div>
                            <div class="col-span-2 text-center">@lang('phonix::app.product.price')</div>
                            <div class="col-span-2 text-center">@lang('phonix::app.cart.quantity')</div>
                            <div class="col-span-2 text-end">@lang('phonix::app.cart.subtotal')</div>
                            <div class="col-span-1"></div>
                        </div>

                        {{-- Items --}}
                        <template x-for="(item, index) in items" :key="index">
                            <div
                                class="card-phoenix p-[16px] md:p-[20px]"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                            >
                                {{-- Desktop Row --}}
                                <div class="hidden md:grid md:grid-cols-12 gap-[16px] items-center">
                                    {{-- Product --}}
                                    <div class="col-span-5 flex items-center gap-[16px]">
                                        <div class="w-[80px] h-[80px] flex-shrink-0 rounded-md bg-slate-100 dark:bg-dark-card overflow-hidden border border-slate-200 dark:border-dark-border">
                                            <template x-if="item.image">
                                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" loading="lazy" />
                                            </template>
                                            <template x-if="!item.image">
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-[36px] h-[36px] text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                                                    </svg>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate" x-text="item.name"></h3>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-[4px]" x-text="item.variant"></p>
                                        </div>
                                    </div>

                                    {{-- Unit Price --}}
                                    <div class="col-span-2 text-center text-sm font-medium text-slate-700 dark:text-slate-300" x-text="formatPrice(item.price)"></div>

                                    {{-- Quantity --}}
                                    <div class="col-span-2 flex justify-center">
                                        <div class="inline-flex items-center border border-slate-200 dark:border-dark-border rounded-md">
                                            <button
                                                @click="decrementQty(index)"
                                                class="w-[36px] h-[36px] flex items-center justify-center text-slate-500 hover:text-phoenix-600 hover:bg-phoenix-50 dark:text-slate-400 dark:hover:text-phoenix-300 dark:hover:bg-dark-card rounded-s-md transition-colors"
                                                :aria-label="'Decrease quantity'"
                                            >
                                                <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M5 12h14"/></svg>
                                            </button>
                                            <input
                                                type="number"
                                                x-model.number="item.quantity"
                                                min="1"
                                                max="99"
                                                class="w-[44px] h-[36px] text-center text-sm font-medium border-x border-slate-200 dark:border-dark-border bg-transparent text-slate-800 dark:text-slate-200 focus:outline-none"
                                                :aria-label="'{{ __('phonix::app.cart.quantity') }}'"
                                            />
                                            <button
                                                @click="incrementQty(index)"
                                                class="w-[36px] h-[36px] flex items-center justify-center text-slate-500 hover:text-phoenix-600 hover:bg-phoenix-50 dark:text-slate-400 dark:hover:text-phoenix-300 dark:hover:bg-dark-card rounded-e-md transition-colors"
                                                :aria-label="'Increase quantity'"
                                            >
                                                <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Line Total --}}
                                    <div class="col-span-2 text-end text-sm font-bold text-phoenix-600 dark:text-phoenix-400" x-text="formatPrice(item.price * item.quantity)"></div>

                                    {{-- Remove --}}
                                    <div class="col-span-1 flex justify-end">
                                        <button
                                            @click="confirmRemove(index)"
                                            class="p-[8px] text-slate-400 hover:text-coral rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                            :aria-label="'{{ __('phonix::app.cart.remove') }}'"
                                        >
                                            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Mobile Card --}}
                                <div class="md:hidden">
                                    <div class="flex gap-[12px]">
                                        <div class="w-[72px] h-[72px] flex-shrink-0 rounded-md bg-slate-100 dark:bg-dark-card overflow-hidden border border-slate-200 dark:border-dark-border">
                                            <template x-if="item.image">
                                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" loading="lazy" />
                                            </template>
                                            <template x-if="!item.image">
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-[32px] h-[32px] text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                                                    </svg>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="item.name"></h3>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-[2px]" x-text="item.variant"></p>
                                                </div>
                                                <button
                                                    @click="confirmRemove(index)"
                                                    class="p-[4px] text-slate-400 hover:text-coral transition-colors"
                                                    :aria-label="'{{ __('phonix::app.cart.remove') }}'"
                                                >
                                                    <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="flex items-center justify-between mt-[12px]">
                                                <div class="inline-flex items-center border border-slate-200 dark:border-dark-border rounded">
                                                    <button @click="decrementQty(index)" class="w-[32px] h-[32px] flex items-center justify-center text-slate-500 hover:text-phoenix-600 transition-colors" :aria-label="'Decrease quantity'">
                                                        <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M5 12h14"/></svg>
                                                    </button>
                                                    <span class="w-[36px] text-center text-sm font-medium text-slate-800 dark:text-slate-200" x-text="item.quantity"></span>
                                                    <button @click="incrementQty(index)" class="w-[32px] h-[32px] flex items-center justify-center text-slate-500 hover:text-phoenix-600 transition-colors" :aria-label="'Increase quantity'">
                                                        <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                                                    </button>
                                                </div>
                                                <span class="text-sm font-bold text-phoenix-600 dark:text-phoenix-400" x-text="formatPrice(item.price * item.quantity)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Continue Shopping --}}
                        <div class="pt-[16px]">
                            <a href="/" class="inline-flex items-center gap-[8px] text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors">
                                <svg class="w-[16px] h-[16px] ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                @lang('phonix::app.cart.continue_shopping')
                            </a>
                        </div>
                    </div>

                    {{-- Order Summary Sidebar --}}
                    <div class="lg:col-span-1" data-gsap="fade-up">
                        <div class="card-phoenix p-[24px] lg:sticky lg:top-[100px] space-y-[20px]">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">@lang('phonix::app.cart.summary')</h2>

                            {{-- Free Shipping Progress --}}
                            <div class="p-[12px] rounded-md bg-phoenix-50 dark:bg-phoenix-900/20 border border-phoenix-100 dark:border-phoenix-800">
                                <div class="flex items-center gap-[8px] mb-[8px]">
                                    <svg class="w-[16px] h-[16px] text-phoenix-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.079-.504 1.079-1.125V11.25M18 1.5l3 3.75M18 1.5l-3 3.75m3-3.75V11.25"/>
                                    </svg>
                                    <p class="text-xs font-medium text-phoenix-700 dark:text-phoenix-300" x-show="freeShippingRemaining > 0">
                                        @lang('phonix::app.cart.free_shipping_message', ['amount' => '']) <span x-text="formatPrice(freeShippingRemaining)"></span>
                                    </p>
                                    <p class="text-xs font-medium text-phoenix-700 dark:text-phoenix-300" x-show="freeShippingRemaining <= 0">
                                        @lang('phonix::app.product.free_shipping')
                                    </p>
                                </div>
                                <div class="w-full bg-phoenix-100 dark:bg-phoenix-800 rounded-full h-[6px] overflow-hidden">
                                    <div class="h-full bg-phoenix-500 dark:bg-phoenix-400 rounded-full transition-all duration-500 ease-phoenix" :style="'width:' + Math.min(100, (subtotal / freeShippingThreshold) * 100) + '%'"></div>
                                </div>
                            </div>

                            {{-- Coupon Code --}}
                            <div>
                                <label for="coupon-code" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[8px]">
                                    @lang('phonix::app.cart.coupon.title')
                                </label>
                                <template x-if="!couponApplied">
                                    <div class="flex gap-[8px]">
                                        <input
                                            type="text"
                                            id="coupon-code"
                                            x-model="couponCode"
                                            placeholder="@lang('phonix::app.cart.coupon.placeholder')"
                                            class="input-phoenix flex-1 py-[10px]"
                                        />
                                        <button
                                            @click="applyCoupon()"
                                            class="btn-phoenix-outline px-[16px] py-[10px] text-sm whitespace-nowrap"
                                        >
                                            @lang('phonix::app.cart.coupon.apply')
                                        </button>
                                    </div>
                                </template>
                                <template x-if="couponApplied">
                                    <div class="flex items-center justify-between p-[10px] rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                                        <div class="flex items-center gap-[8px]">
                                            <svg class="w-[16px] h-[16px] text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span class="text-sm font-medium text-green-700 dark:text-green-300" x-text="couponCode"></span>
                                        </div>
                                        <button
                                            @click="removeCoupon()"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors"
                                        >
                                            @lang('phonix::app.cart.coupon.remove')
                                        </button>
                                    </div>
                                </template>
                            </div>

                            {{-- Totals --}}
                            <div class="space-y-[12px] pt-[8px] border-t border-slate-200 dark:border-dark-border">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.subtotal')</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200" x-text="formatPrice(subtotal)"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.shipping')</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200" x-text="shippingCost === 0 ? '{{ __('phonix::app.checkout.shipping.free') }}' : formatPrice(shippingCost)"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.tax')</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200" x-text="formatPrice(tax)"></span>
                                </div>
                                <template x-if="couponApplied">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-green-600 dark:text-green-400">@lang('phonix::app.cart.discount')</span>
                                        <span class="font-medium text-green-600 dark:text-green-400" x-text="'-' + formatPrice(discountAmount)"></span>
                                    </div>
                                </template>
                            </div>

                            {{-- Grand Total --}}
                            <div class="flex justify-between items-center pt-[12px] border-t-2 border-slate-200 dark:border-dark-border">
                                <span class="text-base font-bold text-slate-900 dark:text-white">@lang('phonix::app.cart.grand_total')</span>
                                <span class="text-xl font-bold text-phoenix-500 dark:text-phoenix-400" x-text="formatPrice(grandTotal)"></span>
                            </div>

                            {{-- Checkout Button --}}
                            <a
                                href="{{ route('phonix.checkout.index') }}"
                                class="btn-phoenix w-full justify-center py-[14px] text-base font-semibold"
                            >
                                @lang('phonix::app.cart.proceed_to_checkout')
                            </a>

                            {{-- Payment Icons --}}
                            <div class="flex items-center justify-center gap-[12px] pt-[8px]">
                                <span class="text-xs text-slate-400 dark:text-slate-500">@lang('phonix::app.footer.payment.secure_payment')</span>
                                <div class="flex gap-[8px]">
                                    {{-- Visa --}}
                                    <div class="w-[36px] h-[24px] rounded border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-card flex items-center justify-center text-[10px] font-bold text-blue-700">VISA</div>
                                    {{-- MC --}}
                                    <div class="w-[36px] h-[24px] rounded border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-card flex items-center justify-center text-[10px] font-bold text-red-600">MC</div>
                                    {{-- Amex --}}
                                    <div class="w-[36px] h-[24px] rounded border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-card flex items-center justify-center text-[10px] font-bold text-blue-500">AMEX</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @pushOnce('scripts')
    <script>
        function cartPage() {
            return {
                items: @json($cartItems),
                couponCode: @json($cartTotals['coupon'] ?? ''),
                couponApplied: {{ ! empty($cartTotals['coupon']) ? 'true' : 'false' }},
                discountAmount: {{ $cartTotals['discount'] }},
                serverShipping: {{ $cartTotals['shipping'] }},
                serverTax: {{ $cartTotals['tax'] }},
                currency: @json(core()->getCurrentCurrencyCode()),
                freeShippingThreshold: 10000,

                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get shippingCost() {
                    return this.serverShipping;
                },

                get tax() {
                    return this.serverTax;
                },

                get freeShippingRemaining() {
                    return Math.max(0, this.freeShippingThreshold - this.subtotal);
                },

                get grandTotal() {
                    return this.subtotal + this.shippingCost + this.tax - (this.couponApplied ? this.discountAmount : 0);
                },

                formatPrice(amount) {
                    try {
                        return new Intl.NumberFormat(undefined, { style: 'currency', currency: this.currency, minimumFractionDigits: 0 }).format(amount);
                    } catch (e) {
                        return amount.toFixed(2) + ' ' + this.currency;
                    }
                },

                incrementQty(index) {
                    if (this.items[index].quantity < 99) this.items[index].quantity++;
                },

                decrementQty(index) {
                    if (this.items[index].quantity > 1) this.items[index].quantity--;
                },

                confirmRemove(index) {
                    if (confirm('{{ __('phonix::app.messages.confirm.remove_item') }}')) {
                        this.items.splice(index, 1);
                    }
                },

                applyCoupon() {
                    if (this.couponCode.trim() !== '') {
                        this.couponApplied = true;
                    }
                },

                removeCoupon() {
                    this.couponApplied = false;
                    this.couponCode = '';
                },
            };
        }
    </script>
    @endPushOnce

</x-phonix::layouts.index>
