{{-- Phonix Theme - Mobile Navigation Drawer --}}

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
    class="fixed inset-y-0 start-0 z-50 w-[300px] max-w-[85vw] bg-white dark:bg-dark-bg shadow-xl overflow-y-auto scrollbar-thin lg:hidden"
    role="dialog"
    aria-modal="true"
    :aria-label="'@lang('phonix::app.header.nav.categories')'"
    @keydown.escape.window="mobileMenuOpen = false"
    x-cloak
>
    {{-- Drawer Header --}}
    <div class="flex items-center justify-between p-[16px] border-b border-slate-100 dark:border-dark-border">
        <a
            href="{{ url('/') }}"
            class="shrink-0"
            aria-label="@lang('phonix::app.theme.name')"
        >
            <span class="text-fluid-lg font-poppins font-bold tracking-tight text-gradient-phoenix">
                PHONIX
            </span>
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
    <nav class="p-[16px]" aria-label="@lang('phonix::app.header.nav.categories')">
        @php
            $mobileNavItems = [
                ['key' => 'home', 'url' => url('/')],
                ['key' => 'categories', 'url' => '#'],
                ['key' => 'deals', 'url' => '#'],
                ['key' => 'new_arrivals', 'url' => '#'],
                ['key' => 'brands', 'url' => '#'],
                ['key' => 'bestsellers', 'url' => '#'],
                ['key' => 'support', 'url' => '#'],
            ];
        @endphp

        <ul class="space-y-[4px]">
            @foreach ($mobileNavItems as $navItem)
                <li>
                    <a
                        href="{{ $navItem['url'] }}"
                        class="flex items-center gap-[12px] px-[12px] py-[12px] text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface hover:text-phoenix-600 dark:hover:text-phoenix-400 rounded-md transition-colors"
                    >
                        @lang('phonix::app.header.nav.' . $navItem['key'])
                    </a>
                </li>
            @endforeach
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
                        href="{{ route('shop.customer.session.index') }}"
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
                        href="{{ route('shop.customers.register.index') }}"
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
                    <a
                        href="{{ route('shop.customers.account.profile.index') }}"
                        class="flex items-center gap-[12px] px-[12px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-md transition-colors"
                    >
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        @lang('phonix::app.header.account.profile')
                    </a>
                </li>
                <li>
                    <a
                        href="{{ route('shop.customers.account.orders.index') }}"
                        class="flex items-center gap-[12px] px-[12px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-md transition-colors"
                    >
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        @lang('phonix::app.header.account.orders')
                    </a>
                </li>
                <li>
                    <a
                        href="{{ route('shop.customers.account.wishlist.index') }}"
                        class="flex items-center gap-[12px] px-[12px] py-[10px] text-sm text-slate-700 dark:text-slate-300 hover:bg-phoenix-50 dark:hover:bg-dark-surface rounded-md transition-colors"
                    >
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        @lang('phonix::app.header.account.wishlist')
                    </a>
                </li>
                <li>
                    <a
                        href="{{ route('shop.customer.session.destroy') }}"
                        class="flex items-center gap-[12px] px-[12px] py-[10px] text-sm text-coral hover:bg-red-50 dark:hover:bg-dark-surface rounded-md transition-colors"
                    >
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        @lang('phonix::app.header.account.logout')
                    </a>
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
        @if (core()->getCurrentChannel()->locales()->count() > 1)
            <div class="px-[12px]">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-[8px]">
                    @lang('phonix::app.general.language')
                </p>
                <div class="flex flex-wrap gap-[8px]">
                    @foreach (core()->getCurrentChannel()->locales()->orderBy('name')->get() as $locale)
                        <a
                            href="?locale={{ $locale->code }}"
                            class="px-[12px] py-[6px] text-sm rounded-md border transition-colors {{ $locale->code === app()->getLocale() ? 'bg-phoenix-500 text-white border-phoenix-500' : 'bg-white dark:bg-dark-card text-slate-600 dark:text-slate-400 border-slate-200 dark:border-dark-border hover:border-phoenix-400' }}"
                        >
                            {{ $locale->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
