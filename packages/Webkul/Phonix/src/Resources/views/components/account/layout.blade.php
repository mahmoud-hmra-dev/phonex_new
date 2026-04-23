{{-- Account Layout — Shared layout for all account pages --}}
@props([
    'title' => '',
    'breadcrumbs' => [],
])

@php
    $accountNav = [
        ['key' => 'dashboard', 'route' => 'phonix.account.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['key' => 'orders', 'route' => 'phonix.account.orders', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
        ['key' => 'addresses', 'route' => 'phonix.account.addresses', 'icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z'],
        ['key' => 'wishlist', 'route' => 'phonix.account.wishlist', 'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z'],
        ['key' => 'reviews', 'route' => 'phonix.account.reviews', 'icon' => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z'],
        ['key' => 'profile', 'route' => 'phonix.account.profile', 'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
    ];

    $currentRoute = request()->route()?->getName() ?? '';

    $customer = auth('customer')->user();
    $fullName = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
    $initials = collect(array_filter([$customer->first_name ?? '', $customer->last_name ?? '']))
        ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->take(2)->implode('');
@endphp

<x-phonix::layouts.index :title="$title">
    <div class="bg-slate-50 dark:bg-dark-bg min-h-screen">
        {{-- Breadcrumb --}}
        <div class="container mx-auto">
            <x-phonix::breadcrumb :items="array_merge([
                ['label' => __('phonix::app.general.home'), 'url' => route('phonix.home')],
                ['label' => __('phonix::app.account.title'), 'url' => route('phonix.account.dashboard')],
            ], $breadcrumbs)" />
        </div>

        <div class="container mx-auto pb-[64px]">
            <div
                class="flex flex-col lg:flex-row gap-[24px]"
                x-data="{ sidebarOpen: false }"
            >
                {{-- Mobile Sidebar Toggle --}}
                <div class="lg:hidden">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="flex items-center gap-[8px] w-full p-[12px] card-phoenix text-sm font-medium text-slate-700 dark:text-slate-300"
                        :aria-expanded="sidebarOpen"
                        aria-controls="account-sidebar"
                    >
                        <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        @lang('phonix::app.general.menu')
                        <svg
                            class="w-[16px] h-[16px] ms-auto transition-transform"
                            :class="{ 'rotate-180': sidebarOpen }"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                </div>

                {{-- Sidebar --}}
                <aside
                    id="account-sidebar"
                    class="w-full lg:w-[280px] shrink-0"
                    :class="{ 'hidden lg:block': !sidebarOpen }"
                    data-gsap="fade-up"
                >
                    <div class="card-phoenix overflow-hidden">
                        {{-- User Info --}}
                        <div class="p-[24px] border-b border-slate-100 dark:border-dark-border">
                            <div class="flex items-center gap-[12px]">
                                <div class="w-[48px] h-[48px] rounded-full gradient-phoenix flex items-center justify-center text-white font-bold text-base shrink-0">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">
                                        {{ $fullName }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                        {{ $customer->email }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Nav Links --}}
                        <nav class="p-[8px]" aria-label="@lang('phonix::app.account.title')">
                            <ul class="space-y-[2px]">
                                @foreach ($accountNav as $nav)
                                    @php
                                        $isActive = $nav['route'] !== '#' && str_starts_with($currentRoute, $nav['route']);
                                    @endphp
                                    <li>
                                        <a
                                            href="{{ $nav['route'] !== '#' ? route($nav['route']) : '#' }}"
                                            class="flex items-center gap-[12px] px-[16px] py-[10px] rounded-md text-sm font-medium transition-all duration-200
                                                {{ $isActive
                                                    ? 'bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-700 dark:text-phoenix-300 border-s-[3px] border-phoenix-500 rtl:border-s-0 rtl:border-e-[3px]'
                                                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-surface hover:text-slate-800 dark:hover:text-slate-200' }}"
                                            @if($isActive) aria-current="page" @endif
                                        >
                                            <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $nav['icon'] }}" />
                                            </svg>
                                            @lang('phonix::app.account.sidebar.' . $nav['key'])
                                        </a>
                                    </li>
                                @endforeach

                                {{-- Logout --}}
                                <li class="pt-[8px] mt-[8px] border-t border-slate-100 dark:border-dark-border">
                                    <a
                                        href="#"
                                        class="flex items-center gap-[12px] px-[16px] py-[10px] rounded-md text-sm font-medium text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                        onclick="event.preventDefault(); if(confirm('{{ __('phonix::app.messages.confirm.logout') }}')) { document.getElementById('logout-form').submit(); }"
                                    >
                                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                        </svg>
                                        @lang('phonix::app.account.sidebar.logout')
                                    </a>
                                    <form id="logout-form" action="{{ route('shop.customer.session.destroy') }}" method="POST" data-turbo="false" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </aside>

                {{-- Main Content --}}
                <div class="flex-1 min-w-0">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</x-phonix::layouts.index>
