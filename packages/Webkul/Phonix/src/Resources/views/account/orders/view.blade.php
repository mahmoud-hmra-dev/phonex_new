{{-- Order Detail View --}}
@php
    $customer = auth('customer')->user();
    $order = app(\Webkul\Sales\Repositories\OrderRepository::class)->findOrFail($id);

    // Security: ensure order belongs to authenticated customer
    if ($order->customer_id !== $customer->id) {
        abort(403);
    }

    $statusColors = [
        'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'shipped'    => 'bg-phoenix-100 text-phoenix-800 dark:bg-phoenix-900/30 dark:text-phoenix-300',
        'delivered'  => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'completed'  => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'cancelled'  => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    ];

    $billingAddress = $order->billing_address;
    $shippingAddress = $order->shipping_address ?? $billingAddress;

    $timeline = [
        ['step' => 'order_placed', 'label' => __('phonix::app.account.orders.order_placed'), 'date' => $order->created_at->format('Y-m-d h:i A'), 'completed' => true],
        ['step' => 'processing', 'label' => __('phonix::app.account.order_status.processing'), 'date' => null, 'completed' => in_array($order->status, ['processing', 'shipped', 'delivered', 'completed'])],
        ['step' => 'shipped', 'label' => __('phonix::app.account.order_status.shipped'), 'date' => null, 'completed' => in_array($order->status, ['shipped', 'delivered', 'completed'])],
        ['step' => 'delivered', 'label' => __('phonix::app.account.order_status.delivered'), 'date' => null, 'completed' => in_array($order->status, ['delivered', 'completed'])],
    ];

    $currentStepIndex = collect($timeline)->search(fn($t) => !$t['completed']);
    if ($currentStepIndex === false) $currentStepIndex = count($timeline);
@endphp

<x-phonix::account.layout
    :title="__('phonix::app.account.orders.order_details')"
    :breadcrumbs="[
        ['label' => __('phonix::app.account.orders.title'), 'url' => route('phonix.account.orders')],
        ['label' => '#' . $order->increment_id],
    ]"
