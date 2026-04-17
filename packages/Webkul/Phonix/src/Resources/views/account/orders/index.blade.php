{{-- My Orders --}}
@php
    $customer = auth('customer')->user();

    $statusFilter = request('status', 'all');

    $ordersQuery = $customer->orders()->latest();

    if ($statusFilter !== 'all') {
        $ordersQuery->where('status', $statusFilter);
    }

    $allOrders = $customer->orders()->latest()->get();
    $paginatedOrders = $ordersQuery->paginate(10);

    $statusCounts = [
        'all'        => $allOrders->count(),
        'pending'    => $allOrders->where('status', 'pending')->count(),
        'processing' => $allOrders->where('status', 'processing')->count(),
        'completed'  => $allOrders->where('status', 'completed')->count(),
        'cancelled'  => $allOrders->where('status', 'cancelled')->count(),
    ];

    // Map to the structure used in the view
    $ordersData = $paginatedOrders->map(function ($order) {
        return [
            'id'     => $order->id,
            'ref'    => '#' . $order->increment_id,
            'date'   => $order->created_at->format('Y-m-d'),
            'status' => $order->status,
            'total'  => core()->currency($order->grand_total),
            'items'  => $order->items->count(),
        ];
    })->values()->all();

    $statusColors = [
        'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'shipped'    => 'bg-phoenix-100 text-phoenix-800 dark:bg-phoenix-900/30 dark:text-phoenix-300',
        'delivered'  => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'completed'  => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'cancelled'  => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    ];

    $filterTabs = [
        'all'        => __('phonix::app.account.orders.filter_all'),
        'pending'    => __('phonix::app.account.order_status.pending'),
        'processing' => __('phonix::app.account.order_status.processing'),
        'completed'  => __('phonix::app.account.order_status.completed'),
        'cancelled'  => __('phonix::app.account.order_status.cancelled'),
    ];
@endphp

<x-phonix::account.layout
    :title="__('phonix::app.account.orders.title')"
    :breadcrumbs="[['label' => __('phonix::app.account.orders.title')]]"
