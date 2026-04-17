@php
    use Illuminate\Support\Str;

    // $products is passed from route as a Collection of Product models.
    // $compareAttributes: the flat attribute keys rendered as extra rows.
    $compareAttributes = ['sku', 'type', 'weight'];

    // Build per-product data array used throughout the template.
    $compareData = $products->map(function ($product) {
        $image      = product_image()->getProductBaseImage($product);
        $hasDiscount = $product->getTypeInstance()->haveDiscount();

        return [
            'id'                => $product->id,
            'name'              => $product->name,
            'url'               => route('phonix.products.view', ['slug' => $product->url_key]),
            'image'             => $image['medium_image_url'] ?? null,
            'price'             => $hasDiscount
                ? core()->currency($product->getTypeInstance()->getMinimalPrice())
                : core()->currency($product->price),
            'original_price'    => $hasDiscount ? core()->currency($product->price) : null,
            'has_discount'      => $hasDiscount,
            'rating'            => $product->reviews->count() > 0
                ? round($product->reviews->avg('rating'))
                : 0,
            'reviews_count'     => $product->reviews->count(),
            'sku'               => $product->sku ?? '—',
            'type'              => $product->type ?? '—',
            'weight'            => $product->weight ?? '—',
            'in_stock'          => $product->getTypeInstance()->isSaleable(),
            'short_description' => Str::limit(strip_tags($product->short_description ?? ''), 100),
        ];
    });

    // Helper: detect whether all values for a given key are identical across products.
    // Returns true when values differ (used to apply highlight classes).
    $valuesDiffer = function (string $key) use ($compareData): bool {
        $values = $compareData->pluck($key)->map(fn ($v) => (string) $v)->unique();
        return $values->count() > 1;
    };
@endphp

