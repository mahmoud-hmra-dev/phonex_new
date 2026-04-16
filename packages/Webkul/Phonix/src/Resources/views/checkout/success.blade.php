<x-phonix::layouts.index :title="__('phonix::app.checkout.success.title')">

    <div class="container mx-auto section-padding">
        <div class="max-w-xl mx-auto text-center" data-gsap="fade-up">

            {{-- Checkmark Animation --}}
            <div class="relative mx-auto w-[120px] h-[120px] mb-[32px]">
                <div class="absolute inset-0 rounded-full bg-green-100 dark:bg-green-900/30 animate-ping opacity-20"></div>
                <div
                    x-data="{ show: false }"
                    x-init="setTimeout(() => show = true, 200)"
                    class="relative w-[120px] h-[120px] rounded-full bg-green-500 dark:bg-green-600 flex items-center justify-center shadow-lg transition-transform duration-500"
                    :class="show ? 'scale-100' : 'scale-0'"
                >
                    <svg
                        class="w-[56px] h-[56px] text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="3"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                            class="transition-all duration-700 delay-500"
                            x-bind:style="show ? 'stroke-dasharray: 30; stroke-dashoffset: 0;' : 'stroke-dasharray: 30; stroke-dashoffset: 30;'"
                        />
                    </svg>
                </div>
            </div>

            {{-- Title --}}
            <h1 class="text-fluid-2xl font-bold text-slate-900 dark:text-white mb-[12px]">
                @lang('phonix::app.checkout.success.title')
            </h1>

            {{-- Message --}}
            <p class="text-slate-500 dark:text-slate-400 mb-[32px] max-w-md mx-auto">
                @lang('phonix::app.checkout.success.message')
            </p>

            {{-- Order Number --}}
            <div class="inline-flex items-center gap-[12px] px-[24px] py-[16px] rounded-lg bg-phoenix-50 dark:bg-phoenix-900/20 border border-phoenix-100 dark:border-phoenix-800 mb-[32px]">
                <span class="text-sm font-medium text-slate-600 dark:text-slate-400">@lang('phonix::app.checkout.success.order_number'):</span>
                <span class="text-lg font-bold text-phoenix-600 dark:text-phoenix-400">#PHX-{{ rand(100000, 999999) }}</span>
            </div>

            {{-- Order Summary Card --}}
            <div class="card-phoenix p-[24px] mb-[32px] text-start" data-gsap="fade-up">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-[16px]">@lang('phonix::app.cart.summary')</h2>

                @php
                    $orderItems = [
                        ['name' => 'iPhone 15 Pro Max', 'variant' => '256GB / Natural Titanium', 'price' => 4999, 'qty' => 1],
                        ['name' => 'AirPods Pro 2', 'variant' => 'USB-C', 'price' => 899, 'qty' => 2],
                        ['name' => 'Samsung Galaxy S24 Ultra', 'variant' => '512GB / Titanium Gray', 'price' => 4499, 'qty' => 1],
                        ['name' => 'Anker PowerCore 20000', 'variant' => 'Black', 'price' => 199, 'qty' => 1],
                    ];
                    $subtotal = collect($orderItems)->sum(fn($i) => $i['price'] * $i['qty']);
                    $tax = round($subtotal * 0.15);
                    $total = $subtotal + $tax;
                @endphp

                <div class="space-y-[10px] mb-[16px]">
                    @foreach ($orderItems as $item)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $item['name'] }}</span>
                                <span class="text-slate-400 dark:text-slate-500"> x{{ $item['qty'] }}</span>
                            </div>
                            <span class="font-medium text-slate-700 dark:text-slate-300 ms-[16px]">SAR {{ number_format($item['price'] * $item['qty']) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-[8px] pt-[12px] border-t border-slate-200 dark:border-dark-border text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.subtotal')</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">SAR {{ number_format($subtotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.shipping')</span>
                        <span class="font-medium text-green-600 dark:text-green-400">@lang('phonix::app.checkout.shipping.free')</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.tax') (15%)</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">SAR {{ number_format($tax) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-[8px] border-t-2 border-slate-200 dark:border-dark-border">
                        <span class="font-bold text-slate-900 dark:text-white">@lang('phonix::app.cart.grand_total')</span>
                        <span class="text-lg font-bold text-phoenix-500 dark:text-phoenix-400">SAR {{ number_format($total) }}</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-[16px]" data-gsap="fade-up">
                <x-phonix::button variant="primary" size="lg" href="#">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    @lang('phonix::app.checkout.success.track_order')
                </x-phonix::button>
                <x-phonix::button variant="outline" size="lg" href="/">
                    @lang('phonix::app.checkout.success.continue_shopping')
                </x-phonix::button>
            </div>
        </div>
    </div>

</x-phonix::layouts.index>
