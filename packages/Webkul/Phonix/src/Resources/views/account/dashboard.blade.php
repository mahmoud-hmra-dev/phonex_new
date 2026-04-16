{{-- Account Dashboard --}}
@php
    $user = [
        'name'  => 'Ahmed Mohamed',
        'email' => 'ahmed@example.com',
        'phone' => '+966 50 123 4567',
    ];

    $stats = [
        ['key' => 'total_orders', 'value' => 12, 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z', 'color' => 'phoenix'],
        ['key' => 'wishlist_items', 'value' => 4, 'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z', 'color' => 'coral'],
        ['key' => 'pending_orders', 'value' => 2, 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'gold'],
        ['key' => 'completed_orders', 'value' => 8, 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'green'],
    ];

    $recentOrders = [
        ['id' => 'PNX-10001', 'date' => '2024-03-15', 'status' => 'delivered', 'total' => 5898, 'items' => 3],
        ['id' => 'PNX-10002', 'date' => '2024-03-20', 'status' => 'shipped', 'total' => 899, 'items' => 1],
        ['id' => 'PNX-10003', 'date' => '2024-03-22', 'status' => 'processing', 'total' => 2499, 'items' => 2],
        ['id' => 'PNX-10004', 'date' => '2024-03-25', 'status' => 'pending', 'total' => 349, 'items' => 1],
    ];

    $defaultAddress = [
        'name'    => 'Ahmed Mohamed',
        'phone'   => '+966 50 123 4567',
        'address' => '123 King Fahd Road',
        'city'    => 'Riyadh',
        'country' => 'Saudi Arabia',
    ];

    $statusColors = [
        'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'shipped'    => 'bg-phoenix-100 text-phoenix-800 dark:bg-phoenix-900/30 dark:text-phoenix-300',
        'delivered'  => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'cancelled'  => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    ];
@endphp

<x-phonix::account.layout
    :title="__('phonix::app.account.dashboard.title')"
    :breadcrumbs="[['label' => __('phonix::app.account.dashboard.title')]]"
>
    <div class="space-y-[24px]">
        {{-- Welcome Message --}}
        <div data-gsap="fade-up">
            <h1 class="text-fluid-xl font-bold text-slate-800 dark:text-slate-100">
                @lang('phonix::app.account.dashboard.welcome', ['name' => $user['name']])
            </h1>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-[16px]" data-gsap="fade-up">
            @foreach ($stats as $stat)
                <div class="card-phoenix p-[20px]">
                    <div class="flex items-center gap-[12px]">
                        <div class="w-[40px] h-[40px] rounded-md flex items-center justify-center shrink-0
                            {{ $stat['color'] === 'phoenix' ? 'bg-phoenix-100 text-phoenix-600 dark:bg-phoenix-900/30 dark:text-phoenix-400' : '' }}
                            {{ $stat['color'] === 'coral' ? 'bg-red-100 text-red-500 dark:bg-red-900/30 dark:text-red-400' : '' }}
                            {{ $stat['color'] === 'gold' ? 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                            {{ $stat['color'] === 'green' ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : '' }}
                        ">
                            <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $stat['value'] }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                @lang('phonix::app.account.dashboard.' . $stat['key'])
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Recent Orders --}}
        <div class="card-phoenix" data-gsap="fade-up">
            <div class="flex items-center justify-between p-[20px] border-b border-slate-100 dark:border-dark-border">
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                    @lang('phonix::app.account.dashboard.recent_orders')
                </h2>
                <a
                    href="{{ route('phonix.account.orders') }}"
                    class="text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors"
                >
                    @lang('phonix::app.account.dashboard.view_all_orders')
                </a>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm" role="table">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-dark-border">
                            <th class="text-start p-[16px] font-medium text-slate-500 dark:text-slate-400">@lang('phonix::app.account.orders.order_id', ['id' => ''])</th>
                            <th class="text-start p-[16px] font-medium text-slate-500 dark:text-slate-400">@lang('phonix::app.account.orders.date')</th>
                            <th class="text-start p-[16px] font-medium text-slate-500 dark:text-slate-400">@lang('phonix::app.account.orders.status')</th>
                            <th class="text-end p-[16px] font-medium text-slate-500 dark:text-slate-400">@lang('phonix::app.account.orders.total')</th>
                            <th class="text-end p-[16px] font-medium text-slate-500 dark:text-slate-400"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr class="border-b border-slate-50 dark:border-dark-border/50 last:border-0 hover:bg-slate-50/50 dark:hover:bg-dark-surface/50 transition-colors">
                                <td class="p-[16px] font-medium text-slate-800 dark:text-slate-200">{{ $order['id'] }}</td>
                                <td class="p-[16px] text-slate-600 dark:text-slate-400">{{ \Carbon\Carbon::parse($order['date'])->format('M d, Y') }}</td>
                                <td class="p-[16px]">
                                    <span class="inline-flex items-center px-[10px] py-[3px] rounded-full text-xs font-semibold {{ $statusColors[$order['status']] ?? '' }}">
                                        @lang('phonix::app.account.order_status.' . $order['status'])
                                    </span>
                                </td>
                                <td class="p-[16px] text-end font-semibold text-slate-800 dark:text-slate-200">${{ number_format($order['total'], 2) }}</td>
                                <td class="p-[16px] text-end">
                                    <a
                                        href="{{ route('phonix.account.orders.view', ['id' => $order['id']]) }}"
                                        class="text-sm text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 font-medium transition-colors"
                                    >
                                        @lang('phonix::app.account.orders.view')
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden p-[16px] space-y-[12px]">
                @foreach ($recentOrders as $order)
                    <div class="p-[12px] rounded-md bg-slate-50 dark:bg-dark-surface">
                        <div class="flex items-center justify-between mb-[8px]">
                            <span class="font-medium text-sm text-slate-800 dark:text-slate-200">{{ $order['id'] }}</span>
                            <span class="inline-flex items-center px-[8px] py-[2px] rounded-full text-xs font-semibold {{ $statusColors[$order['status']] ?? '' }}">
                                @lang('phonix::app.account.order_status.' . $order['status'])
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($order['date'])->format('M d, Y') }}</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">${{ number_format($order['total'], 2) }}</span>
                        </div>
                        <a
                            href="{{ route('phonix.account.orders.view', ['id' => $order['id']]) }}"
                            class="block mt-[8px] text-center text-sm text-phoenix-600 dark:text-phoenix-400 font-medium"
                        >
                            @lang('phonix::app.account.orders.view')
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Bottom Grid: Account Info + Default Address --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-[24px]">
            {{-- Account Info --}}
            <div class="card-phoenix p-[24px]" data-gsap="fade-up">
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-[16px]">
                    @lang('phonix::app.account.dashboard.account_info')
                </h2>
                <div class="space-y-[12px] text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.account.profile.name')</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $user['name'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.account.profile.email')</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $user['email'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">@lang('phonix::app.account.profile.phone')</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $user['phone'] }}</span>
                    </div>
                </div>
                <a
                    href="{{ route('phonix.account.profile') }}"
                    class="btn-phoenix-outline mt-[20px] w-full text-center text-sm py-[10px]"
                >
                    @lang('phonix::app.account.dashboard.edit_profile')
                </a>
            </div>

            {{-- Default Address --}}
            <div class="card-phoenix p-[24px]" data-gsap="fade-up">
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-[16px]">
                    @lang('phonix::app.account.dashboard.default_address')
                </h2>
                @if ($defaultAddress)
                    <div class="text-sm space-y-[4px] text-slate-600 dark:text-slate-400">
                        <p class="font-medium text-slate-800 dark:text-slate-200">{{ $defaultAddress['name'] }}</p>
                        <p>{{ $defaultAddress['phone'] }}</p>
                        <p>{{ $defaultAddress['address'] }}</p>
                        <p>{{ $defaultAddress['city'] }}, {{ $defaultAddress['country'] }}</p>
                    </div>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        @lang('phonix::app.account.dashboard.no_default_address')
                    </p>
                @endif
                <a
                    href="{{ route('phonix.account.addresses') }}"
                    class="btn-phoenix-outline mt-[20px] w-full text-center text-sm py-[10px]"
                >
                    @lang('phonix::app.account.dashboard.manage_addresses')
                </a>
            </div>
        </div>
    </div>
</x-phonix::account.layout>
