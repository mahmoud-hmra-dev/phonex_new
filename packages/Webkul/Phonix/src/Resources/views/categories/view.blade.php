@php
    $productRepository = app(\Webkul\Product\Repositories\ProductRepository::class);
    $products = $productRepository->getAll(['category_id' => $category->id]);
@endphp

<x-phonix::layouts.index>
    <x-slot:title>
        {{ trim($category->meta_title) != "" ? $category->meta_title : $category->name }}
    </x-slot>

    <div class="container mx-auto section-padding">
        {{-- Breadcrumb --}}
        <x-phonix::breadcrumb :items="[
            ['label' => __('phonix::app.general.home'), 'url' => '/'],
            ['label' => __('phonix::app.general.shop'), 'url' => '/'],
            ['label' => $category->name],
        ]" />

        {{-- Category Header --}}
        @if ($category->banner_url)
            <div class="mb-[24px] rounded-lg overflow-hidden">
                <img
                    src="{{ $category->banner_url }}"
                    alt="{{ $category->name }}"
                    class="w-full max-h-[250px] object-cover"
                    loading="lazy"
                />
            </div>
        @endif

        <div class="mb-[24px]" data-gsap="fade-up">
            <h1 class="text-fluid-2xl font-bold text-slate-900 dark:text-white mb-[8px]">
                {{ $category->name }}
            </h1>
            @if ($category->description)
                <div class="text-fluid-sm text-slate-500 dark:text-slate-400 max-w-[640px] prose prose-sm dark:prose-invert">
                    {!! $category->description !!}
                </div>
            @endif
        </div>

        {{-- Products Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-[16px] md:gap-[24px]" data-gsap="stagger">
            @foreach($products as $product)
                @php
                    $productImage = product_image()->getProductBaseImage($product);
                    $hasSpecialPrice = $product->getTypeInstance()->haveDiscount();
                    $avgRating = $product->reviews->count() > 0 ? round($product->reviews->avg('rating')) : 0;
                @endphp
                <x-phonix::product-card
                    :name="$product->name"
                    :price="$hasSpecialPrice ? core()->currency($product->getTypeInstance()->getMinimalPrice()) : core()->currency($product->price)"
                    :originalPrice="$hasSpecialPrice ? core()->currency($product->price) : null"
                    :rating="$avgRating"
                    :reviewsCount="$product->reviews->count()"
                    :badge="$hasSpecialPrice ? 'sale' : ($product->new ? 'new' : null)"
                    :imageUrl="$productImage['medium_image_url']"
                    :url="route('phonix.products.view', ['slug' => $product->url_key])"
                />
            @endforeach
        </div>

        @if($products->isEmpty())
            <div class="text-center py-[48px]">
                <svg class="w-[64px] h-[64px] text-slate-300 dark:text-slate-600 mx-auto mb-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <p class="text-slate-500 dark:text-slate-400">@lang('phonix::app.general.no_products')</p>
            </div>
        @endif

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="mt-[32px]">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-phonix::layouts.index>
