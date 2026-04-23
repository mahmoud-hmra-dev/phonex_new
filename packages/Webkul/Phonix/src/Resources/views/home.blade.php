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

    {{-- Hero --}}
    <x-phonix::hero :categories="$allCategories" />

    {{-- Trust strip — overlaps hero bottom --}}
    <x-phonix::features-bar />

    {{-- Shop by Category --}}
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

    {{-- Deal of the Day --}}
    <x-phonix::deal-of-day />

    {{-- Brands --}}
    <x-phonix::brands-carousel />

    {{-- Social proof --}}
    <x-phonix::stats-counter />

    {{-- Testimonials --}}
    <x-phonix::testimonials />

</x-phonix::layouts.index>
