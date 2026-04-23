{{-- Phonix Theme - Mobile Navigation Drawer --}}
@php
    $navCategories = app(\Webkul\Category\Repositories\CategoryRepository::class)
        ->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id);
@endphp

{{-- Backdrop --}}
<div
    x-show="mobileMenuOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="mobileMenuOpen = false"
    class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm lg:hidden"
    aria-hidden="true"
    x-cloak
></div>

{{-- Slide-in Sidebar --}}
<div
    x-show="mobileMenuOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="ltr:-translate-x-full rtl:translate-x-full"
    class="fixed inset-y-0 start-0 z-[60] w-[300px] max-w-[85vw] bg-white dark:bg-dark-bg shadow-xl overflow-y-auto scrollbar-thin lg:hidden"
    role="dialog"
    aria-modal="true"
    aria-label="@lang('phonix::app.general.menu')"
    @keydown.escape.window="mobileMenuOpen = false"
    x-cloak
>
    {{-- Drawer Header --}}
    <div class="flex items-center justify-between p-[16px] border-b border-slate-100 dark:border-dark-border">
        <a href="{{ route('phonix.home') }}" class="flex items-center shrink-0" @click="mobileMenuOpen = false" aria-label="@lang('phonix::app.theme.name')">
            <img src="{{ asset('phonix-logo.png') }}" alt="Phonix" class="h-[60px] w-auto">
        </a>
        <button
            @click="mobileMenuOpen = false"
            class="p-[8px] text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
            aria-label="@lang('phonix::app.general.close')"
        >
            <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Navigation Links --}}
    <nav class="p-[16px]" aria-label="@lang('phonix::app.general.menu')">
        <ul class="space-y-[4px]">
            <li>
                <a
                    href="{{ route('phonix.home') }}"
                    @click="mobileMenuOpen = false"
                    class="flex items-center gap-[12px] px-[12px] py-[12px] text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:text-phoenix-600 dark:hover:text-phoenix-400 rounded-md transition-colors"
                >
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    @lang('phonix::app.header.nav.home')
                </a>
            </li>
            <li x-data="{ catOpen: false }">
                <button
                    type="button"
                    @click="catOpen = !catOpen"
                    class="flex items-center justify-between w-full gap-[12px] px-[12px] py-[12px] text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:text-phoenix-600 dark:hover:text-phoenix-400 rounded-md transition-colors"
                    :aria-expanded="catOpen"
                >
                    <span class="flex items-center gap-[12px]">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        @lang('phonix::app.header.nav.categories')
                    </span>
                    <svg class="w-[16px] h-[16px] shrink-0 transition-transform" :class="catOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div
                    x-show="catOpen"
                    x-collapse
                    x-cloak
                    class="ms-[30px] mt-[4px] mb-[4px] space-y-[2px]"
                >
                    <a
                        href="{{ route('phonix.products.index') }}"
                        @click="mobileMenuOpen = false"
                        class="flex items-center gap-[8px] px-[12px] py-[8px] text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:text-phoenix-600 dark:hover:text-phoenix-400 rounded-md transition-colors"
                    >
                        <svg class="w-[14px] h-[14px] shrink-0 text-phoenix-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        @lang('phonix::app.listing.filters.all_categories')
                    </a>
                    @foreach ($navCategories as $navCat)
                        <a
                            href="{{ route('phonix.products.index', ['category_ids' => [$navCat->id]]) }}"
                            @click="mobileMenuOpen = false"
                            class="flex items-center gap-[8px] px-[12px] py-[8px] text-sm text-slate-600 dark:text-slate-400 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:text-phoenix-600 dark:hover:text-phoenix-400 rounded-md transition-colors"
                        >
                            <span class="w-[5px] h-[5px] rounded-full bg-phoenix-400 shrink-0"></span>
                            {{ $navCat->name }}
                        </a>
                    @endforeach
                </div>
            </li>
            <li>
                <a
                    href="{{ route('phonix.products.index', ['sort' => 'price-asc']) }}"
                    @click="mobileMenuOpen = false"
                    class="flex items-center gap-[12px] px-[12px] py-[12px] text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:text-phoenix-600 dark:hover:text-phoenix-400 rounded-md transition-colors"
                >
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    @lang('phonix::app.header.nav.deals')
                </a>
            </li>
            <li>
                <a
                    href="{{ route('phonix.products.index', ['sort' => 'created_at-desc']) }}"
                    @click="mobileMenuOpen = false"
                    class="flex items-center gap-[12px] px-[12px] py-[12px] text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:text-phoenix-600 dark:hover:text-phoenix-400 rounded-md transition-colors"
                >
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @lang('phonix::app.header.nav.new_arrivals')
                </a>
            </li>
            <li>
                <a
                    href="{{ route('phonix.products.index') }}"
                    @click="mobileMenuOpen = false"
                    class="flex items-center gap-[12px] px-[12px] py-[12px] text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:text-phoenix-600 dark:hover:text-phoenix-400 rounded-md transition-colors"
                >
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                    </svg>
                    @lang('phonix::app.header.nav.brands')
                </a>
            </li>
        </ul>
    </nav>

    {{-- Divider --}}
    <div class="border-t border-slate-100 dark:border-dark-border mx-[16px]"></div>

    {{-- Account Section --}}
    <div class="p-[16px]">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-[8px] px-[12px]">
            @lang('phonix::app.header.account.my_account')
        </p>

        <ul class="space-y-[4px]">
            @guest('customer')
                <li>
                    <a
                        href="{{ route('phonix.auth.login') }}"
                        @click="mobileMenuOpen = false"
                        class="flex items-center gap-[12px] px-[12px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-md transition-colors"
                    >
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        @lang('phonix::app.header.account.login')
                    </a>
                </li>
                <li>
                    <a
                        href="{{ route('phonix.auth.register') }}"
                        @click="mobileMenuOpen = false"
                        class="flex items-center gap-[12px] px-[12px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-md transition-colors"
                    >
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                        @lang('phonix::app.header.account.register')
                    </a>
                </li>
            @endguest

            @auth('customer')
                <li>
                    <a href="{{ route('phonix.account.dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center gap-[12px] px-[12px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-md transition-colors">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3h7.5v7.5h-7.5V3zM3.75 13.5h7.5V21h-7.5v-7.5zM13.5 3h7.5v7.5H13.5V3zM13.5 13.5h7.5V21H13.5v-7.5z"/></svg>
                        @lang('phonix::app.account.sidebar.dashboard')
                    </a>
                </li>
                <li>
                    <a href="{{ route('phonix.account.orders') }}" @click="mobileMenuOpen = false" class="flex items-center gap-[12px] px-[12px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-md transition-colors">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" /></svg>
                        @lang('phonix::app.header.account.orders')
                    </a>
                </li>
                <li>
                    <a href="{{ route('phonix.account.wishlist') }}" @click="mobileMenuOpen = false" class="flex items-center gap-[12px] px-[12px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-md transition-colors">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                        @lang('phonix::app.header.account.wishlist')
                    </a>
                </li>
                <li>
                    <a href="{{ route('phonix.account.profile') }}" @click="mobileMenuOpen = false" class="flex items-center gap-[12px] px-[12px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-md transition-colors">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        @lang('phonix::app.header.account.profile')
                    </a>
                </li>
                <li class="border-t border-slate-100 dark:border-dark-border pt-[4px] mt-[4px]">
                    <form action="{{ route('shop.customer.session.destroy') }}" method="POST" id="mobile-logout-form" data-turbo="false">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="flex items-center gap-[12px] w-full px-[12px] py-[10px] text-sm text-coral hover:bg-red-50 dark:hover:bg-dark-surface rounded-md transition-colors text-start"
                        >
                            <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                            @lang('phonix::app.header.account.logout')
                        </button>
                    </form>
                </li>
            @endauth
        </ul>
    </div>

    {{-- Divider --}}
    <div class="border-t border-slate-100 dark:border-dark-border mx-[16px]"></div>

    {{-- Settings: Dark Mode & Language --}}
    <div class="p-[16px] space-y-[12px]">
        {{-- Dark Mode Toggle --}}
        <div class="flex items-center justify-between px-[12px]">
            <span class="text-sm text-slate-600 dark:text-slate-400">
                @lang('phonix::app.header.dark_mode.toggle')
            </span>
            <button
                @click="darkMode = !darkMode"
                class="relative w-[44px] h-[24px] rounded-full transition-colors"
                :class="darkMode ? 'bg-phoenix-500' : 'bg-slate-300'"
                role="switch"
                :aria-checked="darkMode.toString()"
                aria-label="@lang('phonix::app.header.dark_mode.toggle')"
            >
                <span
                    class="absolute top-[2px] start-[2px] w-[20px] h-[20px] bg-white rounded-full shadow transition-transform"
                    :class="darkMode ? 'ltr:translate-x-[20px] rtl:-translate-x-[20px]' : ''"
                ></span>
            </button>
        </div>

        {{-- Language Switcher --}}
        @php
            $mobileLocales = app(\Webkul\Core\Repositories\LocaleRepository::class)->all();
            $mobileCurrentLocale = app()->getLocale();
        @endphp
        @if ($mobileLocales->count() > 1)
            <div class="px-[12px]">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-[8px]">
                    @lang('phonix::app.general.language')
                </p>
                <div class="flex flex-wrap gap-[8px]">
                    @foreach ($mobileLocales as $locale)
                        <a
                            href="{{ phonix_locale_url($locale->code) }}"
                            class="px-[12px] py-[6px] text-sm rounded-md border transition-colors {{ $locale->code === $mobileCurrentLocale ? 'bg-phoenix-500 text-white border-phoenix-500' : 'bg-white dark:bg-dark-card text-slate-600 dark:text-slate-400 border-slate-200 dark:border-dark-border hover:border-phoenix-400' }}"
                        >
                            {{ $locale->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Cart & Compare Quick Links --}}
    <div class="border-t border-slate-100 dark:border-dark-border mx-[16px]"></div>
    <div class="p-[16px] flex gap-[8px]">
        <a
            href="{{ route('phonix.cart.index') }}"
            @click="mobileMenuOpen = false"
            class="flex-1 flex items-center justify-center gap-[8px] py-[10px] px-[12px] bg-phoenix-500 hover:bg-phoenix-600 text-white text-sm font-medium rounded-md transition-colors"
        >
            <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
            </svg>
            @lang('phonix::app.header.cart.view_cart')
        </a>
        <a
            href="{{ route('phonix.checkout.index') }}"
            @click="mobileMenuOpen = false"
            class="flex-1 flex items-center justify-center gap-[8px] py-[10px] px-[12px] bg-slate-900 dark:bg-white hover:bg-slate-700 dark:hover:bg-slate-100 text-white dark:text-slate-900 text-sm font-medium rounded-md transition-colors"
        >
            @lang('phonix::app.header.cart.checkout')
        </a>
    </div>
</div>
