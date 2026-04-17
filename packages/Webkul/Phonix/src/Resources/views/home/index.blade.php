@php
    $productRepository = app(\Webkul\Product\Repositories\ProductRepository::class);
    $categoryRepository = app(\Webkul\Category\Repositories\CategoryRepository::class);

    $featuredProducts = $productRepository->scopeQuery(function ($query) {
        return $query->distinct()
            ->addSelect('products.*')
            ->whereIn('products.type', ['simple', 'configurable'])
            ->inRandomOrder()
            ->limit(8);
    })->get();

    $newArrivals = $productRepository->scopeQuery(function ($query) {
        return $query->distinct()
            ->addSelect('products.*')
            ->whereIn('products.type', ['simple', 'configurable'])
            ->orderBy('products.id', 'desc')
            ->limit(8);
    })->get();

    $newProducts = $productRepository->scopeQuery(function ($query) {
        return $query->distinct()
            ->addSelect('products.*')
            ->whereIn('products.type', ['simple', 'configurable'])
            ->inRandomOrder()
            ->limit(8);
    })->get();

    $allCategories = $categoryRepository->getVisibleCategoryTree(
        core()->getCurrentChannel()->root_category_id
    );
@endphp

<x-phonix::layouts.index>
    <x-slot:title>
        {{ core()->getCurrentChannel()->home_seo['meta_title'] ?? __('phonix::app.theme.title') }}
    </x-slot>

    {{-- Hero Section --}}
    <x-phonix::hero />

    {{-- Features / Trust Badges --}}
    <x-phonix::features-bar />

    {{-- Categories Grid --}}
    @if($allCategories->count())
        <x-phonix::categories-grid :categories="$allCategories" />
    @endif

    {{-- Flash Deals --}}
    @php
        $dealProducts = $newProducts->isNotEmpty() ? $newProducts : $featuredProducts;
    @endphp
    @if($dealProducts->isNotEmpty())
        <x-phonix::flash-deals :products="$dealProducts" />
    @endif

    {{-- Featured Products (Tabs) --}}
    @if($featuredProducts->isNotEmpty() || $newArrivals->isNotEmpty() || $newProducts->isNotEmpty())
        <x-phonix::featured-products
            :featured="$featuredProducts"
            :newArrivals="$newArrivals"
            :trending="$newProducts"
        />
    @endif

    {{-- Brand Carousel --}}
    <x-phonix::brands-carousel />

    {{-- Deal of the Day --}}
    <x-phonix::deal-of-day />

    {{-- Stats Counter --}}
    <x-phonix::stats-counter />

    {{-- Testimonials --}}
    <x-phonix::testimonials />

    {{-- Newsletter --}}
    <x-phonix::newsletter />

</x-phonix::layouts.index>
