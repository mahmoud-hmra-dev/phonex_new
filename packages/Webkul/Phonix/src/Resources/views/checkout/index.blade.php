@php
    $cartItems = [
        ['name' => 'iPhone 15 Pro Max', 'variant' => '256GB / Natural Titanium', 'price' => 4999, 'quantity' => 1, 'image' => null],
        ['name' => 'AirPods Pro 2', 'variant' => 'USB-C', 'price' => 899, 'quantity' => 2, 'image' => null],
        ['name' => 'Samsung Galaxy S24 Ultra', 'variant' => '512GB / Titanium Gray', 'price' => 4499, 'quantity' => 1, 'image' => null],
        ['name' => 'Anker PowerCore 20000', 'variant' => 'Black', 'price' => 199, 'quantity' => 1, 'image' => null],
    ];
@endphp

<x-phonix::layouts.index :title="__('phonix::app.checkout.title')">

    <div
        x-data="checkoutPage()"
        class="container mx-auto section-padding"
    >
        {{-- Breadcrumb --}}
        <nav aria-label="@lang('phonix::app.general.breadcrumb')" class="mb-[32px]" data-gsap="fade-up">
            <ol class="flex items-center gap-[8px] text-sm text-slate-500 dark:text-slate-400">
                <li><a href="/" class="hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors">@lang('phonix::app.general.home')</a></li>
                <li aria-hidden="true"><svg class="w-[16px] h-[16px] ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></li>
                <li><a href="{{ route('phonix.cart.index') }}" class="hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors">@lang('phonix::app.cart.title')</a></li>
                <li aria-hidden="true"><svg class="w-[16px] h-[16px] ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></li>
                <li class="text-slate-800 dark:text-slate-200 font-medium" aria-current="page">@lang('phonix::app.checkout.title')</li>
            </ol>
        </nav>

        {{-- Step Indicator --}}
        <div class="mb-[48px]" data-gsap="fade-up" role="navigation" :aria-label="'{{ __('phonix::app.checkout.steps') }}'">
            <div class="flex items-center justify-between max-w-2xl mx-auto">
                <template x-for="(step, i) in steps" :key="i">
                    <div class="flex items-center" :class="{ 'flex-1': i < steps.length - 1 }">
                        {{-- Step Circle --}}
                        <button
                            @click="if (i < currentStep) goToStep(i)"
                            class="relative flex items-center justify-center w-[40px] h-[40px] rounded-full border-2 font-semibold text-sm transition-all duration-300"
                            :class="{
                                'bg-phoenix-500 border-phoenix-500 text-white dark:bg-phoenix-400 dark:border-phoenix-400 dark:text-phoenix-950': i < currentStep,
                                'bg-phoenix-500 border-phoenix-500 text-white dark:bg-phoenix-400 dark:border-phoenix-400 dark:text-phoenix-950 ring-4 ring-phoenix-100 dark:ring-phoenix-900': i === currentStep,
                                'bg-white dark:bg-dark-surface border-slate-300 dark:border-dark-border text-slate-500 dark:text-slate-400': i > currentStep,
                            }"
                            :aria-label="step.label"
                            :aria-current="i === currentStep ? 'step' : false"
                            :disabled="i > currentStep"
                        >
                            <template x-if="i < currentStep">
                                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="i >= currentStep">
                                <span x-text="i + 1"></span>
                            </template>
                        </button>

                        {{-- Step Label (below circle on mobile, beside on desktop) --}}
                        <span
                            class="hidden sm:block ms-[8px] text-xs font-medium whitespace-nowrap"
                            :class="{
                                'text-phoenix-600 dark:text-phoenix-400': i <= currentStep,
                                'text-slate-400 dark:text-slate-500': i > currentStep,
                            }"
                            x-text="step.label"
                        ></span>

                        {{-- Connector Line --}}
                        <template x-if="i < steps.length - 1">
                            <div class="flex-1 mx-[12px] h-[2px] rounded-full transition-colors duration-300" :class="i < currentStep ? 'bg-phoenix-500 dark:bg-phoenix-400' : 'bg-slate-200 dark:bg-dark-border'"></div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-[32px]">
            {{-- Main Steps Area --}}
            <div class="lg:col-span-2">

                {{-- STEP 1: Shipping Address --}}
                <div
                    x-show="currentStep === 0"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    data-gsap="fade-up"
                >
                    <div class="card-phoenix p-[24px] md:p-[32px]">
                        <h2 class="text-fluid-xl font-bold text-slate-900 dark:text-white mb-[24px]">
                            @lang('phonix::app.checkout.address.title')
                        </h2>

                        <form @submit.prevent="validateAddress()" novalidate>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-[16px]">
                                {{-- First Name --}}
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                        @lang('phonix::app.checkout.form.first_name') <span class="text-coral" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="first_name"
                                        x-model="address.first_name"
                                        class="input-phoenix"
                                        :class="{ 'border-coral focus:border-coral': errors.first_name }"
                                        required
                                        :aria-invalid="errors.first_name ? 'true' : 'false'"
                                        aria-describedby="first_name-error"
                                    />
                                    <p x-show="errors.first_name" x-text="errors.first_name" id="first_name-error" class="mt-[6px] text-xs text-coral" role="alert"></p>
                                </div>

                                {{-- Last Name --}}
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                        @lang('phonix::app.checkout.form.last_name') <span class="text-coral" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="last_name"
                                        x-model="address.last_name"
                                        class="input-phoenix"
                                        :class="{ 'border-coral focus:border-coral': errors.last_name }"
                                        required
                                        :aria-invalid="errors.last_name ? 'true' : 'false'"
                                        aria-describedby="last_name-error"
                                    />
                                    <p x-show="errors.last_name" x-text="errors.last_name" id="last_name-error" class="mt-[6px] text-xs text-coral" role="alert"></p>
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                        @lang('phonix::app.checkout.form.email') <span class="text-coral" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        id="email"
                                        x-model="address.email"
                                        class="input-phoenix"
                                        :class="{ 'border-coral focus:border-coral': errors.email }"
                                        required
                                        :aria-invalid="errors.email ? 'true' : 'false'"
                                        aria-describedby="email-error"
                                    />
                                    <p x-show="errors.email" x-text="errors.email" id="email-error" class="mt-[6px] text-xs text-coral" role="alert"></p>
                                </div>

                                {{-- Phone --}}
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                        @lang('phonix::app.checkout.form.phone') <span class="text-coral" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        type="tel"
                                        id="phone"
                                        x-model="address.phone"
                                        class="input-phoenix"
                                        :class="{ 'border-coral focus:border-coral': errors.phone }"
                                        required
                                        :aria-invalid="errors.phone ? 'true' : 'false'"
                                        aria-describedby="phone-error"
                                    />
                                    <p x-show="errors.phone" x-text="errors.phone" id="phone-error" class="mt-[6px] text-xs text-coral" role="alert"></p>
                                </div>

                                {{-- Address 1 --}}
                                <div class="md:col-span-2">
                                    <label for="address1" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                        @lang('phonix::app.checkout.form.address1') <span class="text-coral" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="address1"
                                        x-model="address.address1"
                                        class="input-phoenix"
                                        :class="{ 'border-coral focus:border-coral': errors.address1 }"
                                        required
                                        :aria-invalid="errors.address1 ? 'true' : 'false'"
                                        aria-describedby="address1-error"
                                    />
                                    <p x-show="errors.address1" x-text="errors.address1" id="address1-error" class="mt-[6px] text-xs text-coral" role="alert"></p>
                                </div>

                                {{-- Address 2 --}}
                                <div class="md:col-span-2">
                                    <label for="address2" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                        @lang('phonix::app.checkout.form.address2')
                                    </label>
                                    <input type="text" id="address2" x-model="address.address2" class="input-phoenix" />
                                </div>

                                {{-- City --}}
                                <div>
                                    <label for="city" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                        @lang('phonix::app.checkout.form.city') <span class="text-coral" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="city"
                                        x-model="address.city"
                                        class="input-phoenix"
                                        :class="{ 'border-coral focus:border-coral': errors.city }"
                                        required
                                        :aria-invalid="errors.city ? 'true' : 'false'"
                                        aria-describedby="city-error"
                                    />
                                    <p x-show="errors.city" x-text="errors.city" id="city-error" class="mt-[6px] text-xs text-coral" role="alert"></p>
                                </div>

                                {{-- State --}}
                                <div>
                                    <label for="state" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                        @lang('phonix::app.checkout.form.state') <span class="text-coral" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="state"
                                        x-model="address.state"
                                        class="input-phoenix"
                                        :class="{ 'border-coral focus:border-coral': errors.state }"
                                        required
                                        :aria-invalid="errors.state ? 'true' : 'false'"
                                        aria-describedby="state-error"
                                    />
                                    <p x-show="errors.state" x-text="errors.state" id="state-error" class="mt-[6px] text-xs text-coral" role="alert"></p>
                                </div>

                                {{-- Postcode --}}
                                <div>
                                    <label for="postcode" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                        @lang('phonix::app.checkout.form.postcode') <span class="text-coral" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="postcode"
                                        x-model="address.postcode"
                                        class="input-phoenix"
                                        :class="{ 'border-coral focus:border-coral': errors.postcode }"
                                        required
                                        :aria-invalid="errors.postcode ? 'true' : 'false'"
                                        aria-describedby="postcode-error"
                                    />
                                    <p x-show="errors.postcode" x-text="errors.postcode" id="postcode-error" class="mt-[6px] text-xs text-coral" role="alert"></p>
                                </div>

                                {{-- Country --}}
                                <div>
                                    <label for="country" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                        @lang('phonix::app.checkout.form.country') <span class="text-coral" aria-hidden="true">*</span>
                                    </label>
                                    <select
                                        id="country"
                                        x-model="address.country"
                                        class="input-phoenix"
                                        :class="{ 'border-coral focus:border-coral': errors.country }"
                                        required
                                        :aria-invalid="errors.country ? 'true' : 'false'"
                                        aria-describedby="country-error"
                                    >
                                        <option value="">-- @lang('phonix::app.checkout.form.country') --</option>
                                        <option value="SA">Saudi Arabia</option>
                                        <option value="AE">United Arab Emirates</option>
                                        <option value="KW">Kuwait</option>
                                        <option value="QA">Qatar</option>
                                        <option value="BH">Bahrain</option>
                                        <option value="OM">Oman</option>
                                        <option value="EG">Egypt</option>
                                        <option value="JO">Jordan</option>
                                        <option value="US">United States</option>
                                        <option value="GB">United Kingdom</option>
                                    </select>
                                    <p x-show="errors.country" x-text="errors.country" id="country-error" class="mt-[6px] text-xs text-coral" role="alert"></p>
                                </div>
                            </div>

                            {{-- Checkboxes --}}
                            <div class="mt-[24px] space-y-[12px]">
                                <label class="flex items-center gap-[10px] cursor-pointer">
                                    <input type="checkbox" x-model="address.save" class="w-[18px] h-[18px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400" />
                                    <span class="text-sm text-slate-700 dark:text-slate-300">@lang('phonix::app.general.save') address</span>
                                </label>
                                <label class="flex items-center gap-[10px] cursor-pointer">
                                    <input type="checkbox" x-model="address.sameAsBilling" checked class="w-[18px] h-[18px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400" />
                                    <span class="text-sm text-slate-700 dark:text-slate-300">@lang('phonix::app.checkout.address.same_as_billing')</span>
                                </label>
                            </div>

                            {{-- Navigation --}}
                            <div class="flex justify-end mt-[32px]">
                                <button type="submit" class="btn-phoenix px-[32px] py-[14px]">
                                    @lang('phonix::app.general.next')
                                    <svg class="w-[16px] h-[16px] ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- STEP 2: Shipping Method --}}
                <div
                    x-show="currentStep === 1"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    data-gsap="fade-up"
                >
                    <div class="card-phoenix p-[24px] md:p-[32px]">
                        <h2 class="text-fluid-xl font-bold text-slate-900 dark:text-white mb-[24px]">
                            @lang('phonix::app.checkout.shipping.title')
                        </h2>

                        <div class="space-y-[12px]" role="radiogroup" :aria-label="'{{ __('phonix::app.checkout.shipping.method') }}'">
                            <template x-for="(method, i) in shippingMethods" :key="i">
                                <label
                                    class="flex items-center gap-[16px] p-[20px] rounded-lg border-2 cursor-pointer transition-all duration-200"
                                    :class="selectedShipping === method.id
                                        ? 'border-phoenix-500 dark:border-phoenix-400 bg-phoenix-50/50 dark:bg-phoenix-900/20'
                                        : 'border-slate-200 dark:border-dark-border hover:border-slate-300 dark:hover:border-slate-600'"
                                >
                                    <input
                                        type="radio"
                                        name="shipping_method"
                                        :value="method.id"
                                        x-model="selectedShipping"
                                        class="w-[20px] h-[20px] text-phoenix-500 border-slate-300 dark:border-dark-border focus:ring-phoenix-400"
                                    />
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-slate-800 dark:text-slate-100" x-text="method.name"></span>
                                            <span class="font-bold text-phoenix-600 dark:text-phoenix-400" x-text="method.price === 0 ? '{{ __('phonix::app.checkout.shipping.free') }}' : formatPrice(method.price)"></span>
                                        </div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-[4px]" x-text="method.delivery"></p>
                                    </div>
                                </label>
                            </template>
                        </div>

                        {{-- Navigation --}}
                        <div class="flex justify-between mt-[32px]">
                            <button @click="goToStep(0)" class="btn-phoenix-ghost px-[24px] py-[12px]">
                                <svg class="w-[16px] h-[16px] ltr:rotate-180 rtl:rotate-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                @lang('phonix::app.general.back')
                            </button>
                            <button @click="goToStep(2)" class="btn-phoenix px-[32px] py-[14px]">
                                @lang('phonix::app.general.next')
                                <svg class="w-[16px] h-[16px] ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: Payment Method --}}
                <div
                    x-show="currentStep === 2"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    data-gsap="fade-up"
                >
                    <div class="card-phoenix p-[24px] md:p-[32px]">
                        <h2 class="text-fluid-xl font-bold text-slate-900 dark:text-white mb-[24px]">
                            @lang('phonix::app.checkout.payment.title')
                        </h2>

                        <div class="space-y-[12px]" role="radiogroup" :aria-label="'{{ __('phonix::app.checkout.payment.method') }}'">
                            {{-- Credit Card --}}
                            <div>
                                <label
                                    class="flex items-center gap-[16px] p-[20px] rounded-lg border-2 cursor-pointer transition-all duration-200"
                                    :class="selectedPayment === 'credit_card'
                                        ? 'border-phoenix-500 dark:border-phoenix-400 bg-phoenix-50/50 dark:bg-phoenix-900/20'
                                        : 'border-slate-200 dark:border-dark-border hover:border-slate-300 dark:hover:border-slate-600'"
                                >
                                    <input type="radio" name="payment_method" value="credit_card" x-model="selectedPayment" class="w-[20px] h-[20px] text-phoenix-500 border-slate-300 dark:border-dark-border focus:ring-phoenix-400" />
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-slate-800 dark:text-slate-100">@lang('phonix::app.checkout.payment.credit_card')</span>
                                            <div class="flex gap-[6px]">
                                                <div class="w-[32px] h-[20px] rounded border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-card flex items-center justify-center text-[8px] font-bold text-blue-700">VISA</div>
                                                <div class="w-[32px] h-[20px] rounded border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-card flex items-center justify-center text-[8px] font-bold text-red-600">MC</div>
                                                <div class="w-[32px] h-[20px] rounded border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-card flex items-center justify-center text-[8px] font-bold text-blue-500">AMEX</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                {{-- Card Form --}}
                                <div
                                    x-show="selectedPayment === 'credit_card'"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="mt-[12px] ms-[36px] p-[20px] rounded-lg border border-slate-200 dark:border-dark-border bg-slate-50/50 dark:bg-dark-card/50"
                                >
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-[16px]">
                                        <div class="md:col-span-2">
                                            <label for="card_number" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                                @lang('phonix::app.checkout.payment.card_number') <span class="text-coral" aria-hidden="true">*</span>
                                            </label>
                                            <input type="text" id="card_number" x-model="card.number" placeholder="1234 5678 9012 3456" class="input-phoenix" maxlength="19" required />
                                        </div>
                                        <div>
                                            <label for="card_expiry" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                                @lang('phonix::app.checkout.payment.expiry') <span class="text-coral" aria-hidden="true">*</span>
                                            </label>
                                            <input type="text" id="card_expiry" x-model="card.expiry" placeholder="MM/YY" class="input-phoenix" maxlength="5" required />
                                        </div>
                                        <div>
                                            <label for="card_cvv" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                                @lang('phonix::app.checkout.payment.cvv') <span class="text-coral" aria-hidden="true">*</span>
                                            </label>
                                            <input type="text" id="card_cvv" x-model="card.cvv" placeholder="123" class="input-phoenix" maxlength="4" required />
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="card_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                                @lang('phonix::app.checkout.payment.name_on_card') <span class="text-coral" aria-hidden="true">*</span>
                                            </label>
                                            <input type="text" id="card_name" x-model="card.name" class="input-phoenix" required />
                                        </div>
                                    </div>
                                    {{-- Secure Badge --}}
                                    <div class="flex items-center gap-[8px] mt-[16px] text-xs text-slate-500 dark:text-slate-400">
                                        <svg class="w-[14px] h-[14px] text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                        @lang('phonix::app.features.secure_payment.title')
                                    </div>
                                </div>
                            </div>

                            {{-- Bank Transfer --}}
                            <label
                                class="flex items-center gap-[16px] p-[20px] rounded-lg border-2 cursor-pointer transition-all duration-200"
                                :class="selectedPayment === 'bank_transfer'
                                    ? 'border-phoenix-500 dark:border-phoenix-400 bg-phoenix-50/50 dark:bg-phoenix-900/20'
                                    : 'border-slate-200 dark:border-dark-border hover:border-slate-300 dark:hover:border-slate-600'"
                            >
                                <input type="radio" name="payment_method" value="bank_transfer" x-model="selectedPayment" class="w-[20px] h-[20px] text-phoenix-500 border-slate-300 dark:border-dark-border focus:ring-phoenix-400" />
                                <div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-100">@lang('phonix::app.checkout.payment.bank_transfer')</span>
                                    <p x-show="selectedPayment === 'bank_transfer'" class="text-sm text-slate-500 dark:text-slate-400 mt-[4px]">
                                        Transfer the total amount to our bank account. Your order will be processed once payment is confirmed.
                                    </p>
                                </div>
                            </label>

                            {{-- Cash on Delivery --}}
                            <label
                                class="flex items-center gap-[16px] p-[20px] rounded-lg border-2 cursor-pointer transition-all duration-200"
                                :class="selectedPayment === 'cash_on_delivery'
                                    ? 'border-phoenix-500 dark:border-phoenix-400 bg-phoenix-50/50 dark:bg-phoenix-900/20'
                                    : 'border-slate-200 dark:border-dark-border hover:border-slate-300 dark:hover:border-slate-600'"
                            >
                                <input type="radio" name="payment_method" value="cash_on_delivery" x-model="selectedPayment" class="w-[20px] h-[20px] text-phoenix-500 border-slate-300 dark:border-dark-border focus:ring-phoenix-400" />
                                <div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-100">@lang('phonix::app.checkout.payment.cash_on_delivery')</span>
                                    <p x-show="selectedPayment === 'cash_on_delivery'" class="text-sm text-slate-500 dark:text-slate-400 mt-[4px]">
                                        Pay when you receive your order. Additional COD fee may apply.
                                    </p>
                                </div>
                            </label>
                        </div>

                        {{-- Navigation --}}
                        <div class="flex justify-between mt-[32px]">
                            <button @click="goToStep(1)" class="btn-phoenix-ghost px-[24px] py-[12px]">
                                <svg class="w-[16px] h-[16px] ltr:rotate-180 rtl:rotate-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                @lang('phonix::app.general.back')
                            </button>
                            <button @click="goToStep(3)" class="btn-phoenix px-[32px] py-[14px]">
                                @lang('phonix::app.general.next')
                                <svg class="w-[16px] h-[16px] ltr:rotate-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 4: Review --}}
                <div
                    x-show="currentStep === 3"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    data-gsap="fade-up"
                >
                    <div class="card-phoenix p-[24px] md:p-[32px] space-y-[24px]">
                        <h2 class="text-fluid-xl font-bold text-slate-900 dark:text-white">
                            @lang('phonix::app.checkout.review.title')
                        </h2>

                        {{-- Items --}}
                        <div>
                            <div class="flex items-center justify-between mb-[12px]">
                                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">@lang('phonix::app.cart.items')</h3>
                                <button @click="goToStep(0)" class="text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline">@lang('phonix::app.checkout.review.edit_cart')</button>
                            </div>
                            <div class="space-y-[10px]">
                                <template x-for="(item, i) in items" :key="i">
                                    <div class="flex items-center gap-[12px] p-[12px] rounded-md bg-slate-50 dark:bg-dark-card/50 border border-slate-100 dark:border-dark-border">
                                        <div class="w-[48px] h-[48px] flex-shrink-0 rounded bg-slate-100 dark:bg-dark-card flex items-center justify-center">
                                            <svg class="w-[20px] h-[20px] text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate" x-text="item.name"></p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="item.variant + ' x ' + item.quantity"></p>
                                        </div>
                                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200" x-text="formatPrice(item.price * item.quantity)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Shipping Address --}}
                        <div class="pt-[16px] border-t border-slate-200 dark:border-dark-border">
                            <div class="flex items-center justify-between mb-[8px]">
                                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">@lang('phonix::app.checkout.address.shipping')</h3>
                                <button @click="goToStep(0)" class="text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline">@lang('phonix::app.general.edit')</button>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                <span x-text="address.first_name + ' ' + address.last_name"></span><br>
                                <span x-text="address.address1"></span><br>
                                <span x-text="address.city + ', ' + address.state + ' ' + address.postcode"></span><br>
                                <span x-text="address.phone"></span>
                            </p>
                        </div>

                        {{-- Shipping Method --}}
                        <div class="pt-[16px] border-t border-slate-200 dark:border-dark-border">
                            <div class="flex items-center justify-between mb-[8px]">
                                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">@lang('phonix::app.checkout.shipping.method')</h3>
                                <button @click="goToStep(1)" class="text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline">@lang('phonix::app.general.edit')</button>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-400" x-text="getShippingMethodName()"></p>
                        </div>

                        {{-- Payment Method --}}
                        <div class="pt-[16px] border-t border-slate-200 dark:border-dark-border">
                            <div class="flex items-center justify-between mb-[8px]">
                                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">@lang('phonix::app.checkout.payment.method')</h3>
                                <button @click="goToStep(2)" class="text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline">@lang('phonix::app.general.edit')</button>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-400" x-text="getPaymentMethodName()"></p>
                        </div>

                        {{-- Order Totals --}}
                        <div class="pt-[16px] border-t border-slate-200 dark:border-dark-border space-y-[10px]">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.subtotal')</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200" x-text="formatPrice(subtotal)"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.shipping')</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200" x-text="getShippingCost() === 0 ? '{{ __('phonix::app.checkout.shipping.free') }}' : formatPrice(getShippingCost())"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-400">@lang('phonix::app.cart.tax')</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200" x-text="formatPrice(tax)"></span>
                            </div>
                            <div class="flex justify-between items-center pt-[10px] border-t-2 border-slate-200 dark:border-dark-border">
                                <span class="text-base font-bold text-slate-900 dark:text-white">@lang('phonix::app.cart.grand_total')</span>
                                <span class="text-xl font-bold text-phoenix-500 dark:text-phoenix-400" x-text="formatPrice(grandTotal)"></span>
                            </div>
                        </div>

                        {{-- Terms --}}
                        <div>
                            <label class="flex items-start gap-[10px] cursor-pointer">
                                <input
                                    type="checkbox"
                                    x-model="agreeTerms"
                                    class="w-[18px] h-[18px] mt-[2px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400"
                                    required
                                />
                                <span class="text-sm text-slate-600 dark:text-slate-400">
                                    @lang('phonix::app.checkout.review.terms_agree')
                                </span>
                            </label>
                        </div>

                        {{-- Navigation --}}
                        <div class="flex justify-between pt-[8px]">
                            <button @click="goToStep(2)" class="btn-phoenix-ghost px-[24px] py-[12px]">
                                <svg class="w-[16px] h-[16px] ltr:rotate-180 rtl:rotate-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                @lang('phonix::app.general.back')
                            </button>
                            <button
                                @click="placeOrder()"
                                class="btn-phoenix px-[40px] py-[16px] text-base"
                                :disabled="!agreeTerms"
                                :class="{ 'opacity-50 cursor-not-allowed': !agreeTerms }"
                            >
                                @lang('phonix::app.checkout.review.place_order')
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Summary Sidebar --}}
            <div class="lg:col-span-1" data-gsap="fade-up">
                <div class="card-phoenix p-[24px] lg:sticky lg:top-[100px]">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-[20px]">@lang('phonix::app.cart.summary')</h3>

                    {{-- Mini Items --}}
                    <div class="space-y-[10px] mb-[20px] max-h-[240px] overflow-y-auto scrollbar-thin">
                        <template x-for="(item, i) in items" :key="i">
                            <div class="flex items-center gap-[10px]">
                                <div class="w-[40px] h-[40px] flex-shrink-0 rounded bg-slate-100 dark:bg-dark-card flex items-center justify-center">
                                    <svg class="w-[16px] h-[16px] text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-slate-800 dark:text-slate-200 truncate" x-text="item.name"></p>
                                    <p class="text-xs text-slate-400" x-text="'x' + item.quantity"></p>
                                </div>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="formatPrice(item.price * item.quantity)"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Totals --}}
                    <div class="space-y-[10px] pt-[16px] border-t border-slate-200 dark:border-dark-border">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.subtotal')</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200" x-text="formatPrice(subtotal)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.shipping')</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200" x-text="getShippingCost() === 0 ? '{{ __('phonix::app.checkout.shipping.free') }}' : formatPrice(getShippingCost())"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.cart.tax')</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200" x-text="formatPrice(tax)"></span>
                        </div>
                        <div class="flex justify-between items-center pt-[10px] border-t-2 border-slate-200 dark:border-dark-border">
                            <span class="font-bold text-slate-900 dark:text-white">@lang('phonix::app.cart.grand_total')</span>
                            <span class="text-lg font-bold text-phoenix-500 dark:text-phoenix-400" x-text="formatPrice(grandTotal)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
    <script>
        function checkoutPage() {
            return {
                currentStep: 0,
                items: @json($cartItems),

                steps: [
                    { label: '{{ __('phonix::app.checkout.address.shipping') }}' },
                    { label: '{{ __('phonix::app.checkout.shipping.title') }}' },
                    { label: '{{ __('phonix::app.checkout.payment.title') }}' },
                    { label: '{{ __('phonix::app.checkout.review.title') }}' },
                ],

                address: {
                    first_name: '', last_name: '', email: '', phone: '',
                    address1: '', address2: '', city: '', state: '',
                    postcode: '', country: '', save: false, sameAsBilling: true,
                },

                errors: {},

                selectedShipping: 'standard',
                shippingMethods: [
                    { id: 'standard', name: '{{ __('phonix::app.checkout.shipping.standard') }}', price: 0, delivery: '{{ __('phonix::app.checkout.shipping.estimated_delivery', ['days' => '5-7']) }}' },
                    { id: 'express', name: '{{ __('phonix::app.checkout.shipping.express') }}', price: 15, delivery: '{{ __('phonix::app.checkout.shipping.estimated_delivery', ['days' => '2-3']) }}' },
                    { id: 'next_day', name: 'Next Day Delivery', price: 25, delivery: '{{ __('phonix::app.checkout.shipping.estimated_delivery', ['days' => '1']) }}' },
                ],

                selectedPayment: 'credit_card',
                card: { number: '', expiry: '', cvv: '', name: '' },

                agreeTerms: false,

                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get tax() {
                    return Math.round(this.subtotal * 0.15);
                },

                get grandTotal() {
                    return this.subtotal + this.getShippingCost() + this.tax;
                },

                getShippingCost() {
                    const method = this.shippingMethods.find(m => m.id === this.selectedShipping);
                    return method ? method.price : 0;
                },

                getShippingMethodName() {
                    const method = this.shippingMethods.find(m => m.id === this.selectedShipping);
                    return method ? method.name + (method.price === 0 ? ' ({{ __('phonix::app.checkout.shipping.free') }})' : ' (' + this.formatPrice(method.price) + ')') : '';
                },

                getPaymentMethodName() {
                    const names = {
                        credit_card: '{{ __('phonix::app.checkout.payment.credit_card') }}',
                        bank_transfer: '{{ __('phonix::app.checkout.payment.bank_transfer') }}',
                        cash_on_delivery: '{{ __('phonix::app.checkout.payment.cash_on_delivery') }}',
                    };
                    return names[this.selectedPayment] || '';
                },

                formatPrice(amount) {
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR', minimumFractionDigits: 0 }).format(amount);
                },

                goToStep(step) {
                    this.currentStep = step;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                validateAddress() {
                    this.errors = {};
                    const required = ['first_name', 'last_name', 'email', 'phone', 'address1', 'city', 'state', 'postcode', 'country'];
                    let valid = true;

                    required.forEach(field => {
                        if (!this.address[field] || this.address[field].trim() === '') {
                            this.errors[field] = 'This field is required';
                            valid = false;
                        }
                    });

                    if (this.address.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.address.email)) {
                        this.errors.email = 'Please enter a valid email address';
                        valid = false;
                    }

                    if (this.address.phone && !/^[\+]?[\d\s\-\(\)]{7,}$/.test(this.address.phone)) {
                        this.errors.phone = 'Please enter a valid phone number';
                        valid = false;
                    }

                    if (valid) {
                        this.goToStep(1);
                    }
                },

                placeOrder() {
                    if (!this.agreeTerms) return;
                    window.location.href = '{{ route('phonix.checkout.success') }}';
                },
            };
        }
    </script>
    @endPushOnce

</x-phonix::layouts.index>
