{{-- Phonix Theme - Premium Sticky Header --}}
<header
    x-data="{
        scrolled: false,
        mobileMenuOpen: false,
        searchOpen: false,
        accountOpen: false,
    }"
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })"
    :class="scrolled ? 'glass-nav shadow-lg' : 'bg-white dark:bg-dark-bg border-b border-slate-100 dark:border-dark-border'"
    class="sticky top-0 z-50 transition-all duration-300"
    data-gsap="sticky-nav"
>
    {{-- Top Bar --}}
    <div class="hidden lg:block bg-phoenix-950 dark:bg-phoenix-950 text-white text-fluid-xs">
        <div class="container flex items-center justify-between py-1">
            {{-- Delivery Notice --}}
            <p class="flex items-center gap-[8px]">
                {{-- Truck icon --}}
                <svg class="w-[16px] h-[16px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25m-2.25 0h2.25m0 0V6.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h.375" />
                </svg>
                <span>@lang('phonix::app.features.free_shipping.title')</span>
            </p>

            {{-- Language & Currency --}}
            <div class="flex items-center gap-[16px]">
                {{-- Language Switcher --}}
                @if (core()->getCurrentChannel()->locales()->count() > 1)
                    <div
                        x-data="{ langOpen: false }"
                        class="relative"
                    >
                        <button
                            @click="langOpen = !langOpen"
                            @click.away="langOpen = false"
                            class="flex items-center gap-[4px] hover:text-phoenix-300 transition-colors"
                            aria-haspopup="true"
                            :aria-expanded="langOpen"
                            aria-label="@lang('phonix::app.header.language.switch')"
                        >
                            <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                            </svg>
                            <span>{{ core()->getCurrentLocale()->name }}</span>
                            <svg class="w-[12px] h-[12px] transition-transform" :class="langOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div
                            x-show="langOpen"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute end-0 top-full mt-[8px] w-[140px] bg-white dark:bg-dark-card rounded-md shadow-lg border border-slate-100 dark:border-dark-border overflow-hidden z-50"
                            x-cloak
                        >
                            @foreach (core()->getCurrentChannel()->locales()->orderBy('name')->get() as $locale)
                                <a
                                    href="?locale={{ $locale->code }}"
                                    class="block px-[12px] py-[8px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors {{ $locale->code === app()->getLocale() ? 'bg-phoenix-50 dark:bg-dark-surface text-phoenix-600 dark:text-phoenix-400 font-medium' : '' }}"
                                >
                                    {{ $locale->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Currency Switcher --}}
                @if (core()->getCurrentChannel()->currencies()->count() > 1)
                    <div
                        x-data="{ currOpen: false }"
                        class="relative"
                    >
                        <button
                            @click="currOpen = !currOpen"
                            @click.away="currOpen = false"
                            class="flex items-center gap-[4px] hover:text-phoenix-300 transition-colors"
                            aria-haspopup="true"
                            :aria-expanded="currOpen"
                            aria-label="@lang('phonix::app.general.currency')"
                        >
                            <span>{{ core()->getCurrentCurrency()->code }}</span>
                            <svg class="w-[12px] h-[12px] transition-transform" :class="currOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div
                            x-show="currOpen"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute end-0 top-full mt-[8px] w-[140px] bg-white dark:bg-dark-card rounded-md shadow-lg border border-slate-100 dark:border-dark-border overflow-hidden z-50"
                            x-cloak
                        >
                            @foreach (core()->getCurrentChannel()->currencies as $currency)
                                <a
                                    href="?currency={{ $currency->code }}"
                                    class="block px-[12px] py-[8px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors {{ $currency->code === core()->getCurrentCurrencyCode() ? 'bg-phoenix-50 dark:bg-dark-surface text-phoenix-600 dark:text-phoenix-400 font-medium' : '' }}"
                                >
                                    {{ $currency->code }} ({{ $currency->symbol }})
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Header --}}
    <div class="container">
        <div class="flex items-center justify-between py-[12px] lg:py-[16px] gap-[16px]">
            {{-- Mobile: Hamburger --}}
            <button
                @click="mobileMenuOpen = true"
                class="lg:hidden p-[8px] text-slate-600 dark:text-slate-300 hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors"
                aria-label="@lang('phonix::app.general.open') @lang('phonix::app.header.nav.menu', [], 'Menu')"
            >
                <svg class="w-[24px] h-[24px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            {{-- Logo --}}
            <a
                href="{{ url('/') }}"
                class="shrink-0"
                aria-label="@lang('phonix::app.theme.name')"
            >
                <span class="text-fluid-xl font-poppins font-bold tracking-tight text-gradient-phoenix">
                    PHONIX
                </span>
            </a>

            {{-- Desktop Navigation --}}
            <nav
                class="hidden lg:flex items-center gap-[24px]"
                aria-label="@lang('phonix::app.header.nav.categories')"
            >
                @php
                    $navItems = [
                        ['key' => 'home', 'url' => url('/')],
                        ['key' => 'categories', 'url' => '#'],
                        ['key' => 'deals', 'url' => '#'],
                        ['key' => 'new_arrivals', 'url' => '#'],
                        ['key' => 'brands', 'url' => '#'],
                    ];
                @endphp

                @foreach ($navItems as $navItem)
                    <a
                        href="{{ $navItem['url'] }}"
                        class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors relative after:content-[''] after:absolute after:bottom-[-4px] after:inset-x-0 after:h-[2px] after:bg-phoenix-500 after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:origin-center"
                    >
                        @lang('phonix::app.header.nav.' . $navItem['key'])
                    </a>
                @endforeach
            </nav>

            {{-- Search Bar (Desktop) --}}
            <div class="hidden md:flex flex-1 max-w-[400px] mx-[16px]">
                <div class="relative w-full">
                    <input
                        type="search"
                        class="input-phoenix pe-[40px] ps-[40px] py-[10px]"
                        placeholder="@lang('phonix::app.header.search.placeholder')"
                        aria-label="@lang('phonix::app.header.search.button')"
                    />
                    <svg class="absolute start-[12px] top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
            </div>

            {{-- Action Icons --}}
            <div class="flex items-center gap-[12px] lg:gap-[20px]">
                {{-- Mobile Search Toggle --}}
                <button
                    @click="searchOpen = !searchOpen"
                    class="md:hidden p-[8px] text-slate-600 dark:text-slate-300 hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors"
                    aria-label="@lang('phonix::app.header.search.button')"
                >
                    <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </button>

                {{-- Dark Mode Toggle --}}
                <button
                    @click="darkMode = !darkMode"
                    class="p-[8px] text-slate-600 dark:text-slate-300 hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors"
                    :aria-label="darkMode ? '@lang('phonix::app.header.dark_mode.light')' : '@lang('phonix::app.header.dark_mode.dark')'"
                >
                    {{-- Sun icon (shown in dark mode) --}}
                    <svg x-show="darkMode" class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                    {{-- Moon icon (shown in light mode) --}}
                    <svg x-show="!darkMode" class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>

                {{-- Wishlist --}}
                <a
                    href="{{ route('shop.customers.account.wishlist.index') }}"
                    class="relative p-[8px] text-slate-600 dark:text-slate-300 hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors"
                    aria-label="@lang('phonix::app.header.account.wishlist')"
                >
                    <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </a>

                {{-- Cart --}}
                <a
                    href="{{ route('shop.checkout.cart.index') }}"
                    class="relative p-[8px] text-slate-600 dark:text-slate-300 hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors"
                    aria-label="@lang('phonix::app.header.cart.title')"
                >
                    <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    {{-- Cart count badge --}}
                    <span
                        class="absolute -top-[2px] -end-[2px] flex items-center justify-center w-[18px] h-[18px] text-[10px] font-bold text-white bg-coral rounded-full"
                        x-data="{ count: 0 }"
                        x-show="count > 0"
                        x-text="count"
                        x-cloak
                    ></span>
                </a>

                {{-- Account --}}
                <div
                    x-data="{ accOpen: false }"
                    class="relative"
                >
                    <button
                        @click="accOpen = !accOpen"
                        @click.away="accOpen = false"
                        class="p-[8px] text-slate-600 dark:text-slate-300 hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors"
                        aria-haspopup="true"
                        :aria-expanded="accOpen"
                        aria-label="@lang('phonix::app.header.account.my_account')"
                    >
                        <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </button>

                    {{-- Account Dropdown --}}
                    <div
                        x-show="accOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute end-0 top-full mt-[8px] w-[200px] bg-white dark:bg-dark-card rounded-md shadow-lg border border-slate-100 dark:border-dark-border overflow-hidden z-50"
                        x-cloak
                    >
                        @guest('customer')
                            <a
                                href="{{ route('shop.customer.session.index') }}"
                                class="flex items-center gap-[8px] px-[16px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors"
                            >
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                </svg>
                                @lang('phonix::app.header.account.login')
                            </a>
                            <a
                                href="{{ route('shop.customers.register.index') }}"
                                class="flex items-center gap-[8px] px-[16px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors"
                            >
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                                </svg>
                                @lang('phonix::app.header.account.register')
                            </a>
                        @endguest

                        @auth('customer')
                            <div class="px-[16px] py-[10px] border-b border-slate-100 dark:border-dark-border">
                                <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                                    @lang('phonix::app.header.account.welcome', ['name' => auth('customer')->user()->first_name])
                                </p>
                            </div>
                            <a
                                href="{{ route('shop.customers.account.profile.index') }}"
                                class="flex items-center gap-[8px] px-[16px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors"
                            >
                                @lang('phonix::app.header.account.my_account')
                            </a>
                            <a
                                href="{{ route('shop.customers.account.orders.index') }}"
                                class="flex items-center gap-[8px] px-[16px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors"
                            >
                                @lang('phonix::app.header.account.orders')
                            </a>
                            <a
                                href="{{ route('shop.customers.account.wishlist.index') }}"
                                class="flex items-center gap-[8px] px-[16px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface transition-colors"
                            >
                                @lang('phonix::app.header.account.wishlist')
                            </a>
                            <div class="border-t border-slate-100 dark:border-dark-border">
                                <a
                                    href="{{ route('shop.customer.session.destroy') }}"
                                    class="flex items-center gap-[8px] px-[16px] py-[10px] text-sm text-coral hover:bg-red-50 dark:hover:bg-dark-surface transition-colors"
                                >
                                    @lang('phonix::app.header.account.logout')
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Search (expandable) --}}
        <div
            x-show="searchOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden pb-[12px]"
            x-cloak
        >
            <div class="relative">
                <input
                    type="search"
                    class="input-phoenix ps-[40px] pe-[16px] py-[10px] w-full"
                    placeholder="@lang('phonix::app.header.search.placeholder')"
                    aria-label="@lang('phonix::app.header.search.button')"
                />
                <svg class="absolute start-[12px] top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Mobile Navigation Drawer --}}
    <x-phonix::layouts.header.mobile-nav />
</header>
