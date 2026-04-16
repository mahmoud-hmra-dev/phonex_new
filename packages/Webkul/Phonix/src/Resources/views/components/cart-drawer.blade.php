{{-- Cart Drawer — Slide-in panel from right (left on RTL) --}}
<div
    x-data="cartDrawer()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[60]"
    role="dialog"
    aria-modal="true"
    :aria-label="'{{ __('phonix::app.header.cart.title') }}'"
    @keydown.escape.window="close()"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-black/50 backdrop-blur-xs"
        @click="close()"
        aria-hidden="true"
    ></div>

    {{-- Drawer Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="ltr:translate-x-full rtl:-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="ltr:translate-x-full rtl:-translate-x-full"
        x-trap.inert.noscroll="open"
        class="absolute top-0 ltr:right-0 rtl:left-0 h-full w-full max-w-[420px] bg-white dark:bg-dark-surface shadow-modal flex flex-col"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-[24px] py-[20px] border-b border-slate-200 dark:border-dark-border">
            <div class="flex items-center gap-[12px]">
                <h2 class="text-fluid-lg font-semibold text-slate-900 dark:text-white">
                    @lang('phonix::app.header.cart.title')
                </h2>
                <span class="inline-flex items-center justify-center min-w-[24px] h-[24px] px-[6px] text-xs font-bold text-white rounded-full bg-phoenix-500 dark:bg-phoenix-400 dark:text-phoenix-950">
                    <span x-text="items.length">0</span>
                </span>
            </div>
            <button
                @click="close()"
                class="p-[8px] rounded-md text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-dark-card transition-colors duration-200"
                :aria-label="'{{ __('phonix::app.general.close') }}'"
            >
                <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto scrollbar-thin px-[24px] py-[16px]">
            {{-- Empty State --}}
            <template x-if="items.length === 0">
                <div class="flex flex-col items-center justify-center h-full text-center py-[48px]">
                    <svg class="w-[64px] h-[64px] text-slate-300 dark:text-slate-600 mb-[24px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    <p class="text-slate-500 dark:text-slate-400 font-medium mb-[8px]">
                        @lang('phonix::app.header.cart.empty')
                    </p>
                    <a
                        href="/"
                        class="btn-phoenix-ghost text-sm mt-[16px]"
                        @click="close()"
                    >
                        @lang('phonix::app.cart.continue_shopping')
                    </a>
                </div>
            </template>

            {{-- Items List --}}
            <template x-if="items.length > 0">
                <ul class="space-y-[16px]" role="list">
                    <template x-for="(item, index) in items" :key="index">
                        <li
                            class="flex gap-[16px] p-[12px] rounded-md border border-slate-100 dark:border-dark-border bg-slate-50/50 dark:bg-dark-card/50 transition-all duration-200"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 ltr:translate-x-8 rtl:-translate-x-8"
                        >
                            {{-- Thumbnail --}}
                            <div class="w-[72px] h-[72px] flex-shrink-0 rounded bg-slate-100 dark:bg-dark-card overflow-hidden flex items-center justify-center">
                                <svg class="w-[32px] h-[32px] text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                                </svg>
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate" x-text="item.name"></h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-[2px]" x-text="item.variant"></p>

                                {{-- Quantity & Price --}}
                                <div class="flex items-center justify-between mt-[10px]">
                                    {{-- Quantity Selector --}}
                                    <div class="flex items-center border border-slate-200 dark:border-dark-border rounded">
                                        <button
                                            @click="decrementQty(index)"
                                            class="w-[28px] h-[28px] flex items-center justify-center text-slate-500 hover:text-phoenix-600 hover:bg-phoenix-50 dark:text-slate-400 dark:hover:text-phoenix-300 dark:hover:bg-dark-card transition-colors"
                                            :aria-label="'Decrease quantity'"
                                        >
                                            <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M5 12h14"/></svg>
                                        </button>
                                        <span class="w-[32px] text-center text-sm font-medium text-slate-800 dark:text-slate-200" x-text="item.quantity"></span>
                                        <button
                                            @click="incrementQty(index)"
                                            class="w-[28px] h-[28px] flex items-center justify-center text-slate-500 hover:text-phoenix-600 hover:bg-phoenix-50 dark:text-slate-400 dark:hover:text-phoenix-300 dark:hover:bg-dark-card transition-colors"
                                            :aria-label="'Increase quantity'"
                                        >
                                            <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                                        </button>
                                    </div>

                                    {{-- Price --}}
                                    <span class="text-sm font-bold text-phoenix-600 dark:text-phoenix-400" x-text="formatPrice(item.price * item.quantity)"></span>
                                </div>
                            </div>

                            {{-- Remove --}}
                            <button
                                @click="removeItem(index)"
                                class="self-start p-[4px] text-slate-400 hover:text-coral transition-colors"
                                :aria-label="'{{ __('phonix::app.cart.remove') }}'"
                            >
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                </svg>
                            </button>
                        </li>
                    </template>
                </ul>
            </template>
        </div>

        {{-- Footer --}}
        <template x-if="items.length > 0">
            <div class="border-t border-slate-200 dark:border-dark-border px-[24px] py-[20px] space-y-[16px]">
                {{-- Subtotal --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 dark:text-slate-400">@lang('phonix::app.header.cart.subtotal')</span>
                    <span class="text-lg font-bold text-slate-900 dark:text-white" x-text="formatPrice(subtotal)"></span>
                </div>

                {{-- Buttons --}}
                <div class="space-y-[8px]">
                    <a
                        href="{{ route('phonix.cart.index') }}"
                        class="btn-phoenix-outline w-full justify-center px-[24px] py-[12px] text-sm"
                        @click="close()"
                    >
                        @lang('phonix::app.header.cart.view_cart')
                    </a>
                    <a
                        href="{{ route('phonix.checkout.index') }}"
                        class="btn-phoenix w-full justify-center px-[24px] py-[12px] text-sm"
                        @click="close()"
                    >
                        @lang('phonix::app.header.cart.checkout')
                    </a>
                </div>
            </div>
        </template>
    </div>
</div>

@pushOnce('scripts')
<script>
    function cartDrawer() {
        return {
            open: false,
            items: [
                { name: 'iPhone 15 Pro Max', variant: '256GB / Natural Titanium', price: 4999, quantity: 1 },
                { name: 'AirPods Pro 2', variant: 'USB-C', price: 899, quantity: 2 },
                { name: 'Samsung Galaxy S24 Ultra', variant: '512GB / Titanium Gray', price: 4499, quantity: 1 },
            ],

            get subtotal() {
                return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },

            formatPrice(amount) {
                return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR', minimumFractionDigits: 0 }).format(amount);
            },

            incrementQty(index) {
                if (this.items[index].quantity < 99) {
                    this.items[index].quantity++;
                }
            },

            decrementQty(index) {
                if (this.items[index].quantity > 1) {
                    this.items[index].quantity--;
                }
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            toggle() {
                this.open = !this.open;
            },

            close() {
                this.open = false;
            },
        };
    }
</script>
@endPushOnce
