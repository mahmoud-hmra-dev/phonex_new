{{-- My Reviews --}}
@php
    $customer = auth()->guard('customer')->user();

    $reviews = $customer
        ? app(\Webkul\Product\Repositories\ProductReviewRepository::class)
            ->with('product')
            ->findWhere(['customer_id' => $customer->id])
            ->sortByDesc('created_at')
            ->map(function ($review) {
                $product  = $review->product;
                $imageData = $product ? product_image()->getProductBaseImage($product) : [];

                return [
                    'id'         => $review->id,
                    'title'      => $review->title ?? '',
                    'comment'    => $review->comment ?? '',
                    'rating'     => (int) $review->rating,
                    'status'     => $review->status ?? 'pending',
                    'created_at' => $review->created_at?->format('M d, Y') ?? '',
                    'product'    => $product ? [
                        'name'  => $product->name,
                        'url'   => route('phonix.products.view', $product->url_key),
                        'image' => $imageData['small_image_url'] ?? null,
                    ] : null,
                ];
            })
            ->values()
            ->all()
        : [];
@endphp

<x-phonix::account.layout
    :title="__('phonix::app.account.reviews.title')"
    :breadcrumbs="[['label' => __('phonix::app.account.reviews.title')]]"
>
    <div class="space-y-[24px]">
        {{-- Page Title --}}
        <h1 class="text-fluid-xl font-bold text-slate-800 dark:text-slate-100" data-gsap="fade-up">
            @lang('phonix::app.account.reviews.title')
        </h1>

        @if (count($reviews) > 0)
            <div class="space-y-[16px]" data-gsap="fade-up">
                @foreach ($reviews as $review)
                    <div class="card-phoenix p-[20px]" data-gsap="fade-up">
                        <div class="flex gap-[16px]">
                            {{-- Product Image --}}
                            @if ($review['product'])
                                <a href="{{ $review['product']['url'] }}" class="shrink-0 w-[72px] h-[72px] rounded-md overflow-hidden bg-slate-50 dark:bg-dark-surface">
                                    @if ($review['product']['image'])
                                        <img
                                            src="{{ $review['product']['image'] }}"
                                            alt="{{ $review['product']['name'] }}"
                                            class="w-full h-full object-cover"
                                            loading="lazy"
                                        />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                            <svg class="w-[32px] h-[32px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                            @endif

                            {{-- Review Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-start justify-between gap-[8px] mb-[6px]">
                                    {{-- Product Name --}}
                                    @if ($review['product'])
                                        <a
                                            href="{{ $review['product']['url'] }}"
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors truncate"
                                        >
                                            {{ $review['product']['name'] }}
                                        </a>
                                    @endif

                                    {{-- Status Badge --}}
                                    <span class="shrink-0 inline-flex items-center px-[8px] py-[2px] text-xs font-medium rounded-full
                                        {{ $review['status'] === 'approved'
                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                            : ($review['status'] === 'disapproved'
                                                ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
                                                : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400') }}">
                                        @lang('phonix::app.account.reviews.status.' . $review['status'])
                                    </span>
                                </div>

                                {{-- Stars --}}
                                <div class="flex items-center gap-[3px] mb-[6px]">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg
                                            class="w-[14px] h-[14px] {{ $i <= $review['rating'] ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>

                                {{-- Review Title --}}
                                @if ($review['title'])
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-[4px]">
                                        {{ $review['title'] }}
                                    </p>
                                @endif

                                {{-- Review Comment --}}
                                @if ($review['comment'])
                                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                                        {{ $review['comment'] }}
                                    </p>
                                @endif

                                {{-- Date --}}
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-[8px]">
                                    {{ $review['created_at'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="card-phoenix py-[80px] text-center" data-gsap="fade-up">
                <svg class="w-[80px] h-[80px] mx-auto text-slate-300 dark:text-slate-600 mb-[24px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-[8px]">
                    @lang('phonix::app.account.reviews.no_reviews')
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-[24px] max-w-sm mx-auto">
                    @lang('phonix::app.account.reviews.no_reviews_message')
                </p>
                <a href="{{ route('phonix.products.index') }}" class="btn-phoenix">
                    @lang('phonix::app.account.wishlist.explore_products')
                </a>
            </div>
        @endif
    </div>
</x-phonix::account.layout>