>
    <div class="space-y-[24px]">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-[12px]" data-gsap="fade-up">
            <div>
                <h1 class="text-fluid-xl font-bold text-slate-800 dark:text-slate-100">
                    @lang('phonix::app.account.orders.order_id', ['id' => '#' . $order->increment_id])
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-[4px]">
                    {{ $order->created_at->format('F d, Y') }}
                </p>
            </div>
            <span class="inline-flex items-center self-start px-[12px] py-[4px] rounded-full text-sm font-semibold {{ $statusColors[$order->status] ?? 'bg-slate-100 text-slate-800' }}">
                @lang('phonix::app.account.order_status.' . $order->status)
            </span>
        </div>

        {{-- Order Timeline --}}
        <div class="card-phoenix p-[24px]" data-gsap="fade-up">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-[24px]">
                @lang('phonix::app.account.orders.order_timeline')
            </h2>
            <div class="relative">
                @foreach ($timeline as $index => $step)
                    <div class="flex gap-[16px] {{ $index < count($timeline) - 1 ? 'pb-[32px]' : '' }}">
                        {{-- Step Indicator --}}
                        <div class="flex flex-col items-center">
                            <div class="w-[32px] h-[32px] rounded-full flex items-center justify-center shrink-0 z-10
                                {{ $step['completed'] ? 'bg-green-500 text-white' : ($index === $currentStepIndex ? 'bg-phoenix-500 text-white animate-pulse-glow' : 'bg-slate-200 dark:bg-dark-border text-slate-400 dark:text-slate-600') }}">
                                @if ($step['completed'])
                                    <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                @else
                                    <span class="text-xs font-bold">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            @if ($index < count($timeline) - 1)
                                <div class="w-[2px] flex-1 mt-[4px] {{ $step['completed'] ? 'bg-green-500' : 'bg-slate-200 dark:bg-dark-border' }}"></div>
                            @endif
                        </div>

                        {{-- Step Content --}}
                        <div class="pb-[4px]">
                            <p class="text-sm font-semibold {{ $step['completed'] ? 'text-slate-800 dark:text-slate-200' : ($index === $currentStepIndex ? 'text-phoenix-600 dark:text-phoenix-400' : 'text-slate-400 dark:text-slate-600') }}">
                                {{ $step['label'] }}
                            </p>
                            @if ($step['date'])
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-[2px]">
                                    {{ $step['date'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Order Items --}}
        <div class="card-phoenix" data-gsap="fade-up">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 p-[20px] border-b border-slate-100 dark:border-dark-border">
                @lang('phonix::app.account.orders.order_details')
            </h2>
            <div class="divide-y divide-slate-100 dark:divide-dark-border">
                @foreach ($order->items as $item)
                    @php
                        $itemImage = $item->product
                            ? (product_image()->getProductBaseImage($item->product)['small_image_url'] ?? null)
                            : null;
                    @endphp
                    <div class="flex gap-[16px] p-[20px]">
                        {{-- Image --}}
                        <div class="w-[64px] h-[64px] rounded-md bg-slate-100 dark:bg-dark-surface shrink-0 flex items-center justify-center overflow-hidden">
                            @if ($itemImage)
                                <img src="{{ $itemImage }}" alt="{{ $item->name }}" class="w-full h-full object-cover" />
                            @else
                                <svg class="w-[24px] h-[24px] text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                </svg>
                            @endif
                        </div>
                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $item->name }}</p>
                            @if ($item->additional && isset($item->additional['attributes']))
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-[2px]">
                                    @foreach ($item->additional['attributes'] as $attribute)
                                        {{ $attribute['attribute_name'] }}: {{ $attribute['option_label'] }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </p>
                            @endif
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                @lang('phonix::app.account.orders.quantity'): {{ (int) $item->qty_ordered }}
                            </p>
                        </div>
                        {{-- Price --}}
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 shrink-0">
                            {{ core()->currency($item->total) }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Order Summary --}}
            <div class="border-t border-slate-100 dark:border-dark-border p-[20px]">
                <div class="max-w-[320px] ms-auto space-y-[8px] text-sm">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>@lang('phonix::app.account.orders.subtotal')</span>
                        <span>{{ core()->currency($order->sub_total) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>@lang('phonix::app.account.orders.shipping')</span>
                        <span>{{ $order->shipping_amount > 0 ? core()->currency($order->shipping_amount) : __('phonix::app.product.free_shipping') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>@lang('phonix::app.account.orders.tax')</span>
                        <span>{{ core()->currency($order->tax_amount) }}</span>
                    </div>
                    @if ($order->discount_amount > 0)
                        <div class="flex justify-between text-green-600 dark:text-green-400">
                            <span>@lang('phonix::app.account.orders.discount')</span>
                            <span>-{{ core()->currency($order->discount_amount) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-slate-800 dark:text-slate-100 pt-[8px] border-t border-slate-100 dark:border-dark-border">
                        <span>@lang('phonix::app.account.orders.grand_total')</span>
                        <span>{{ core()->currency($order->grand_total) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Shipping & Payment --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-[24px]">
            {{-- Shipping Address --}}
            <div class="card-phoenix p-[24px]" data-gsap="fade-up">
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-[12px]">
                    @lang('phonix::app.account.orders.shipping_address')
                </h2>
                @if ($shippingAddress)
                    <div class="text-sm space-y-[4px] text-slate-600 dark:text-slate-400">
                        <p class="font-medium text-slate-800 dark:text-slate-200">{{ $shippingAddress->first_name . ' ' . $shippingAddress->last_name }}</p>
                        @if ($shippingAddress->phone)
                            <p>{{ $shippingAddress->phone }}</p>
                        @endif
                        <p>{{ $shippingAddress->address }}</p>
                        <p>{{ $shippingAddress->city }}@if ($shippingAddress->state), {{ $shippingAddress->state }}@endif {{ $shippingAddress->postcode }}</p>
                        <p>{{ $shippingAddress->country }}</p>
                    </div>
                @endif
            </div>

            {{-- Payment Method --}}
            <div class="card-phoenix p-[24px]" data-gsap="fade-up">
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-[12px]">
                    @lang('phonix::app.account.orders.payment_method')
                </h2>
                <div class="flex items-center gap-[12px] text-sm text-slate-600 dark:text-slate-400">
                    <div class="w-[40px] h-[28px] rounded bg-slate-100 dark:bg-dark-surface flex items-center justify-center">
                        <svg class="w-[20px] h-[20px] text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    @if ($order->payment)
                        <span>{{ $order->payment->method_title ?? $order->payment->method }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap gap-[12px]" data-gsap="fade-up">
            @if (in_array($order->status, ['shipped', 'processing']))
                <a href="{{ route('phonix.account.orders') }}" class="btn-phoenix text-sm inline-flex items-center gap-[8px]">
                    <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    @lang('phonix::app.account.orders.track')
                </a>
            @endif
        </div>
    </div>
</x-phonix::account.layout>
