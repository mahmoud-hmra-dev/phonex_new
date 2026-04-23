@php
    $navCategories = app(\Webkul\Category\Repositories\CategoryRepository::class)
        ->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id)
        ->filter(fn ($c) => $c->id !== 1)
        ->values();

    $featuredNav = $navCategories->take(6);

    // Live cart count
    $cart = \Webkul\Checkout\Facades\Cart::getCart();
    $cartItemCount = $cart?->items_qty ?? 0;

    $allLocales   = app(\Webkul\Core\Repositories\LocaleRepository::class)->all();
    $currentLocale = app()->getLocale();
@endphp

{{-- Phonix — Premium Sticky Header --}}
<header
    x-data="{
        scrolled: false,
        mobileMenuOpen: false,
        searchOpen: false,
        accOpen: false,
    }"
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })"
    :class="scrolled
        ? 'bg-white/85 dark:bg-dark-bg/85 backdrop-blur-xl shadow-[0_8px_24px_-12px_rgba(15,23,42,0.12)] border-b border-slate-200/60 dark:border-dark-border/60'
        : 'bg-white dark:bg-dark-bg border-b border-slate-100 dark:border-dark-border/50'"
    class="sticky top-0 z-50 transition-all duration-300"
>
    {{-- Promo Marquee --}}
    <div class="hidden lg:block relative text-white text-[11px] font-medium tracking-wide"
         style="background: linear-gradient(90deg, #0F0C29 0%, #1E1B4B 35%, #4338CA 70%, #FF4757 100%); background-size: 200% 100%;"
    >
        <div class="container flex items-center justify-between py-[7px]">
            <div class="flex items-center gap-[20px]">
                <span class="flex items-center gap-[6px]">
                    <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    @lang('phonix::app.deals.flash_deal') — @lang('phonix::app.deals.save_up_to', ['percent' => 60])
                </span>
                <span class="hidden xl:flex items-center gap-[6px] text-white/80">
                    <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @lang('phonix::app.features.free_shipping.title')
                </span>
                <span class="hidden xl:flex items-center gap-[6px] text-white/80">
                    <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    @lang('phonix::app.features.secure_payment.title')
                </span>
            </div>

            <div class="flex items-center gap-[16px] text-white/85">
                @if (core()->getCurrentChannel()->locales()->count() > 1)
                    <div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative">
                        <button type="button" @click.stop="open = !open" :class="open && 'text-white'" class="flex items-center gap-[4px] hover:text-white transition-colors" aria-haspopup="menu" :aria-expanded="open.toString()">
                            <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                            <span>{{ core()->getCurrentLocale()->name }}</span>
                            <svg class="w-[10px] h-[10px] transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute end-0 top-full mt-[8px] w-[160px] bg-white dark:bg-dark-card rounded-lg shadow-xl border border-slate-200 dark:border-dark-border overflow-hidden z-[60]" role="menu">
                            @foreach (core()->getCurrentChannel()->locales()->orderBy('name')->get() as $locale)
                                <a href="{{ phonix_locale_url($locale->code) }}" class="flex items-center gap-[8px] px-[12px] py-[9px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors {{ $locale->code === app()->getLocale() ? 'bg-phoenix-50 dark:bg-dark-surface text-phoenix-600 dark:text-phoenix-400 font-semibold' : '' }}" role="menuitem">
                                    @if ($locale->code === app()->getLocale())
                                        <svg class="w-[14px] h-[14px] text-phoenix-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    @else
                                        <span class="w-[14px] h-[14px] shrink-0"></span>
                                    @endif
                                    <span class="flex-1">{{ $locale->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (core()->getCurrentChannel()->currencies()->count() > 1)
                    <div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative">
                        <button type="button" @click.stop="open = !open" :class="open && 'text-white'" class="flex items-center gap-[4px] hover:text-white transition-colors" aria-haspopup="menu" :aria-expanded="open.toString()">
                            <span>{{ core()->getCurrentCurrency()->code }}</span>
                            <svg class="w-[10px] h-[10px] transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute end-0 top-full mt-[8px] w-[160px] bg-white dark:bg-dark-card rounded-lg shadow-xl border border-slate-200 dark:border-dark-border overflow-hidden z-[60]" role="menu">
                            @foreach (core()->getCurrentChannel()->currencies as $currency)
                                <a href="{{ phonix_switch_url(['currency' => $currency->code]) }}" class="block px-[12px] py-[9px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors {{ $currency->code === core()->getCurrentCurrencyCode() ? 'bg-phoenix-50 dark:bg-dark-surface text-phoenix-600 dark:text-phoenix-400 font-semibold' : '' }}" role="menuitem">{{ $currency->code }} ({{ $currency->symbol }})</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <a href="{{ auth('customer')->check() ? route('phonix.account.orders') : route('phonix.auth.login') }}" class="hidden md:flex items-center gap-[4px] hover:text-white transition-colors">
                    <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25"/></svg>
                    @lang('phonix::app.header.nav.track_order')
                </a>
            </div>
        </div>
    </div>

    {{-- Main Row --}}
    <div class="container">
        <div class="flex items-center justify-between gap-[20px] py-[14px] lg:py-[18px]">

            {{-- Mobile hamburger --}}
            <button
                @click="mobileMenuOpen = true"
                class="lg:hidden -ms-[6px] p-[8px] text-slate-700 dark:text-slate-200 hover:text-phoenix-500 transition-colors rounded-lg"
                aria-label="@lang('phonix::app.general.menu')"
            >
                <svg class="w-[24px] h-[24px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>

            {{-- Logo --}}
            <a href="{{ route('phonix.home') }}" class="flex items-center gap-[10px] shrink-0" aria-label="@lang('phonix::app.theme.name')">
                <span class="relative inline-flex items-center justify-center w-[38px] h-[38px] rounded-xl gradient-phoenix shadow-[0_6px_16px_-4px_rgba(79,70,229,0.5)]">
                    <svg class="w-[22px] h-[22px] text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 3l-6 9h5l-3 9 10-12h-6l3-6z"/>
                    </svg>
                </span>
                <span class="font-display text-[22px] font-bold tracking-tight text-slate-900 dark:text-white">
                    phonix<span class="text-phoenix-500 dark:text-phoenix-400">.</span>
                </span>
            </a>

            {{-- Search Bar (Desktop) --}}
            <form
                method="GET"
                action="{{ route('phonix.products.index') }}"
                class="hidden md:flex flex-1 max-w-[560px]"
                role="search"
            >
                <div class="relative w-full group">
                    <svg class="absolute start-[16px] top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-slate-400 pointer-events-none transition-colors group-focus-within:text-phoenix-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input
                        type="search"
                        name="query"
                        value="{{ request('query') }}"
                        class="w-full ps-[46px] pe-[120px] py-[12px] text-sm rounded-full border border-slate-200 dark:border-dark-border bg-slate-50 dark:bg-dark-surface focus:bg-white dark:focus:bg-dark-card focus:border-phoenix-400 focus:ring-4 focus:ring-phoenix-500/10 focus:outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400 transition-all"
                        placeholder="@lang('phonix::app.header.search.placeholder')"
                        aria-label="@lang('phonix::app.header.search.button')"
                        autocomplete="off"
                    />
                    <button
                        type="submit"
                        class="absolute end-[4px] top-1/2 -translate-y-1/2 btn-phoenix !rounded-full !py-[8px] !px-[18px] text-[13px]"
                        aria-label="@lang('phonix::app.header.search.button')"
                    >
                        @lang('phonix::app.header.search.button')
                    </button>
                </div>
            </form>

            {{-- Action Icons --}}
            <div class="flex items-center gap-[2px] sm:gap-[6px]">

                {{-- Mobile search toggle --}}
                <button
                    @click="searchOpen = !searchOpen"
                    class="md:hidden p-[10px] text-slate-700 dark:text-slate-200 hover:text-phoenix-500 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-xl transition-colors"
                    aria-label="@lang('phonix::app.header.search.button')"
                >
                    <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </button>

                {{-- Language quick toggle (mobile) --}}
                @if ($allLocales->count() > 1)
                    <div x-data="{ open: false }" @keydown.escape.window="open = false" class="lg:hidden relative">
                        <button
                            type="button"
                            @click.stop="open = !open"
                            class="inline-flex items-center gap-[4px] p-[10px] text-slate-700 dark:text-slate-200 hover:text-phoenix-500 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-xl transition-colors text-xs font-semibold uppercase"
                            aria-haspopup="menu"
                            :aria-expanded="open.toString()"
                            aria-label="@lang('phonix::app.header.language.switch')"
                        >
                            <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                            <span>{{ strtoupper($currentLocale) }}</span>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute end-0 top-full mt-[6px] w-[170px] bg-white dark:bg-dark-card rounded-xl shadow-xl border border-slate-200 dark:border-dark-border overflow-hidden z-[60]" role="menu">
                            @foreach ($allLocales as $locale)
                                <a href="{{ phonix_locale_url($locale->code) }}" class="flex items-center gap-[8px] px-[14px] py-[10px] text-sm transition-colors {{ $locale->code === $currentLocale ? 'bg-phoenix-50 dark:bg-dark-surface text-phoenix-600 dark:text-phoenix-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-dark-surface' }}" role="menuitem">
                                    @if ($locale->code === $currentLocale)
                                        <svg class="w-[14px] h-[14px] text-phoenix-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    @else
                                        <span class="w-[14px] h-[14px] shrink-0"></span>
                                    @endif
                                    <span class="flex-1">{{ $locale->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Dark mode toggle --}}
                <button
                    @click="darkMode = !darkMode"
                    class="p-[10px] text-slate-700 dark:text-slate-200 hover:text-phoenix-500 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-xl transition-colors"
                    :aria-label="darkMode ? '@lang('phonix::app.header.dark_mode.light')' : '@lang('phonix::app.header.dark_mode.dark')'"
                >
                    <svg x-show="darkMode" x-cloak class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                    <svg x-show="!darkMode" class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                </button>

                {{-- Wishlist --}}
                <a
                    href="{{ auth('customer')->check() ? route('phonix.account.wishlist') : route('phonix.auth.login') }}"
                    class="hidden sm:inline-flex p-[10px] text-slate-700 dark:text-slate-200 hover:text-plasma-500 hover:bg-plasma-50 dark:hover:bg-dark-surface rounded-xl transition-colors"
                    aria-label="@lang('phonix::app.header.account.wishlist')"
                >
                    <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                </a>

                {{-- Cart --}}
                <a
                    href="{{ route('phonix.cart.index') }}"
                    class="relative p-[10px] text-slate-700 dark:text-slate-200 hover:text-phoenix-500 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-xl transition-colors"
                    aria-label="@lang('phonix::app.header.cart.title')"
                >
                    <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .956-.343 1.087-.835l2.25-8.482a.75.75 0 00-.725-.952H5.106m0 0L4.32 2.272M7.5 14.25L5.106 5.272M7.5 14.25a3 3 0 00-3 3h15.75m-8.25 3.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm7.5 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    @if ($cartItemCount > 0)
                        <span class="absolute top-[2px] end-[2px] flex items-center justify-center min-w-[18px] h-[18px] px-[4px] text-[10px] font-bold text-white bg-plasma-500 rounded-full shadow-lg shadow-plasma-500/40 ring-2 ring-white dark:ring-dark-bg">
                            {{ $cartItemCount > 99 ? '99+' : $cartItemCount }}
                        </span>
                    @endif
                </a>

                {{-- Account --}}
                <div class="relative" x-data="{ accOpen: false }" @click.away="accOpen = false">
                    <button
                        @click="accOpen = !accOpen"
                        class="p-[10px] text-slate-700 dark:text-slate-200 hover:text-phoenix-500 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-xl transition-colors"
                        aria-haspopup="true"
                        :aria-expanded="accOpen"
                        aria-label="@lang('phonix::app.header.account.my_account')"
                    >
                        <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </button>

                    <div
                        x-show="accOpen" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        class="absolute end-0 top-full mt-[10px] w-[240px] bg-white dark:bg-dark-card rounded-2xl shadow-[0_16px_48px_-12px_rgba(15,23,42,0.2)] border border-slate-100 dark:border-dark-border overflow-hidden z-50 origin-top-end"
                    >
                        @guest('customer')
                            <div class="p-[20px] bg-gradient-to-br from-phoenix-50 to-white dark:from-phoenix-900/30 dark:to-dark-card">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white mb-[4px]">@lang('phonix::app.header.account.my_account')</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-[12px]">@lang('phonix::app.auth.login.no_account')</p>
                                <a href="{{ route('phonix.auth.login') }}" class="btn-phoenix w-full !py-[10px] !text-xs">
                                    @lang('phonix::app.header.account.login')
                                </a>
                                <a href="{{ route('phonix.auth.register') }}" class="block text-center mt-[8px] text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:underline">
                                    @lang('phonix::app.header.account.register')
                                </a>
                            </div>
                        @endguest

                        @auth('customer')
                            <div class="px-[18px] py-[14px] border-b border-slate-100 dark:border-dark-border bg-gradient-to-br from-phoenix-50 to-white dark:from-phoenix-900/20 dark:to-dark-card">
                                <p class="text-xs text-slate-500 dark:text-slate-400">@lang('phonix::app.header.account.welcome', ['name' => auth('customer')->user()->first_name])</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ auth('customer')->user()->email }}</p>
                            </div>
                            <div class="py-[4px]">
                                @foreach ([
                                    ['route' => 'phonix.account.dashboard', 'label' => 'account.sidebar.dashboard', 'icon' => 'M3.75 3h7.5v7.5h-7.5V3zM3.75 13.5h7.5V21h-7.5v-7.5zM13.5 3h7.5v7.5H13.5V3zM13.5 13.5h7.5V21H13.5v-7.5z'],
                                    ['route' => 'phonix.account.orders',    'label' => 'header.account.orders',    'icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193'],
                                    ['route' => 'phonix.account.wishlist',  'label' => 'header.account.wishlist',  'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z'],
                                    ['route' => 'phonix.account.addresses', 'label' => 'header.account.addresses',  'icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z'],
                                    ['route' => 'phonix.account.profile',   'label' => 'header.account.profile',    'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
                                ] as $item)
                                    <a href="{{ route($item['route']) }}" class="flex items-center gap-[10px] px-[18px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors">
                                        <svg class="w-[16px] h-[16px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                                        @lang('phonix::app.' . $item['label'])
                                    </a>
                                @endforeach
                            </div>
                            <div class="border-t border-slate-100 dark:border-dark-border">
                                <form action="{{ route('shop.customer.session.destroy') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex items-center gap-[10px] w-full px-[18px] py-[12px] text-sm text-plasma-500 hover:bg-plasma-50 dark:hover:bg-dark-surface transition-colors text-start font-medium">
                                        <svg class="w-[16px] h-[16px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                                        @lang('phonix::app.header.account.logout')
                                    </button>
                                </form>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile search (expandable) --}}
        <div
            x-show="searchOpen" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="md:hidden pb-[14px]"
        >
            <form method="GET" action="{{ route('phonix.products.index') }}" role="search">
                <div class="relative">
                    <svg class="absolute start-[14px] top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input
                        type="search"
                        name="query"
                        value="{{ request('query') }}"
                        class="w-full ps-[44px] pe-[90px] py-[12px] text-sm rounded-full border border-slate-200 dark:border-dark-border bg-slate-50 dark:bg-dark-surface focus:bg-white dark:focus:bg-dark-card focus:border-phoenix-400 focus:outline-none"
                        placeholder="@lang('phonix::app.header.search.placeholder')"
                        aria-label="@lang('phonix::app.header.search.button')"
                        autocomplete="off"
                    />
                    <button type="submit" class="absolute end-[4px] top-1/2 -translate-y-1/2 btn-phoenix !rounded-full !py-[8px] !px-[16px] text-[12px]">
                        @lang('phonix::app.header.search.button')
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Category Nav Strip (desktop) --}}
    <div class="hidden lg:block border-t border-slate-100 dark:border-dark-border/50 bg-white/80 dark:bg-dark-bg/80">
        <div class="container">
            <nav class="flex items-center gap-[4px] h-[48px]" aria-label="Main navigation">
                {{-- All Categories mega dropdown --}}
                <div x-data="{ open: false }" class="relative h-full" @click.away="open = false">
                    <button
                        @click="open = !open"
                        class="h-full flex items-center gap-[8px] px-[16px] text-sm font-semibold text-white gradient-phoenix rounded-t-xl -my-[1px] transition-all"
                        :aria-expanded="open"
                    >
                        <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        @lang('phonix::app.header.nav.categories')
                        <svg class="w-[14px] h-[14px] transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </button>

                    {{-- Mega panel --}}
                    <div
                        x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute start-0 top-full mt-[4px] w-[320px] bg-white dark:bg-dark-card rounded-2xl shadow-[0_20px_48px_-12px_rgba(15,23,42,0.25)] border border-slate-100 dark:border-dark-border overflow-hidden z-50 py-[8px]"
                    >
                        <a href="{{ route('phonix.products.index') }}" class="flex items-center gap-[10px] px-[16px] py-[12px] text-sm font-semibold text-phoenix-600 dark:text-phoenix-400 hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors">
                            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                            @lang('phonix::app.listing.filters.all_categories')
                        </a>
                        <div class="divider-soft my-[4px]"></div>
                        <div class="max-h-[480px] overflow-y-auto scrollbar-thin">
                            @foreach ($navCategories as $navCat)
                                <a href="{{ route('phonix.products.index', ['category_ids' => [$navCat->id]]) }}" class="flex items-center gap-[12px] px-[16px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors group">
                                    @if($navCat->logo_url)
                                        <img src="{{ $navCat->logo_url }}" alt="" class="w-[28px] h-[28px] rounded-lg object-cover bg-slate-100"/>
                                    @else
                                        <span class="w-[28px] h-[28px] rounded-lg bg-phoenix-50 dark:bg-phoenix-900/40 flex items-center justify-center text-phoenix-500">
                                            <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5"/></svg>
                                        </span>
                                    @endif
                                    <span class="flex-1 font-medium">{{ $navCat->name }}</span>
                                    <svg class="w-[14px] h-[14px] opacity-0 group-hover:opacity-100 rtl:rotate-180 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Primary nav --}}
                <a href="{{ route('phonix.home') }}" class="px-[14px] py-[10px] text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors rounded-lg hover:bg-phoenix-50 dark:hover:bg-dark-surface">@lang('phonix::app.header.nav.home')</a>

                @foreach ($featuredNav->take(5) as $navCat)
                    <a href="{{ route('phonix.products.index', ['category_ids' => [$navCat->id]]) }}" class="px-[14px] py-[10px] text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors rounded-lg hover:bg-phoenix-50 dark:hover:bg-dark-surface">{{ $navCat->name }}</a>
                @endforeach

                <a href="{{ route('phonix.products.index', ['sort' => 'price-asc']) }}" class="ms-auto px-[14px] py-[10px] text-sm font-semibold text-plasma-500 hover:bg-plasma-50 dark:hover:bg-plasma-900/20 rounded-lg transition-colors flex items-center gap-[6px]">
                    <svg class="w-[14px] h-[14px]" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    @lang('phonix::app.header.nav.deals')
                </a>
                <a href="{{ route('phonix.products.index', ['sort' => 'created_at-desc']) }}" class="px-[14px] py-[10px] text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors rounded-lg hover:bg-phoenix-50 dark:hover:bg-dark-surface">@lang('phonix::app.header.nav.new_arrivals')</a>
            </nav>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <x-phonix::layouts.header.mobile-nav />
</header>