>
    <div class="space-y-[24px]">
        {{-- Page Title --}}
        <h1 class="text-fluid-xl font-bold text-slate-800 dark:text-slate-100" data-gsap="fade-up">
            @lang('phonix::app.account.orders.title')
        </h1>

        {{-- Filter Tabs --}}
        <div class="flex gap-[8px] overflow-x-auto scrollbar-thin pb-[4px]" data-gsap="fade-up" role="tablist" aria-label="@lang('phonix::app.account.orders.title')">
            @foreach ($filterTabs as $tabKey => $tabLabel)
                <a
                    href="{{ route('phonix.account.orders', $tabKey !== 'all' ? ['status' => $tabKey] : []) }}"
                    class="px-[16px] py-[8px] rounded-full text-sm font-medium whitespace-nowrap transition-all duration-200 {{ $statusFilter === $tabKey ? 'bg-phoenix-500 text-white dark:bg-phoenix-400 dark:text-phoenix-950 shadow-sm' : 'bg-white dark:bg-dark-card text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-dark-border hover:border-phoenix-300 dark:hover:border-phoenix-700' }}"
                    role="tab"
                    aria-selected="{{ $statusFilter === $tabKey ? 'true' : 'false' }}"
                >
                    {{ $tabLabel }} ({{ $statusCounts[$tabKey] ?? 0 }})
                </a>
            @endforeach
        </div>

        {{-- Orders Table (Desktop) --}}
        <div class="card-phoenix hidden md:block" data-gsap="fade-up">
            @if (count($ordersData) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" role="table">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-dark-border">
                                <th class="text-start p-[16px] font-medium text-slate-500 dark:text-slate-400">@lang('phonix::app.account.orders.order_id', ['id' => ''])</th>
                                <th class="text-start p-[16px] font-medium text-slate-500 dark:text-slate-400">@lang('phonix::app.account.orders.date')</th>
                                <th class="text-center p-[16px] font-medium text-slate-500 dark:text-slate-400">@lang('phonix::app.account.orders.items')</th>
                                <th class="text-start p-[16px] font-medium text-slate-500 dark:text-slate-400">@lang('phonix::app.account.orders.status')</th>
                                <th class="text-end p-[16px] font-medium text-slate-500 dark:text-slate-400">@lang('phonix::app.account.orders.total')</th>
                                <th class="text-end p-[16px] font-medium text-slate-500 dark:text-slate-400"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ordersData as $order)
                                <tr class="border-b border-slate-50 dark:border-dark-border/50 last:border-0 hover:bg-slate-50/50 dark:hover:bg-dark-surface/50 transition-colors">
                                    <td class="p-[16px] font-medium text-slate-800 dark:text-slate-200">{{ $order['ref'] }}</td>
                                    <td class="p-[16px] text-slate-600 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($order['date'])->format('M d, Y') }}
                                    </td>
                                    <td class="p-[16px] text-center text-slate-600 dark:text-slate-400">{{ $order['items'] }}</td>
                                    <td class="p-[16px]">
                                        <span class="inline-flex items-center px-[10px] py-[3px] rounded-full text-xs font-semibold capitalize {{ $statusColors[$order['status']] ?? 'bg-slate-100 text-slate-800' }}">
                                            {{ $order['status'] }}
                                        </span>
                                    </td>
                                    <td class="p-[16px] text-end font-semibold text-slate-800 dark:text-slate-200">{{ $order['total'] }}</td>
                                    <td class="p-[16px] text-end">
                                        <div class="flex items-center justify-end gap-[8px]">
                                            <a
                                                href="{{ route('phonix.account.orders.view', ['id' => $order['id']]) }}"
                                                class="text-sm text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 font-medium transition-colors"
                                            >
                                                @lang('phonix::app.account.orders.view')
                                            </a>
                                            @if (in_array($order['status'], ['shipped', 'processing']))
                                                <a
                                                    href="{{ route('phonix.account.orders.view', ['id' => $order['id']]) }}"
                                                    class="text-sm text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 font-medium transition-colors"
                                                >
                                                    @lang('phonix::app.account.orders.track')
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Empty State --}}
                <div class="py-[64px] text-center">
                    <svg class="w-[64px] h-[64px] mx-auto text-slate-300 dark:text-slate-600 mb-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-[8px]">
                        @lang('phonix::app.account.orders.no_orders')
                    </h3>
                    <a href="{{ route('phonix.products.index') }}" class="btn-phoenix mt-[16px]">
                        @lang('phonix::app.account.orders.start_shopping')
                    </a>
                </div>
            @endif
        </div>

        {{-- Orders Cards (Mobile) --}}
        <div class="md:hidden space-y-[12px]" data-gsap="fade-up">
            @if (count($ordersData) > 0)
                <div class="space-y-[12px]">
                    @foreach ($ordersData as $order)
                        <div class="card-phoenix p-[16px]">
                            <div class="flex items-center justify-between mb-[12px]">
                                <span class="font-semibold text-sm text-slate-800 dark:text-slate-200">{{ $order['ref'] }}</span>
                                <span class="inline-flex items-center px-[8px] py-[2px] rounded-full text-xs font-semibold capitalize {{ $statusColors[$order['status']] ?? 'bg-slate-100 text-slate-800' }}">
                                    {{ $order['status'] }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400 mb-[8px]">
                                <span>{{ \Carbon\Carbon::parse($order['date'])->format('M d, Y') }}</span>
                                <span>{{ $order['items'] }} item(s)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $order['total'] }}</span>
                                <a
                                    href="{{ route('phonix.account.orders.view', ['id' => $order['id']]) }}"
                                    class="btn-phoenix-ghost text-sm py-[6px] px-[12px]"
                                >
                                    @lang('phonix::app.account.orders.view')
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card-phoenix py-[48px] text-center">
                    <svg class="w-[48px] h-[48px] mx-auto text-slate-300 dark:text-slate-600 mb-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                    </svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-[12px]">
                        @lang('phonix::app.account.orders.no_orders')
                    </p>
                    <a href="{{ route('phonix.products.index') }}" class="btn-phoenix text-sm">
                        @lang('phonix::app.account.orders.start_shopping')
                    </a>
                </div>
            @endif
        </div>

        {{-- Pagination --}}
        @if ($paginatedOrders->hasPages())
            <div data-gsap="fade-up">
                {{ $paginatedOrders->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</x-phonix::account.layout>
