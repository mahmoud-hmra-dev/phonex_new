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
                'id'       => $item->id,
                'name'     => $item->name,
                'variant'  => implode(' / ', array_filter($variantLabels)),
                'price'    => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'total'    => (float) $item->total,
                'image'    => $imageData['small_image_url'] ?? null,
            ];
        })->values()->all()
        : [];

    $cartSubTotal   = $cart ? (float) ($cart->sub_total ?? 0) : 0;
    $cartGrandTotal = $cart ? (float) ($cart->grand_total ?? 0) : 0;
    $cartTax        = $cart ? (float) ($cart->tax_total ?? 0) : 0;
    $cartDiscount   = $cart ? (float) ($cart->discount_amount ?? 0) : 0;
    $cartShipping   = $cart ? (float) ($cart->shipping_amount ?? 0) : 0;
    $isGuest        = ! auth()->guard('customer')->check();
    $customer       = auth()->guard('customer')->user();
@endphp

<x-phonix::layouts.index
    :title="__('phonix::app.checkout.title')"
    :hasFooter="false"
>

{{-- =====================================================================
     CHECKOUT PAGE  —  Alpine.js multi-step, Bagisto API-driven
====================================================================== --}}
<div
    x-data="phonixCheckout()"
    x-init="init()"
    class="min-h-screen bg-slate-50 dark:bg-dark-bg"