<x-phonix::layouts.index :title="__('phonix::app.misc.compare.title') . ' — Phonix'">

    <div
        x-data="{
            removeProduct(id) {
                let list = JSON.parse(localStorage.getItem('phonix_compare') || '[]');
                list = list.filter(i => i != id);
                localStorage.setItem('phonix_compare', JSON.stringify(list));
                window.location.href = '{{ route('phonix.compare.index') }}?ids=' + list.join(',');
            }
        }"
        class="container mx-auto section-padding"
    >

        {{-- ── Breadcrumb ─────────────────────────────────────────────── --}}
        <x-phonix::breadcrumb
            :items="[
                ['label' => __('phonix::app.general.home'), 'url' => '/'],
                ['label' => __('phonix::app.general.shop'), 'url' => route('phonix.products.index')],
                ['label' => __('phonix::app.misc.compare.title')],
            ]"
            class="mb-[28px]"
            data-gsap="fade-up"
        />

        {{-- ── Page heading ────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-[12px] mb-[32px]" data-gsap="fade-up">
            <div>
                <h1 class="text-fluid-2xl font-bold text-slate-900 dark:text-white">
                    @lang('phonix::app.misc.compare.title')
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-[4px]">
                    @lang('phonix::app.misc.compare.comparing_label', ['count' => $compareData->count()])
                </p>
            </div>
            @if ($compareData->isNotEmpty())
                <button
                    onclick="
                        localStorage.removeItem('phonix_compare');
                        window.location.href='{{ route('phonix.compare.index') }}';
                    "
                    class="inline-flex items-center gap-[6px] text-sm font-medium text-coral hover:text-coral/80 transition-colors"
                >
                    <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    @lang('phonix::app.misc.compare.clear_all')
                </button>
            @endif
        </div>

        {{-- ── Empty state ─────────────────────────────────────────────── --}}
        @if ($compareData->isEmpty())
            <div class="flex flex-col items-center justify-center py-[96px] text-center" data-gsap="fade-up">
                <div class="w-[120px] h-[120px] rounded-full bg-phoenix-50 dark:bg-phoenix-900/20 flex items-center justify-center mb-[32px]">
                    <svg class="w-[56px] h-[56px] text-phoenix-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                </div>
                <h2 class="text-fluid-xl font-semibold text-slate-800 dark:text-white mb-[12px]">
                    @lang('phonix::app.misc.compare.empty')
                </h2>
                <p class="text-slate-500 dark:text-slate-400 max-w-md mb-[32px]">
                    @lang('phonix::app.misc.compare.no_products')
                </p>
                <x-phonix::button variant="primary" size="md" :href="route('phonix.products.index')">
                    @lang('phonix::app.general.shop')
                </x-phonix::button>
            </div>

        {{-- ── Comparison table ────────────────────────────────────────── --}}
        @else
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-dark-border shadow-sm" data-gsap="fade-up">
                <table class="w-full border-collapse" style="min-width: {{ 160 + ($compareData->count() * 220) }}px">

                    {{-- ════════════════════════════════════════════
                         Row 1 – Product images + remove buttons
                    ════════════════════════════════════════════ --}}
                    <tr class="border-b border-slate-200 dark:border-dark-border">

                        {{-- Label cell (sticky) --}}
                        <th
                            scope="col"
                            class="sticky start-0 z-10 w-[160px] bg-slate-50 dark:bg-dark-surface text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-[20px] py-[20px] border-e border-slate-200 dark:border-dark-border"
                            aria-label="@lang('phonix::app.misc.compare.product_label')"
                        >
                            @lang('phonix::app.misc.compare.product_label')
                        </th>

                        {{-- Product columns --}}
                        @foreach ($compareData as $item)
                            <td class="align-top px-[20px] py-[20px] bg-white dark:bg-dark-card border-e border-slate-200 dark:border-dark-border last:border-e-0 min-w-[220px]">
                                <div class="relative group">
                                    {{-- Remove button --}}
                                    <button
                                        @click="removeProduct({{ $item['id'] }})"
                                        class="absolute top-0 end-0 z-10 w-[28px] h-[28px] flex items-center justify-center rounded-full bg-white dark:bg-dark-surface border border-slate-200 dark:border-dark-border text-slate-400 hover:text-coral hover:border-coral transition-colors shadow-sm"
                                        :aria-label="'{{ __('phonix::app.misc.compare.remove') }}'"
                                    >
                                        <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>

                                    {{-- Product image --}}
                                    <a href="{{ $item['url'] }}" class="block">
                                        <div class="aspect-square w-full max-w-[200px] mx-auto rounded-lg overflow-hidden bg-slate-50 dark:bg-dark-surface mb-[12px]">
                                            @if ($item['image'])
                                                <img
                                                    src="{{ $item['image'] }}"
                                                    alt="{{ $item['name'] }}"
                                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                    loading="lazy"
                                                />
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                                    <svg class="w-[48px] h-[48px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            </td>
                        @endforeach
                    </tr>

                    {{-- ════════════════════════════════════════════
                         Row 2 – Product name
                    ════════════════════════════════════════════ --}}
                    <tr class="border-b border-slate-200 dark:border-dark-border">
                        <th
                            scope="row"
                            class="sticky start-0 z-10 bg-slate-50 dark:bg-dark-surface text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-[20px] py-[16px] border-e border-slate-200 dark:border-dark-border"
                        >
                            @lang('phonix::app.misc.compare.rows.name')
                        </th>
                        @foreach ($compareData as $item)
                            <td class="px-[20px] py-[16px] bg-white dark:bg-dark-card border-e border-slate-200 dark:border-dark-border last:border-e-0">
                                <a
                                    href="{{ $item['url'] }}"
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-100 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors leading-snug line-clamp-3"
                                >
                                    {{ $item['name'] }}
                                </a>
                            </td>
                        @endforeach
                    </tr>

                    {{-- ════════════════════════════════════════════
                         Row 3 – Price
                    ════════════════════════════════════════════ --}}
                    <tr class="border-b border-slate-200 dark:border-dark-border {{ $valuesDiffer('price') ? 'bg-amber-50/60 dark:bg-amber-900/10' : '' }}">
                        <th
                            scope="row"
                            class="sticky start-0 z-10 bg-slate-50 dark:bg-dark-surface text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-[20px] py-[16px] border-e border-slate-200 dark:border-dark-border"
                        >
                            @lang('phonix::app.misc.compare.rows.price')
                        </th>
                        @foreach ($compareData as $item)
                            <td class="px-[20px] py-[16px] bg-white dark:bg-dark-card border-e border-slate-200 dark:border-dark-border last:border-e-0 {{ $valuesDiffer('price') ? 'bg-amber-50/40 dark:bg-amber-900/10' : '' }}">
                                <div class="flex flex-col gap-[2px]">
                                    <span class="text-base font-bold text-phoenix-600 dark:text-phoenix-400">
                                        {{ $item['price'] }}
                                    </span>
                                    @if ($item['original_price'])
                                        <span class="text-sm text-slate-400 line-through">
                                            {{ $item['original_price'] }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>

                    {{-- ════════════════════════════════════════════
                         Row 4 – Rating
                    ════════════════════════════════════════════ --}}
                    <tr class="border-b border-slate-200 dark:border-dark-border {{ $valuesDiffer('rating') ? 'bg-amber-50/60 dark:bg-amber-900/10' : '' }}">
                        <th
                            scope="row"
                            class="sticky start-0 z-10 bg-slate-50 dark:bg-dark-surface text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-[20px] py-[16px] border-e border-slate-200 dark:border-dark-border"
                        >
                            @lang('phonix::app.misc.compare.rows.rating')
                        </th>
                        @foreach ($compareData as $item)
                            <td class="px-[20px] py-[16px] bg-white dark:bg-dark-card border-e border-slate-200 dark:border-dark-border last:border-e-0 {{ $valuesDiffer('rating') ? 'bg-amber-50/40 dark:bg-amber-900/10' : '' }}">
                                <div class="flex flex-col gap-[4px]">
                                    <div class="flex items-center gap-[2px]" aria-label="{{ $item['rating'] }} @lang('phonix::app.misc.compare.stars_out_of_five')">
                                        @for ($star = 1; $star <= 5; $star++)
                                            <svg
                                                class="w-[14px] h-[14px] {{ $star <= $item['rating'] ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                aria-hidden="true"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    @if ($item['reviews_count'] > 0)
                                        <span class="text-xs text-slate-400 dark:text-slate-500">
                                            @lang('phonix::app.product.reviews_count', ['count' => $item['reviews_count']])
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500">
                                            @lang('phonix::app.misc.compare.no_reviews')
                                        </span>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>

                    {{-- ════════════════════════════════════════════
                         Row 5 – SKU
                    ════════════════════════════════════════════ --}}
                    <tr class="border-b border-slate-200 dark:border-dark-border {{ $valuesDiffer('sku') ? 'bg-amber-50/60 dark:bg-amber-900/10' : '' }}">
                        <th
                            scope="row"
                            class="sticky start-0 z-10 bg-slate-50 dark:bg-dark-surface text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-[20px] py-[16px] border-e border-slate-200 dark:border-dark-border"
                        >
                            @lang('phonix::app.product.sku')
                        </th>
                        @foreach ($compareData as $item)
                            <td class="px-[20px] py-[16px] text-sm text-slate-700 dark:text-slate-300 font-mono bg-white dark:bg-dark-card border-e border-slate-200 dark:border-dark-border last:border-e-0 {{ $valuesDiffer('sku') ? 'bg-amber-50/40 dark:bg-amber-900/10' : '' }}">
                                {{ $item['sku'] }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- ════════════════════════════════════════════
                         Row 6 – Stock status
                    ════════════════════════════════════════════ --}}
                    <tr class="border-b border-slate-200 dark:border-dark-border {{ $valuesDiffer('in_stock') ? 'bg-amber-50/60 dark:bg-amber-900/10' : '' }}">
                        <th
                            scope="row"
                            class="sticky start-0 z-10 bg-slate-50 dark:bg-dark-surface text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-[20px] py-[16px] border-e border-slate-200 dark:border-dark-border"
                        >
                            @lang('phonix::app.misc.compare.rows.stock')
                        </th>
                        @foreach ($compareData as $item)
                            <td class="px-[20px] py-[16px] bg-white dark:bg-dark-card border-e border-slate-200 dark:border-dark-border last:border-e-0 {{ $valuesDiffer('in_stock') ? 'bg-amber-50/40 dark:bg-amber-900/10' : '' }}">
                                @if ($item['in_stock'])
                                    <span class="inline-flex items-center gap-[5px] text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-[10px] py-[4px] rounded-full">
                                        <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        @lang('phonix::app.product.in_stock')
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-[5px] text-xs font-semibold text-coral bg-coral/10 px-[10px] py-[4px] rounded-full">
                                        <svg class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        @lang('phonix::app.product.out_of_stock')
                                    </span>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    {{-- ════════════════════════════════════════════
                         Row 7 – Type (product type attribute)
                    ════════════════════════════════════════════ --}}
                    <tr class="border-b border-slate-200 dark:border-dark-border {{ $valuesDiffer('type') ? 'bg-amber-50/60 dark:bg-amber-900/10' : '' }}">
                        <th
                            scope="row"
                            class="sticky start-0 z-10 bg-slate-50 dark:bg-dark-surface text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-[20px] py-[16px] border-e border-slate-200 dark:border-dark-border"
                        >
                            @lang('phonix::app.misc.compare.rows.type')
                        </th>
                        @foreach ($compareData as $item)
                            <td class="px-[20px] py-[16px] text-sm text-slate-700 dark:text-slate-300 capitalize bg-white dark:bg-dark-card border-e border-slate-200 dark:border-dark-border last:border-e-0 {{ $valuesDiffer('type') ? 'bg-amber-50/40 dark:bg-amber-900/10' : '' }}">
                                {{ $item['type'] }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- ════════════════════════════════════════════
                         Row 8 – Weight
                    ════════════════════════════════════════════ --}}
                    <tr class="border-b border-slate-200 dark:border-dark-border {{ $valuesDiffer('weight') ? 'bg-amber-50/60 dark:bg-amber-900/10' : '' }}">
                        <th
                            scope="row"
                            class="sticky start-0 z-10 bg-slate-50 dark:bg-dark-surface text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-[20px] py-[16px] border-e border-slate-200 dark:border-dark-border"
                        >
                            @lang('phonix::app.misc.compare.rows.weight')
                        </th>
                        @foreach ($compareData as $item)
                            <td class="px-[20px] py-[16px] text-sm text-slate-700 dark:text-slate-300 bg-white dark:bg-dark-card border-e border-slate-200 dark:border-dark-border last:border-e-0 {{ $valuesDiffer('weight') ? 'bg-amber-50/40 dark:bg-amber-900/10' : '' }}">
                                {{ $item['weight'] }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- ════════════════════════════════════════════
                         Row 9 – Short description
                    ════════════════════════════════════════════ --}}
                    <tr class="border-b border-slate-200 dark:border-dark-border">
                        <th
                            scope="row"
                            class="sticky start-0 z-10 bg-slate-50 dark:bg-dark-surface text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-[20px] py-[16px] border-e border-slate-200 dark:border-dark-border"
                        >
                            @lang('phonix::app.misc.compare.rows.description')
                        </th>
                        @foreach ($compareData as $item)
                            <td class="px-[20px] py-[16px] text-sm text-slate-600 dark:text-slate-400 leading-relaxed bg-white dark:bg-dark-card border-e border-slate-200 dark:border-dark-border last:border-e-0">
                                {{ $item['short_description'] ?: '—' }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- ════════════════════════════════════════════
                         Row 10 – Actions (Add to Cart + Remove)
                    ════════════════════════════════════════════ --}}
                    <tr>
                        <th
                            scope="row"
                            class="sticky start-0 z-10 bg-slate-50 dark:bg-dark-surface text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-[20px] py-[20px] border-e border-slate-200 dark:border-dark-border"
                        >
                            @lang('phonix::app.misc.compare.rows.actions')
                        </th>
                        @foreach ($compareData as $item)
                            <td class="px-[20px] py-[20px] bg-white dark:bg-dark-card border-e border-slate-200 dark:border-dark-border last:border-e-0">
                                <div class="flex flex-col gap-[10px]">
                                    {{-- Add to Cart --}}
                                    @if ($item['in_stock'])
                                        <a
                                            href="{{ route('shop.api.checkout.cart.store') }}?product_id={{ $item['id'] }}&quantity=1"
                                            class="btn-phoenix inline-flex items-center justify-center gap-[8px] px-[16px] py-[10px] text-sm w-full text-center"
                                        >
                                            <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                            @lang('phonix::app.product.add_to_cart')
                                        </a>
                                    @else
                                        <button
                                            disabled
                                            class="inline-flex items-center justify-center gap-[8px] px-[16px] py-[10px] text-sm w-full text-center rounded-md bg-slate-100 dark:bg-dark-surface text-slate-400 dark:text-slate-500 cursor-not-allowed"
                                        >
                                            @lang('phonix::app.product.out_of_stock')
                                        </button>
                                    @endif

                                    {{-- Remove from comparison --}}
                                    <button
                                        @click="removeProduct({{ $item['id'] }})"
                                        class="btn-phoenix-outline inline-flex items-center justify-center gap-[6px] px-[16px] py-[8px] text-xs w-full border-coral text-coral hover:bg-coral hover:text-white"
                                    >
                                        <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        @lang('phonix::app.misc.compare.remove')
                                    </button>
                                </div>
                            </td>
                        @endforeach
                    </tr>

                </table>
            </div>

            {{-- ── Difference legend ──────────────────────────────────── --}}
            <div class="flex items-center gap-[8px] mt-[16px] text-xs text-slate-500 dark:text-slate-400" data-gsap="fade-up">
                <span class="inline-block w-[16px] h-[16px] rounded-sm bg-amber-100 dark:bg-amber-900/40 border border-amber-300 dark:border-amber-700 shrink-0"></span>
                @lang('phonix::app.misc.compare.difference_legend')
            </div>
        @endif

    </div>

</x-phonix::layouts.index>