>

    {{-- ── MINIMAL CHECKOUT HEADER ──────────────────────────────────── --}}
    <header class="bg-white dark:bg-dark-card border-b border-slate-200 dark:border-dark-border sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-[64px] flex items-center justify-between">

            {{-- Logo --}}
            <a
                href="{{ route('phonix.home') }}"
                class="flex items-center gap-[10px] group"
                :aria-label="'{{ __('phonix::app.general.home') }}'"
            >
                @if (core()->getCurrentChannel()->logo_url)
                    <img
                        src="{{ core()->getCurrentChannel()->logo_url }}"
                        alt="{{ config('app.name') }}"
                        class="h-[32px] w-auto object-contain"
                        width="120"
                        height="32"
                    />
                @else
                    <span class="text-xl font-bold text-phoenix-600 dark:text-phoenix-400 tracking-tight">
                        {{ config('app.name') }}
                    </span>
                @endif
            </a>

            {{-- Secure Checkout badge --}}
            <div class="flex items-center gap-[8px] text-sm font-medium text-slate-600 dark:text-slate-400">
                <svg class="w-[18px] h-[18px] text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
                <span class="hidden sm:inline">@lang('phonix::app.checkout.secure_checkout')</span>
            </div>

            {{-- Breadcrumb --}}
            <nav aria-label="@lang('phonix::app.general.breadcrumb')" class="hidden md:flex">
                <ol class="flex items-center gap-[6px] text-xs text-slate-400 dark:text-slate-500">
                    <li>
                        <a href="{{ route('phonix.home') }}" class="hover:text-phoenix-500 transition-colors">
                            @lang('phonix::app.general.home')
                        </a>
                    </li>
                    <li aria-hidden="true">
                        <svg class="w-[12px] h-[12px] ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </li>
                    <li>
                        <a href="{{ route('phonix.cart.index') }}" class="hover:text-phoenix-500 transition-colors">
                            @lang('phonix::app.cart.title')
                        </a>
                    </li>
                    <li aria-hidden="true">
                        <svg class="w-[12px] h-[12px] ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </li>
                    <li class="text-slate-700 dark:text-slate-300 font-medium" aria-current="page">
                        @lang('phonix::app.checkout.title')
                    </li>
                </ol>
            </nav>
        </div>
    </header>

    {{-- ── GLOBAL ERROR TOAST ───────────────────────────────────────── --}}
    <div
        x-show="toast.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        :class="toast.type === 'error' ? 'bg-red-600' : 'bg-emerald-600'"
        class="fixed top-[72px] inset-x-0 z-50 flex justify-center pointer-events-none px-4"
        role="alert"
        aria-live="assertive"
    >
        <div class="pointer-events-auto flex items-center gap-[10px] text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium max-w-lg w-full">
            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            <span x-text="toast.message" class="flex-1"></span>
            <button @click="toast.show = false" class="text-white/80 hover:text-white ms-2" aria-label="@lang('phonix::app.general.close')">
                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- ── PAGE BODY ─────────────────────────────────────────────────── --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-[32px] lg:py-[48px]">

        {{-- ── STEP INDICATOR ───────────────────────────────────────── --}}
        <div
            class="mb-[40px]"
            role="navigation"
            aria-label="@lang('phonix::app.checkout.steps')"
            data-gsap="fade-up"
        >
            <div class="flex items-center justify-center max-w-2xl mx-auto">

                {{-- Step 1: Shipping --}}
                <div class="flex items-center">
                    <div class="flex flex-col items-center">
                        <button
                            @click="currentStep > 1 ? goToStep(1) : null"
                            class="w-[44px] h-[44px] rounded-full flex items-center justify-center border-2 transition-all duration-300 font-semibold text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-phoenix-400 focus-visible:ring-offset-2"
                            :class="{
                                'bg-phoenix-500 border-phoenix-500 text-white dark:bg-phoenix-500 dark:border-phoenix-500 cursor-pointer': currentStep > 1,
                                'bg-phoenix-500 border-phoenix-500 text-white ring-4 ring-phoenix-200 dark:ring-phoenix-900/50': currentStep === 1,
                                'bg-white dark:bg-dark-surface border-slate-300 dark:border-dark-border text-slate-400 dark:text-slate-500 cursor-not-allowed': currentStep < 1
                            }"
                            :aria-current="currentStep === 1 ? 'step' : false"
                            :aria-label="'{{ __('phonix::app.checkout.step.shipping') }}'"
                            :disabled="currentStep < 1"
                        >
                            <template x-if="currentStep > 1">
                                <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="currentStep <= 1">
                                <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17l4-4 4 4m0-6l-4-4-4 4"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 6a2 2 0 012-2h14a2 2 0 012 2M3 6l2 12a2 2 0 002 2h10a2 2 0 002-2l2-12"/></svg>
                            </template>
                        </button>
                        <span
                            class="mt-[6px] text-[11px] font-medium whitespace-nowrap transition-colors duration-300"
                            :class="currentStep >= 1 ? 'text-phoenix-600 dark:text-phoenix-400' : 'text-slate-400 dark:text-slate-500'"
                        >@lang('phonix::app.checkout.step.shipping')</span>
                    </div>

                    {{-- Connector --}}
                    <div
                        class="w-[60px] sm:w-[100px] lg:w-[140px] h-[2px] mx-[8px] mb-[20px] rounded-full transition-colors duration-500"
                        :class="currentStep > 1 ? 'bg-phoenix-500' : 'bg-slate-200 dark:bg-dark-border'"
                        aria-hidden="true"
                    ></div>
                </div>

                {{-- Step 2: Payment --}}
                <div class="flex items-center">
                    <div class="flex flex-col items-center">
                        <button
                            @click="currentStep > 2 ? goToStep(2) : null"
                            class="w-[44px] h-[44px] rounded-full flex items-center justify-center border-2 transition-all duration-300 font-semibold text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-phoenix-400 focus-visible:ring-offset-2"
                            :class="{
                                'bg-phoenix-500 border-phoenix-500 text-white dark:bg-phoenix-500 dark:border-phoenix-500 cursor-pointer': currentStep > 2,
                                'bg-phoenix-500 border-phoenix-500 text-white ring-4 ring-phoenix-200 dark:ring-phoenix-900/50': currentStep === 2,
                                'bg-white dark:bg-dark-surface border-slate-300 dark:border-dark-border text-slate-400 dark:text-slate-500 cursor-not-allowed': currentStep < 2
                            }"
                            :aria-current="currentStep === 2 ? 'step' : false"
                            :aria-label="'{{ __('phonix::app.checkout.step.payment') }}'"
                            :disabled="currentStep < 2"
                        >
                            <template x-if="currentStep > 2">
                                <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="currentStep <= 2">
                                <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </template>
                        </button>
                        <span
                            class="mt-[6px] text-[11px] font-medium whitespace-nowrap transition-colors duration-300"
                            :class="currentStep >= 2 ? 'text-phoenix-600 dark:text-phoenix-400' : 'text-slate-400 dark:text-slate-500'"
                        >@lang('phonix::app.checkout.step.payment')</span>
                    </div>

                    {{-- Connector --}}
                    <div
                        class="w-[60px] sm:w-[100px] lg:w-[140px] h-[2px] mx-[8px] mb-[20px] rounded-full transition-colors duration-500"
                        :class="currentStep > 2 ? 'bg-phoenix-500' : 'bg-slate-200 dark:bg-dark-border'"
                        aria-hidden="true"
                    ></div>
                </div>

                {{-- Step 3: Review --}}
                <div class="flex flex-col items-center">
                    <button
                        class="w-[44px] h-[44px] rounded-full flex items-center justify-center border-2 transition-all duration-300 font-semibold text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-phoenix-400 focus-visible:ring-offset-2 cursor-not-allowed"
                        :class="{
                            'bg-phoenix-500 border-phoenix-500 text-white ring-4 ring-phoenix-200 dark:ring-phoenix-900/50': currentStep === 3,
                            'bg-white dark:bg-dark-surface border-slate-300 dark:border-dark-border text-slate-400 dark:text-slate-500': currentStep < 3
                        }"
                        :aria-current="currentStep === 3 ? 'step' : false"
                        :aria-label="'{{ __('phonix::app.checkout.step.review') }}'"
                        disabled
                    >
                        <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </button>
                    <span
                        class="mt-[6px] text-[11px] font-medium whitespace-nowrap transition-colors duration-300"
                        :class="currentStep >= 3 ? 'text-phoenix-600 dark:text-phoenix-400' : 'text-slate-400 dark:text-slate-500'"
                    >@lang('phonix::app.checkout.step.review')</span>
                </div>

            </div>
        </div>

        {{-- ── TWO-COLUMN GRID ───────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-[24px] xl:gap-[32px]">

            {{-- ═══════════════════════════════════════════════════════
                 LEFT — STEPS AREA  (3 of 5 columns)
            ═══════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-3 space-y-[0px]">

                {{-- ── STEP 1: SHIPPING ADDRESS ──────────────────── --}}
                <div
                    x-show="currentStep === 1"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-[16px]"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-[8px]"
                    data-gsap="fade-up"
                >
                    <div class="card-phoenix p-[24px] md:p-[32px]">

                        {{-- Section heading --}}
                        <div class="flex items-center gap-[12px] mb-[28px]">
                            <div class="w-[40px] h-[40px] rounded-xl bg-phoenix-100 dark:bg-phoenix-900/30 flex items-center justify-center flex-shrink-0">
                                <svg class="w-[20px] h-[20px] text-phoenix-600 dark:text-phoenix-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white leading-tight">
                                    @lang('phonix::app.checkout.address.title')
                                </h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-[2px]">
                                    @lang('phonix::app.checkout.address.subtitle')
                                </p>
                            </div>
                        </div>

                        {{-- Saved addresses (logged-in customers only) --}}
                        @auth('customer')
                        <div x-show="savedAddresses.length > 0 && !showNewAddressForm">

                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-[14px]">
                                @lang('phonix::app.checkout.address.saved_addresses')
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[12px] mb-[20px]">
                                <template x-for="addr in savedAddresses" :key="addr.id">
                                    <label
                                        class="relative block cursor-pointer rounded-xl border-2 p-[16px] transition-all duration-200"
                                        :class="selectedAddressId === addr.id
                                            ? 'border-phoenix-500 dark:border-phoenix-400 bg-phoenix-50/60 dark:bg-phoenix-900/20 shadow-sm'
                                            : 'border-slate-200 dark:border-dark-border hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-dark-card'"
                                    >
                                        <input
                                            type="radio"
                                            name="saved_address"
                                            :value="addr.id"
                                            x-model="selectedAddressId"
                                            class="sr-only"
                                        />
                                        {{-- Selected indicator --}}
                                        <div
                                            x-show="selectedAddressId === addr.id"
                                            class="absolute top-[12px] end-[12px] w-[20px] h-[20px] rounded-full bg-phoenix-500 flex items-center justify-center"
                                            aria-hidden="true"
                                        >
                                            <svg class="w-[12px] h-[12px] text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>

                                        <div class="flex items-start gap-[10px]">
                                            <svg class="w-[18px] h-[18px] text-slate-400 dark:text-slate-500 flex-shrink-0 mt-[1px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                                            <div class="min-w-0 flex-1 pe-[24px]">
                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="addr.first_name + ' ' + addr.last_name"></p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-[2px] leading-relaxed" x-text="(addr.address ? addr.address.join(', ') + ', ' : '') + addr.city + ', ' + addr.state + ' ' + addr.postcode + ', ' + addr.country"></p>
                                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-[2px]" x-text="addr.phone"></p>
                                            </div>
                                        </div>
                                    </label>
                                </template>

                                {{-- Add new address card --}}
                                <button
                                    type="button"
                                    @click="showNewAddressForm = true; selectedAddressId = null"
                                    class="flex items-center justify-center gap-[10px] rounded-xl border-2 border-dashed border-slate-300 dark:border-dark-border hover:border-phoenix-400 dark:hover:border-phoenix-500 p-[16px] text-slate-500 dark:text-slate-400 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-all duration-200 min-h-[80px] bg-white dark:bg-dark-card"
                                >
                                    <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    <span class="text-sm font-medium">@lang('phonix::app.checkout.address.add_new')</span>
                                </button>
                            </div>

                            {{-- Use selected saved address --}}
                            <div class="flex justify-between items-center mt-[24px]">
                                <a
                                    href="{{ route('phonix.cart.index') }}"
                                    class="inline-flex items-center gap-[6px] text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
                                >
                                    <svg class="w-[14px] h-[14px] ltr:rotate-180 rtl:rotate-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    @lang('phonix::app.cart.title')
                                </a>
                                <button
                                    type="button"
                                    @click="submitSavedAddress()"
                                    :disabled="!selectedAddressId || step1Loading"
                                    class="btn-phoenix px-[28px] py-[12px] flex items-center gap-[8px]"
                                    :class="(!selectedAddressId || step1Loading) ? 'opacity-60 cursor-not-allowed' : ''"
                                >
                                    <span x-show="step1Loading" class="w-[16px] h-[16px] border-2 border-white/40 border-t-white rounded-full animate-spin" aria-hidden="true"></span>
                                    <span>@lang('phonix::app.checkout.address.proceed')</span>
                                    <svg x-show="!step1Loading" class="w-[16px] h-[16px] ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                        @endauth

                        {{-- New Address Form --}}
                        <div x-show="showNewAddressForm || savedAddresses.length === 0 || !{{ auth()->guard('customer')->check() ? 'true' : 'false' }}">

                            @auth('customer')
                            <div x-show="savedAddresses.length > 0" class="mb-[20px]">
                                <button
                                    type="button"
                                    @click="showNewAddressForm = false"
                                    class="inline-flex items-center gap-[6px] text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline"
                                >
                                    <svg class="w-[14px] h-[14px] ltr:rotate-180 rtl:rotate-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    @lang('phonix::app.checkout.address.back_to_saved')
                                </button>
                            </div>
                            @endauth

                            <form
                                @submit.prevent="submitNewAddress()"
                                novalidate
                                id="address-form"
                            >
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-[16px]">

                                    {{-- First Name --}}
                                    <div>
                                        <label for="billing_first_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                            @lang('phonix::app.checkout.form.first_name') <span class="text-coral" aria-hidden="true">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="billing_first_name"
                                            name="billing[first_name]"
                                            x-model="form.first_name"
                                            @blur="touchField('first_name')"
                                            class="input-phoenix"
                                            :class="fieldError('first_name') ? 'border-coral focus:border-coral focus:ring-coral/20' : ''"
                                            required
                                            autocomplete="given-name"
                                            :aria-invalid="!!fieldError('first_name')"
                                            aria-describedby="first_name-err"
                                        />
                                        <p x-show="fieldError('first_name')" x-text="fieldError('first_name')" id="first_name-err" class="mt-[5px] text-xs text-coral" role="alert"></p>
                                    </div>

                                    {{-- Last Name --}}
                                    <div>
                                        <label for="billing_last_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                            @lang('phonix::app.checkout.form.last_name') <span class="text-coral" aria-hidden="true">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="billing_last_name"
                                            name="billing[last_name]"
                                            x-model="form.last_name"
                                            @blur="touchField('last_name')"
                                            class="input-phoenix"
                                            :class="fieldError('last_name') ? 'border-coral focus:border-coral focus:ring-coral/20' : ''"
                                            required
                                            autocomplete="family-name"
                                            :aria-invalid="!!fieldError('last_name')"
                                            aria-describedby="last_name-err"
                                        />
                                        <p x-show="fieldError('last_name')" x-text="fieldError('last_name')" id="last_name-err" class="mt-[5px] text-xs text-coral" role="alert"></p>
                                    </div>

                                    {{-- Email --}}
                                    <div>
                                        <label for="billing_email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                            @lang('phonix::app.checkout.form.email') <span class="text-coral" aria-hidden="true">*</span>
                                        </label>
                                        <input
                                            type="email"
                                            id="billing_email"
                                            name="billing[email]"
                                            x-model="form.email"
                                            @blur="touchField('email')"
                                            class="input-phoenix"
                                            :class="fieldError('email') ? 'border-coral focus:border-coral focus:ring-coral/20' : ''"
                                            required
                                            autocomplete="email"
                                            :aria-invalid="!!fieldError('email')"
                                            aria-describedby="email-err"
                                        />
                                        <p x-show="fieldError('email')" x-text="fieldError('email')" id="email-err" class="mt-[5px] text-xs text-coral" role="alert"></p>
                                    </div>

                                    {{-- Phone --}}
                                    <div>
                                        <label for="billing_phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                            @lang('phonix::app.checkout.form.phone') <span class="text-coral" aria-hidden="true">*</span>
                                        </label>
                                        <input
                                            type="tel"
                                            id="billing_phone"
                                            name="billing[phone]"
                                            x-model="form.phone"
                                            @blur="touchField('phone')"
                                            class="input-phoenix"
                                            :class="fieldError('phone') ? 'border-coral focus:border-coral focus:ring-coral/20' : ''"
                                            required
                                            autocomplete="tel"
                                            :aria-invalid="!!fieldError('phone')"
                                            aria-describedby="phone-err"
                                        />
                                        <p x-show="fieldError('phone')" x-text="fieldError('phone')" id="phone-err" class="mt-[5px] text-xs text-coral" role="alert"></p>
                                    </div>

                                    {{-- Address Line 1 --}}
                                    <div class="sm:col-span-2">
                                        <label for="billing_address1" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                            @lang('phonix::app.checkout.form.address1') <span class="text-coral" aria-hidden="true">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="billing_address1"
                                            name="billing[address][0]"
                                            x-model="form.address1"
                                            @blur="touchField('address1')"
                                            class="input-phoenix"
                                            :class="fieldError('address1') ? 'border-coral focus:border-coral focus:ring-coral/20' : ''"
                                            required
                                            autocomplete="address-line1"
                                            :aria-invalid="!!fieldError('address1')"
                                            aria-describedby="address1-err"
                                        />
                                        <p x-show="fieldError('address1')" x-text="fieldError('address1')" id="address1-err" class="mt-[5px] text-xs text-coral" role="alert"></p>
                                    </div>

                                    {{-- Address Line 2 --}}
                                    <div class="sm:col-span-2">
                                        <label for="billing_address2" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                            @lang('phonix::app.checkout.form.address2')
                                            <span class="text-slate-400 dark:text-slate-500 font-normal text-xs ms-[4px]">(@lang('phonix::app.general.optional'))</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="billing_address2"
                                            name="billing[address][1]"
                                            x-model="form.address2"
                                            class="input-phoenix"
                                            autocomplete="address-line2"
                                        />
                                    </div>

                                    {{-- Country --}}
                                    <div>
                                        <label for="billing_country" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                            @lang('phonix::app.checkout.form.country') <span class="text-coral" aria-hidden="true">*</span>
                                        </label>
                                        <div class="relative">
                                            <select
                                                id="billing_country"
                                                name="billing[country]"
                                                x-model="form.country"
                                                @change="onCountryChange(); touchField('country')"
                                                class="input-phoenix appearance-none pe-[36px]"
                                                :class="fieldError('country') ? 'border-coral focus:border-coral focus:ring-coral/20' : ''"
                                                required
                                                autocomplete="country"
                                                :aria-invalid="!!fieldError('country')"
                                                aria-describedby="country-err"
                                            >
                                                <option value="">-- @lang('phonix::app.checkout.form.select_country') --</option>
                                                <template x-for="c in countries" :key="c.code">
                                                    <option :value="c.code" x-text="c.name"></option>
                                                </template>
                                            </select>
                                            <svg class="absolute end-[12px] top-1/2 -translate-y-1/2 w-[14px] h-[14px] text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                        <p x-show="fieldError('country')" x-text="fieldError('country')" id="country-err" class="mt-[5px] text-xs text-coral" role="alert"></p>
                                    </div>

                                    {{-- State --}}
                                    <div>
                                        <label for="billing_state" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                            @lang('phonix::app.checkout.form.state') <span class="text-coral" aria-hidden="true">*</span>
                                        </label>
                                        <template x-if="statesForCountry.length > 0">
                                            <div class="relative">
                                                <select
                                                    id="billing_state"
                                                    name="billing[state]"
                                                    x-model="form.state"
                                                    @blur="touchField('state')"
                                                    class="input-phoenix appearance-none pe-[36px]"
                                                    :class="fieldError('state') ? 'border-coral focus:border-coral focus:ring-coral/20' : ''"
                                                    required
                                                    autocomplete="address-level1"
                                                    :aria-invalid="!!fieldError('state')"
                                                    aria-describedby="state-err"
                                                >
                                                    <option value="">-- @lang('phonix::app.checkout.form.select_state') --</option>
                                                    <template x-for="s in statesForCountry" :key="s.code">
                                                        <option :value="s.code" x-text="s.default_name"></option>
                                                    </template>
                                                </select>
                                                <svg class="absolute end-[12px] top-1/2 -translate-y-1/2 w-[14px] h-[14px] text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                        </template>
                                        <template x-if="statesForCountry.length === 0">
                                            <input
                                                type="text"
                                                id="billing_state"
                                                name="billing[state]"
                                                x-model="form.state"
                                                @blur="touchField('state')"
                                                class="input-phoenix"
                                                :class="fieldError('state') ? 'border-coral focus:border-coral focus:ring-coral/20' : ''"
                                                autocomplete="address-level1"
                                                :aria-invalid="!!fieldError('state')"
                                                aria-describedby="state-err"
                                            />
                                        </template>
                                        <p x-show="fieldError('state')" x-text="fieldError('state')" id="state-err" class="mt-[5px] text-xs text-coral" role="alert"></p>
                                    </div>

                                    {{-- City --}}
                                    <div>
                                        <label for="billing_city" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                            @lang('phonix::app.checkout.form.city') <span class="text-coral" aria-hidden="true">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="billing_city"
                                            name="billing[city]"
                                            x-model="form.city"
                                            @blur="touchField('city')"
                                            class="input-phoenix"
                                            :class="fieldError('city') ? 'border-coral focus:border-coral focus:ring-coral/20' : ''"
                                            required
                                            autocomplete="address-level2"
                                            :aria-invalid="!!fieldError('city')"
                                            aria-describedby="city-err"
                                        />
                                        <p x-show="fieldError('city')" x-text="fieldError('city')" id="city-err" class="mt-[5px] text-xs text-coral" role="alert"></p>
                                    </div>

                                    {{-- Postcode --}}
                                    <div>
                                        <label for="billing_postcode" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                            @lang('phonix::app.checkout.form.postcode') <span class="text-coral" aria-hidden="true">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="billing_postcode"
                                            name="billing[postcode]"
                                            x-model="form.postcode"
                                            @blur="touchField('postcode')"
                                            class="input-phoenix"
                                            :class="fieldError('postcode') ? 'border-coral focus:border-coral focus:ring-coral/20' : ''"
                                            required
                                            autocomplete="postal-code"
                                            :aria-invalid="!!fieldError('postcode')"
                                            aria-describedby="postcode-err"
                                        />
                                        <p x-show="fieldError('postcode')" x-text="fieldError('postcode')" id="postcode-err" class="mt-[5px] text-xs text-coral" role="alert"></p>
                                    </div>

                                </div>

                                {{-- Checkboxes --}}
                                <div class="mt-[20px] space-y-[12px]">
                                    <label class="flex items-center gap-[10px] cursor-pointer group">
                                        <input
                                            type="checkbox"
                                            x-model="form.useForShipping"
                                            class="w-[18px] h-[18px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 focus:ring-offset-0 transition-colors"
                                        />
                                        <span class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors select-none">
                                            @lang('phonix::app.checkout.address.same_as_billing')
                                        </span>
                                    </label>

                                    @auth('customer')
                                    <label class="flex items-center gap-[10px] cursor-pointer group">
                                        <input
                                            type="checkbox"
                                            x-model="form.saveAddress"
                                            class="w-[18px] h-[18px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 focus:ring-offset-0 transition-colors"
                                        />
                                        <span class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors select-none">
                                            @lang('phonix::app.checkout.address.save_address')
                                        </span>
                                    </label>
                                    @endauth
                                </div>

                                {{-- Navigation --}}
                                <div class="flex justify-between items-center mt-[28px] pt-[20px] border-t border-slate-100 dark:border-dark-border">
                                    <a
                                        href="{{ route('phonix.cart.index') }}"
                                        class="inline-flex items-center gap-[6px] text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
                                    >
                                        <svg class="w-[14px] h-[14px] ltr:rotate-180 rtl:rotate-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        @lang('phonix::app.cart.title')
                                    </a>
                                    <button
                                        type="submit"
                                        class="btn-phoenix px-[28px] py-[12px] flex items-center gap-[8px]"
                                        :disabled="step1Loading"
                                        :class="step1Loading ? 'opacity-60 cursor-not-allowed' : ''"
                                    >
                                        <span x-show="step1Loading" class="w-[16px] h-[16px] border-2 border-white/40 border-t-white rounded-full animate-spin" aria-hidden="true"></span>
                                        <span>@lang('phonix::app.checkout.address.proceed')</span>
                                        <svg x-show="!step1Loading" class="w-[16px] h-[16px] ltr:rotate-0 rtl:rotate-180 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
                {{-- END STEP 1 --}}

                {{-- ── STEP 2: SHIPPING + PAYMENT ────────────────── --}}
                <div
                    x-show="currentStep === 2"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-[16px]"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-[8px]"
                    data-gsap="fade-up"
                    class="space-y-[20px]"
                >

                    {{-- ── 2a: Shipping Methods ──────────────────── --}}
                    <div class="card-phoenix p-[24px] md:p-[32px]">
                        <div class="flex items-center gap-[12px] mb-[24px]">
                            <div class="w-[40px] h-[40px] rounded-xl bg-gold/10 dark:bg-gold/5 flex items-center justify-center flex-shrink-0">
                                <svg class="w-[20px] h-[20px] text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                                    @lang('phonix::app.checkout.shipping.title')
                                </h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-[2px]">
                                    @lang('phonix::app.checkout.shipping.subtitle')
                                </p>
                            </div>
                        </div>

                        {{-- Loading shimmer --}}
                        <div x-show="step2Loading" class="space-y-[12px]" aria-busy="true" aria-label="@lang('phonix::app.general.loading')">
                            <template x-for="i in 2" :key="i">
                                <div class="h-[72px] rounded-xl bg-slate-100 dark:bg-dark-surface animate-pulse"></div>
                            </template>
                        </div>

                        {{-- No methods --}}
                        <div
                            x-show="!step2Loading && shippingMethods.length === 0"
                            class="flex flex-col items-center justify-center py-[32px] text-center"
                        >
                            <svg class="w-[48px] h-[48px] text-slate-300 dark:text-slate-600 mb-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            <p class="text-sm text-slate-500 dark:text-slate-400">@lang('phonix::app.checkout.shipping.no_methods')</p>
                        </div>

                        {{-- Methods list --}}
                        <div
                            x-show="!step2Loading && shippingMethods.length > 0"
                            class="space-y-[10px]"
                            role="radiogroup"
                            aria-label="@lang('phonix::app.checkout.shipping.method')"
                        >
                            <template x-for="carrier in shippingMethods" :key="carrier.code">
                                <div>
                                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-[8px]" x-text="carrier.carrier_title"></p>
                                    <div class="space-y-[8px]">
                                        <template x-for="rate in carrier.rates" :key="rate.method">
                                            <label
                                                class="flex items-center gap-[16px] p-[16px] rounded-xl border-2 cursor-pointer transition-all duration-200"
                                                :class="selectedShipping === rate.method
                                                    ? 'border-phoenix-500 dark:border-phoenix-400 bg-phoenix-50/50 dark:bg-phoenix-900/20 shadow-sm'
                                                    : 'border-slate-200 dark:border-dark-border hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-dark-card'"
                                            >
                                                <input
                                                    type="radio"
                                                    name="shipping_method"
                                                    :value="rate.method"
                                                    x-model="selectedShipping"
                                                    @change="onShippingChange(rate)"
                                                    class="w-[18px] h-[18px] text-phoenix-500 border-slate-300 dark:border-dark-border focus:ring-phoenix-400 focus:ring-offset-0 flex-shrink-0"
                                                />
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-[8px]">
                                                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="rate.method_title"></span>
                                                        <span
                                                            class="text-sm font-bold flex-shrink-0"
                                                            :class="rate.base_price === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-phoenix-600 dark:text-phoenix-400'"
                                                            x-text="rate.base_price === 0 ? '{{ __('phonix::app.checkout.shipping.free') }}' : rate.base_formatted_price"
                                                        ></span>
                                                    </div>
                                                    <p x-show="rate.method_description" class="text-xs text-slate-500 dark:text-slate-400 mt-[2px]" x-text="rate.method_description"></p>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ── 2b: Payment Methods ───────────────────── --}}
                    <div class="card-phoenix p-[24px] md:p-[32px]">
                        <div class="flex items-center gap-[12px] mb-[24px]">
                            <div class="w-[40px] h-[40px] rounded-xl bg-phoenix-100 dark:bg-phoenix-900/30 flex items-center justify-center flex-shrink-0">
                                <svg class="w-[20px] h-[20px] text-phoenix-600 dark:text-phoenix-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                                    @lang('phonix::app.checkout.payment.title')
                                </h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-[2px]">
                                    @lang('phonix::app.checkout.payment.subtitle')
                                </p>
                            </div>
                        </div>

                        {{-- Loading shimmer --}}
                        <div x-show="paymentLoading" class="space-y-[12px]" aria-busy="true">
                            <template x-for="i in 3" :key="i">
                                <div class="h-[64px] rounded-xl bg-slate-100 dark:bg-dark-surface animate-pulse"></div>
                            </template>
                        </div>

                        {{-- Methods --}}
                        <div
                            x-show="!paymentLoading && paymentMethods.length > 0"
                            class="space-y-[10px]"
                            role="radiogroup"
                            aria-label="@lang('phonix::app.checkout.payment.method')"
                        >
                            <template x-for="method in paymentMethods" :key="method.method">
                                <div>
                                    <label
                                        class="flex items-center gap-[16px] p-[16px] rounded-xl border-2 cursor-pointer transition-all duration-200"
                                        :class="selectedPayment === method.method
                                            ? 'border-phoenix-500 dark:border-phoenix-400 bg-phoenix-50/50 dark:bg-phoenix-900/20 shadow-sm'
                                            : 'border-slate-200 dark:border-dark-border hover:border-slate-300 dark:hover:border-slate-600 bg-white dark:bg-dark-card'"
                                    >
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            :value="method.method"
                                            x-model="selectedPayment"
                                            @change="onPaymentChange(method)"
                                            class="w-[18px] h-[18px] text-phoenix-500 border-slate-300 dark:border-dark-border focus:ring-phoenix-400 focus:ring-offset-0 flex-shrink-0"
                                        />
                                        <div class="flex items-center gap-[12px] flex-1 min-w-0">
                                            <template x-if="method.image">
                                                <img :src="method.image" :alt="method.method_title" class="w-[40px] h-[28px] object-contain rounded border border-slate-100 dark:border-dark-border flex-shrink-0" loading="lazy" />
                                            </template>
                                            <template x-if="!method.image">
                                                <div class="w-[40px] h-[28px] rounded bg-slate-100 dark:bg-dark-surface flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-[16px] h-[16px] text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1"/></svg>
                                                </div>
                                            </template>
                                            <div class="min-w-0">
                                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 block" x-text="method.method_title"></span>
                                                <span x-show="method.description" class="text-xs text-slate-500 dark:text-slate-400" x-text="method.description"></span>
                                            </div>
                                        </div>
                                    </label>

                                    {{-- Inline credit card form for card-type payments --}}
                                    <div
                                        x-show="selectedPayment === method.method && isCardPayment(method.method)"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 -translate-y-[8px]"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="mt-[8px] ms-[34px] p-[20px] rounded-xl border border-slate-200 dark:border-dark-border bg-slate-50 dark:bg-dark-surface/50"
                                    >
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-[14px]">
                                            <div class="sm:col-span-2">
                                                <label for="card_number" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-[5px]">
                                                    @lang('phonix::app.checkout.payment.card_number') <span class="text-coral" aria-hidden="true">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    id="card_number"
                                                    x-model="card.number"
                                                    @input="formatCardNumber($event)"
                                                    placeholder="1234 5678 9012 3456"
                                                    maxlength="19"
                                                    class="input-phoenix text-sm"
                                                    autocomplete="cc-number"
                                                    inputmode="numeric"
                                                />
                                            </div>
                                            <div>
                                                <label for="card_expiry" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-[5px]">
                                                    @lang('phonix::app.checkout.payment.expiry') <span class="text-coral" aria-hidden="true">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    id="card_expiry"
                                                    x-model="card.expiry"
                                                    @input="formatExpiry($event)"
                                                    placeholder="MM / YY"
                                                    maxlength="7"
                                                    class="input-phoenix text-sm"
                                                    autocomplete="cc-exp"
                                                    inputmode="numeric"
                                                />
                                            </div>
                                            <div>
                                                <label for="card_cvv" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-[5px]">
                                                    @lang('phonix::app.checkout.payment.cvv') <span class="text-coral" aria-hidden="true">*</span>
                                                </label>
                                                <input
                                                    type="password"
                                                    id="card_cvv"
                                                    x-model="card.cvv"
                                                    placeholder="•••"
                                                    maxlength="4"
                                                    class="input-phoenix text-sm"
                                                    autocomplete="cc-csc"
                                                    inputmode="numeric"
                                                />
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label for="card_name" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-[5px]">
                                                    @lang('phonix::app.checkout.payment.name_on_card') <span class="text-coral" aria-hidden="true">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    id="card_name"
                                                    x-model="card.name"
                                                    class="input-phoenix text-sm"
                                                    autocomplete="cc-name"
                                                />
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-[6px] mt-[12px] text-xs text-slate-400 dark:text-slate-500">
                                            <svg class="w-[12px] h-[12px] text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                            @lang('phonix::app.checkout.payment.secure_note')
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- No payment methods --}}
                        <div x-show="!paymentLoading && paymentMethods.length === 0" class="text-center py-[24px]">
                            <p class="text-sm text-slate-500 dark:text-slate-400">@lang('phonix::app.checkout.payment.no_methods')</p>
                        </div>
                    </div>

                    {{-- Navigation --}}
                    <div class="flex justify-between items-center">
                        <button
                            type="button"
                            @click="goToStep(1)"
                            class="inline-flex items-center gap-[8px] px-[20px] py-[11px] rounded-xl border border-slate-300 dark:border-dark-border text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-dark-card hover:bg-slate-50 dark:hover:bg-dark-surface transition-all duration-200"
                        >
                            <svg class="w-[14px] h-[14px] ltr:rotate-180 rtl:rotate-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            @lang('phonix::app.checkout.address.title')
                        </button>
                        <button
                            type="button"
                            @click="proceedToReview()"
                            :disabled="!selectedShipping || !selectedPayment || step2Saving"
                            class="btn-phoenix px-[28px] py-[12px] flex items-center gap-[8px]"
                            :class="(!selectedShipping || !selectedPayment || step2Saving) ? 'opacity-60 cursor-not-allowed' : ''"
                        >
                            <span x-show="step2Saving" class="w-[16px] h-[16px] border-2 border-white/40 border-t-white rounded-full animate-spin" aria-hidden="true"></span>
                            <span>@lang('phonix::app.checkout.review.proceed')</span>
                            <svg x-show="!step2Saving" class="w-[16px] h-[16px] ltr:rotate-0 rtl:rotate-180 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                </div>
                {{-- END STEP 2 --}}

                {{-- ── STEP 3: REVIEW & PLACE ORDER ──────────────── --}}
                <div
                    x-show="currentStep === 3"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-[16px]"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-[8px]"
                    data-gsap="fade-up"
                    class="space-y-[20px]"
                >

                    {{-- Review Card --}}
                    <div class="card-phoenix p-[24px] md:p-[32px]">
                        <div class="flex items-center gap-[12px] mb-[28px]">
                            <div class="w-[40px] h-[40px] rounded-xl bg-emerald-100 dark:bg-emerald-900/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-[20px] h-[20px] text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                                @lang('phonix::app.checkout.review.title')
                            </h2>
                        </div>

                        {{-- Cart items table --}}
                        <div class="mb-[24px]">
                            <div class="flex items-center justify-between mb-[12px]">
                                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    @lang('phonix::app.cart.items')
                                </h3>
                                <a
                                    href="{{ route('phonix.cart.index') }}"
                                    class="text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline"
                                >
                                    @lang('phonix::app.checkout.review.edit_cart')
                                </a>
                            </div>
                            <div class="space-y-[8px] max-h-[280px] overflow-y-auto overscroll-contain scrollbar-thin pr-[4px]">
                                @foreach ($cartItems as $item)
                                <div class="flex items-center gap-[12px] p-[12px] rounded-xl bg-slate-50 dark:bg-dark-surface border border-slate-100 dark:border-dark-border">
                                    <div class="w-[52px] h-[52px] flex-shrink-0 rounded-lg bg-white dark:bg-dark-card overflow-hidden border border-slate-100 dark:border-dark-border">
                                        @if ($item['image'])
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover" loading="lazy" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-[20px] h-[20px] text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l4.5 4.5L9 12l3 3 3-3 3 3"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $item['name'] }}</p>
                                        @if ($item['variant'])
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $item['variant'] }}</p>
                                        @endif
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            @lang('phonix::app.cart.qty'): {{ $item['quantity'] }}
                                        </p>
                                    </div>
                                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200 flex-shrink-0">
                                        {{ core()->formatPrice($item['total']) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Shipping & Payment summary --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-[16px] mb-[24px]">

                            {{-- Shipping address --}}
                            <div class="p-[16px] rounded-xl bg-slate-50 dark:bg-dark-surface border border-slate-100 dark:border-dark-border">
                                <div class="flex items-center justify-between mb-[10px]">
                                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        @lang('phonix::app.checkout.address.shipping')
                                    </h3>
                                    <button @click="goToStep(1)" class="text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline">
                                        @lang('phonix::app.general.edit')
                                    </button>
                                </div>
                                <div class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed" x-html="getAddressSummary()"></div>
                            </div>

                            {{-- Shipping + Payment methods --}}
                            <div class="space-y-[12px]">
                                <div class="p-[16px] rounded-xl bg-slate-50 dark:bg-dark-surface border border-slate-100 dark:border-dark-border">
                                    <div class="flex items-center justify-between mb-[6px]">
                                        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            @lang('phonix::app.checkout.shipping.method')
                                        </h3>
                                        <button @click="goToStep(2)" class="text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline">
                                            @lang('phonix::app.general.edit')
                                        </button>
                                    </div>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 font-medium" x-text="selectedShippingLabel || '—'"></p>
                                </div>
                                <div class="p-[16px] rounded-xl bg-slate-50 dark:bg-dark-surface border border-slate-100 dark:border-dark-border">
                                    <div class="flex items-center justify-between mb-[6px]">
                                        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            @lang('phonix::app.checkout.payment.method')
                                        </h3>
                                        <button @click="goToStep(2)" class="text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline">
                                            @lang('phonix::app.general.edit')
                                        </button>
                                    </div>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 font-medium" x-text="selectedPaymentLabel || '—'"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Order totals --}}
                        <div class="rounded-xl bg-slate-50 dark:bg-dark-surface border border-slate-100 dark:border-dark-border p-[16px] mb-[24px] space-y-[10px]">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.subtotal')</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ core()->formatPrice($cartSubTotal) }}</span>
                            </div>
                            @if ($cartShipping > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.shipping')</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ core()->formatPrice($cartShipping) }}</span>
                            </div>
                            @endif
                            @if ($cartDiscount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.discount')</span>
                                <span class="font-medium text-emerald-600 dark:text-emerald-400">-{{ core()->formatPrice($cartDiscount) }}</span>
                            </div>
                            @endif
                            @if ($cartTax > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.tax')</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ core()->formatPrice($cartTax) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-center pt-[12px] border-t-2 border-slate-200 dark:border-dark-border">
                                <span class="text-base font-bold text-slate-900 dark:text-white">@lang('phonix::app.cart.grand_total')</span>
                                <span class="text-2xl font-bold text-phoenix-600 dark:text-phoenix-400">{{ core()->formatPrice($cartGrandTotal) }}</span>
                            </div>
                        </div>

                        {{-- Terms --}}
                        <div class="mb-[24px]">
                            <label class="flex items-start gap-[12px] cursor-pointer group">
                                <input
                                    type="checkbox"
                                    x-model="termsAccepted"
                                    id="terms_checkbox"
                                    class="w-[18px] h-[18px] mt-[2px] flex-shrink-0 rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400 focus:ring-offset-0 transition-colors"
                                    required
                                    :aria-invalid="!termsAccepted && orderAttempted"
                                />
                                <span class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors select-none leading-relaxed">
                                    @lang('phonix::app.checkout.review.terms_agree')
                                    <a href="{{ route('phonix.products.index') }}" class="text-phoenix-600 dark:text-phoenix-400 hover:underline font-medium" target="_blank" rel="noopener">
                                        @lang('phonix::app.checkout.review.terms_link')
                                    </a>
                                </span>
                            </label>
                            <p
                                x-show="!termsAccepted && orderAttempted"
                                class="mt-[6px] text-xs text-coral ms-[30px]"
                                role="alert"
                            >@lang('phonix::app.checkout.review.terms_required')</p>
                        </div>

                        {{-- Navigation --}}
                        <div class="flex flex-col-reverse sm:flex-row justify-between items-stretch sm:items-center gap-[12px] pt-[8px] border-t border-slate-100 dark:border-dark-border">
                            <button
                                type="button"
                                @click="goToStep(2)"
                                class="inline-flex items-center justify-center gap-[8px] px-[20px] py-[11px] rounded-xl border border-slate-300 dark:border-dark-border text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-dark-card hover:bg-slate-50 dark:hover:bg-dark-surface transition-all duration-200"
                            >
                                <svg class="w-[14px] h-[14px] ltr:rotate-180 rtl:rotate-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                @lang('phonix::app.checkout.step.payment')
                            </button>
                            <button
                                type="button"
                                @click="placeOrder()"
                                :disabled="placingOrder"
                                class="btn-phoenix px-[36px] py-[14px] text-base flex items-center justify-center gap-[10px]"
                                :class="placingOrder ? 'opacity-70 cursor-not-allowed' : ''"
                                data-gsap="pulse"
                            >
                                <span x-show="placingOrder" class="w-[18px] h-[18px] border-2 border-white/40 border-t-white rounded-full animate-spin" aria-hidden="true"></span>
                                <svg x-show="!placingOrder" class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="placingOrder ? '{{ __('phonix::app.checkout.review.placing_order') }}' : '{{ __('phonix::app.checkout.review.place_order') }}'"></span>
                            </button>
                        </div>
                    </div>

                </div>
                {{-- END STEP 3 --}}

            </div>
            {{-- END LEFT COLUMN --}}

            {{-- ═══════════════════════════════════════════════════════
                 RIGHT — ORDER SUMMARY SIDEBAR  (2 of 5 columns)
            ═══════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-2" data-gsap="fade-up">
                <div class="card-phoenix p-[20px] lg:sticky lg:top-[88px]">

                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-[16px]">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            @lang('phonix::app.cart.summary')
                        </h3>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                            ({{ count($cartItems) }} {{ trans_choice('phonix::app.cart.items_count', count($cartItems)) }})
                        </span>
                    </div>

                    {{-- Items (collapsible) --}}
                    <div x-data="{ expanded: false }">
                        <div
                            class="space-y-[8px] overflow-hidden transition-all duration-300"
                            :class="expanded ? 'max-h-[400px]' : 'max-h-[160px]'"
                            style="mask-image: linear-gradient(to bottom, black 70%, transparent 100%);"
                            :style="expanded ? 'mask-image: none' : ''"
                        >
                            @foreach ($cartItems as $item)
                            <div class="flex items-center gap-[10px]">
                                <div class="relative flex-shrink-0">
                                    <div class="w-[44px] h-[44px] rounded-lg bg-slate-100 dark:bg-dark-surface overflow-hidden border border-slate-100 dark:border-dark-border">
                                        @if ($item['image'])
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover" loading="lazy" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-[16px] h-[16px] text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="absolute -top-[5px] -end-[5px] w-[18px] h-[18px] rounded-full bg-phoenix-500 text-white text-[9px] font-bold flex items-center justify-center leading-none">
                                        {{ $item['quantity'] }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-slate-800 dark:text-slate-200 truncate">{{ $item['name'] }}</p>
                                    @if ($item['variant'])
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate">{{ $item['variant'] }}</p>
                                    @endif
                                </div>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex-shrink-0 ms-[4px]">
                                    {{ core()->formatPrice($item['total']) }}
                                </span>
                            </div>
                            @endforeach
                        </div>

                        @if (count($cartItems) > 2)
                        <button
                            type="button"
                            @click="expanded = !expanded"
                            class="mt-[10px] text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline flex items-center gap-[4px]"
                        >
                            <span x-text="expanded ? '{{ __('phonix::app.general.show_less') }}' : '{{ __('phonix::app.cart.show_more', ['count' => max(0, count($cartItems) - 2)]) }}'"></span>
                            <svg class="w-[12px] h-[12px] transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        @endif
                    </div>

                    {{-- Divider --}}
                    <div class="my-[16px] border-t border-slate-200 dark:border-dark-border"></div>

                    {{-- Totals --}}
                    <div class="space-y-[8px]">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.subtotal')</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ core()->formatPrice($cartSubTotal) }}</span>
                        </div>

                        @if ($cartShipping > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.shipping')</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ core()->formatPrice($cartShipping) }}</span>
                        </div>
                        @else
                        <div class="flex justify-between text-sm" x-show="currentStep === 1">
                            <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.shipping')</span>
                            <span class="text-xs font-medium text-slate-400 dark:text-slate-500 italic">@lang('phonix::app.checkout.shipping.calculated_next')</span>
                        </div>
                        @endif

                        @if ($cartDiscount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.discount')</span>
                            <span class="font-medium text-emerald-600 dark:text-emerald-400">-{{ core()->formatPrice($cartDiscount) }}</span>
                        </div>
                        @endif

                        @if ($cartTax > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.tax')</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ core()->formatPrice($cartTax) }}</span>
                        </div>
                        @endif

                        <div class="flex justify-between items-center pt-[12px] border-t-2 border-slate-200 dark:border-dark-border">
                            <span class="font-bold text-slate-900 dark:text-white">@lang('phonix::app.cart.grand_total')</span>
                            <span class="text-xl font-bold text-phoenix-600 dark:text-phoenix-400">{{ core()->formatPrice($cartGrandTotal) }}</span>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="my-[16px] border-t border-slate-200 dark:border-dark-border"></div>

                    {{-- Security badges --}}
                    <div class="space-y-[10px]">
                        <div class="flex items-center gap-[8px] text-xs text-slate-500 dark:text-slate-400">
                            <svg class="w-[14px] h-[14px] text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            @lang('phonix::app.checkout.security.ssl')
                        </div>
                        <div class="flex items-center gap-[8px] text-xs text-slate-500 dark:text-slate-400">
                            <svg class="w-[14px] h-[14px] text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            @lang('phonix::app.checkout.security.encrypted')
                        </div>
                        <div class="flex items-center gap-[8px] text-xs text-slate-500 dark:text-slate-400">
                            <svg class="w-[14px] h-[14px] text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                            @lang('phonix::app.checkout.security.easy_returns')
                        </div>
                    </div>

                    {{-- Payment icons --}}
                    <div class="mt-[16px] flex items-center flex-wrap gap-[6px]" aria-label="@lang('phonix::app.checkout.security.accepted_payments')">
                        @foreach (['VISA', 'MC', 'AMEX', 'PP'] as $badge)
                        <div class="px-[8px] py-[4px] rounded border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-card text-[9px] font-bold
                            {{ $badge === 'VISA' ? 'text-blue-700' : ($badge === 'MC' ? 'text-red-600' : ($badge === 'AMEX' ? 'text-blue-500' : 'text-sky-600')) }}">
                            {{ $badge }}
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
            {{-- END RIGHT COLUMN --}}

        </div>
    </main>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     ALPINE.JS CONTROLLER
═══════════════════════════════════════════════════════════════════ --}}
@pushOnce('scripts')
<script>
function phonixCheckout() {
    return {
        /* ── state ───────────────────────────────────────────────── */
        currentStep: 1,

        /* step-1 */
        countries: [],
        allStates: {},
        savedAddresses: [],
        selectedAddressId: null,
        showNewAddressForm: {{ auth()->guard('customer')->check() ? 'false' : 'true' }},
        step1Loading: false,
        form: {
            first_name:     '{{ $customer->first_name ?? '' }}',
            last_name:      '{{ $customer->last_name ?? '' }}',
            email:          '{{ $customer->email ?? '' }}',
            phone:          '',
            address1:       '',
            address2:       '',
            country:        '{{ core()->getCurrentChannel()->country ?? '' }}',
            state:          '',
            city:           '',
            postcode:       '',
            useForShipping: true,
            saveAddress:    false,
        },
        touched: {},
        errors: {},

        /* step-2 */
        shippingMethods:    [],
        paymentMethods:     [],
        selectedShipping:   '',
        selectedShippingLabel: '',
        selectedPayment:    '',
        selectedPaymentLabel: '',
        step2Loading:       false,
        paymentLoading:     false,
        step2Saving:        false,

        /* card form */
        card: { number: '', expiry: '', cvv: '', name: '' },

        /* step-3 */
        termsAccepted: false,
        orderAttempted: false,
        placingOrder:  false,

        /* toast */
        toast: { show: false, type: 'error', message: '' },

        /* stored shipping address (from API response) */
        _shippingAddress: null,

        /* ── init ────────────────────────────────────────────────── */
        async init() {
            await Promise.all([
                this.loadCountries(),
                this.loadStates(),
                @auth('customer')
                this.loadSavedAddresses(),
                @endauth
            ]);
        },

        /* ── helpers ─────────────────────────────────────────────── */
        showToast(message, type = 'error') {
            this.toast = { show: true, type, message };
            setTimeout(() => { this.toast.show = false; }, 5000);
        },

        goToStep(step) {
            this.currentStep = step;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fieldError(key) {
            return this.touched[key] ? (this.errors[key] || null) : null;
        },

        touchField(key) {
            this.touched[key] = true;
            this.validateField(key);
        },

        validateField(key) {
            const v = this.form[key] ?? '';
            const required = ['first_name','last_name','email','phone','address1','country','city','postcode'];
            if (required.includes(key) && !String(v).trim()) {
                this.errors[key] = '{{ __('phonix::app.validation.required') }}';
                return false;
            }
            if (key === 'email' && v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
                this.errors[key] = '{{ __('phonix::app.validation.email') }}';
                return false;
            }
            if (key === 'phone' && v && !/^[\+]?[\d\s\-\(\)]{7,20}$/.test(v)) {
                this.errors[key] = '{{ __('phonix::app.validation.phone') }}';
                return false;
            }
            delete this.errors[key];
            return true;
        },

        validateAddressForm() {
            const required = ['first_name','last_name','email','phone','address1','country','city','postcode'];
            let valid = true;
            required.forEach(k => {
                this.touched[k] = true;
                if (!this.validateField(k)) valid = false;
            });
            return valid;
        },

        get statesForCountry() {
            if (!this.form.country || !this.allStates[this.form.country]) return [];
            return this.allStates[this.form.country];
        },

        onCountryChange() {
            this.form.state = '';
        },

        isCardPayment(method) {
            const cardMethods = ['cashondelivery', 'stripe', 'paypal', 'moneybookers', 'free'];
            // Show card form only for methods whose name suggests card entry
            return ['stripe_payment', 'authorizenet', 'paymentexpress', 'eway', 'sagepay'].some(m => method.toLowerCase().includes(m));
        },

        formatCardNumber(event) {
            let v = event.target.value.replace(/\D/g, '').substring(0, 16);
            this.card.number = v.replace(/(.{4})/g, '$1 ').trim();
        },

        formatExpiry(event) {
            let v = event.target.value.replace(/\D/g, '').substring(0, 4);
            if (v.length >= 3) {
                this.card.expiry = v.substring(0, 2) + ' / ' + v.substring(2);
            } else {
                this.card.expiry = v;
            }
        },

        getAddressSummary() {
            const a = this._shippingAddress || this.form;
            if (!a) return '—';
            const lines = [
                `<strong>${a.first_name} ${a.last_name}</strong>`,
                a.address1 || a.address?.[0],
                a.address2 || a.address?.[1],
                [a.city, a.state, a.postcode].filter(Boolean).join(', '),
                a.country,
                a.phone,
            ].filter(Boolean);
            return lines.join('<br>');
        },

        /* ── API calls ────────────────────────────────────────────── */
        async loadCountries() {
            try {
                const res = await fetch('{{ route('shop.api.core.countries') }}');
                const data = await res.json();
                this.countries = data.data || [];
            } catch (e) {}
        },

        async loadStates() {
            try {
                const res = await fetch('{{ route('shop.api.core.states') }}');
                const data = await res.json();
                this.allStates = data.data || {};
            } catch (e) {}
        },

        @auth('customer')
        async loadSavedAddresses() {
            try {
                const res = await fetch('{{ route('shop.api.customers.account.addresses.index') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.savedAddresses = data.data || [];
                if (this.savedAddresses.length > 0) {
                    const def = this.savedAddresses.find(a => a.default_address) || this.savedAddresses[0];
                    this.selectedAddressId = def.id;
                    this.showNewAddressForm = false;
                }
            } catch (e) {}
        },
        @endauth

        /* STEP 1 — submit saved address */
        async submitSavedAddress() {
            if (!this.selectedAddressId) return;
            const addr = this.savedAddresses.find(a => a.id === this.selectedAddressId);
            if (!addr) return;
            this.step1Loading = true;
            await this.postAddressToApi({
                billing: { ...addr, id: addr.id, use_for_shipping: true },
            });
            this.step1Loading = false;
        },

        /* STEP 1 — submit new address form */
        async submitNewAddress() {
            if (!this.validateAddressForm()) return;
            this.step1Loading = true;
            const payload = {
                billing: {
                    first_name:       this.form.first_name,
                    last_name:        this.form.last_name,
                    email:            this.form.email,
                    phone:            this.form.phone,
                    address:          [this.form.address1, this.form.address2].filter(Boolean),
                    country:          this.form.country,
                    state:            this.form.state,
                    city:             this.form.city,
                    postcode:         this.form.postcode,
                    use_for_shipping: this.form.useForShipping,
                    save_address:     this.form.saveAddress ? 1 : 0,
                },
            };
            if (!this.form.useForShipping) {
                payload.shipping = { ...payload.billing };
            }
            await this.postAddressToApi(payload);
            this.step1Loading = false;
        },

        async postAddressToApi(payload) {
            try {
                const res = await fetch('{{ route('shop.checkout.onepage.addresses.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || '{{ __('phonix::app.general.error') }}');
                    return;
                }
                if (data.data?.redirect_url) {
                    window.location.href = data.data.redirect_url;
                    return;
                }
                this._shippingAddress = payload.billing;
                this.shippingMethods = data.data?.shippingMethods || data.data?.shipping_methods || [];
                this.step2Loading = false;
                this.goToStep(2);
                // Also fetch payment methods
                await this.loadPaymentMethods();
            } catch (e) {
                this.showToast('{{ __('phonix::app.general.error') }}');
            }
        },

        /* STEP 2 — shipping method selection → triggers payment method fetch */
        async onShippingChange(rate) {
            this.selectedShippingLabel = rate.method_title + (rate.base_price === 0 ? ' ({{ __('phonix::app.checkout.shipping.free') }})' : ' — ' + rate.base_formatted_price);
            this.paymentLoading = true;
            try {
                const res = await fetch('{{ route('shop.checkout.onepage.shipping_methods.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ shipping_method: rate.method }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || '{{ __('phonix::app.general.error') }}');
                    this.paymentLoading = false;
                    return;
                }
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                    return;
                }
                this.paymentMethods = data.payment_methods || [];
                this.paymentLoading = false;
            } catch (e) {
                this.paymentLoading = false;
                this.showToast('{{ __('phonix::app.general.error') }}');
            }
        },

        async loadPaymentMethods() {
            // Payment methods load after shipping method is chosen.
            // Nothing to do here initially.
        },

        /* STEP 2 — payment method change → store on server */
        async onPaymentChange(method) {
            this.selectedPaymentLabel = method.method_title;
            this.step2Saving = true;
            try {
                const res = await fetch('{{ route('shop.checkout.onepage.payment_methods.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ payment: { method: method.method, method_title: method.method_title } }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || '{{ __('phonix::app.general.error') }}');
                }
            } catch (e) {
                this.showToast('{{ __('phonix::app.general.error') }}');
            } finally {
                this.step2Saving = false;
            }
        },

        /* STEP 2 — proceed to review validates selections */
        proceedToReview() {
            if (!this.selectedShipping) {
                this.showToast('{{ __('phonix::app.checkout.shipping.select_required') }}');
                return;
            }
            if (!this.selectedPayment) {
                this.showToast('{{ __('phonix::app.checkout.payment.select_required') }}');
                return;
            }
            this.goToStep(3);
        },

        /* STEP 3 — place order */
        async placeOrder() {
            this.orderAttempted = true;
            if (!this.termsAccepted) {
                document.getElementById('terms_checkbox')?.focus();
                return;
            }
            this.placingOrder = true;
            try {
                const res = await fetch('{{ route('shop.checkout.onepage.orders.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || '{{ __('phonix::app.checkout.review.order_failed') }}');
                    this.placingOrder = false;
                    return;
                }
                if (data.data?.redirect) {
                    window.location.href = data.data.redirect_url;
                } else {
                    window.location.href = '{{ route('phonix.checkout.success') }}';
                }
            } catch (e) {
                this.placingOrder = false;
                this.showToast('{{ __('phonix::app.checkout.review.order_failed') }}');
            }
        },
    };
}
</script>
@endPushOnce

</x-phonix::layouts.index>
