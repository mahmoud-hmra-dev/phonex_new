@props([
    'featured' => collect(),
    'newArrivals' => collect(),
    'trending' => collect(),
])

{{-- Featured Products with Tabs --}}
<section class="section-padding" data-gsap="fade-up">
    <div class="container">
        <x-phonix::section-heading :title="__('phonix::app.featured.title')" />

        <div x-data="{ activeTab: 'bestsellers' }">
            {{-- Tab Navigation --}}
            <div class="flex items-center justify-center gap-[4px] mb-[32px] lg:mb-[48px]">
                @foreach (['bestsellers', 'new_arrivals', 'trending'] as $tab)
                    <button
                        @click="activeTab = '{{ $tab }}'"
                        :class="activeTab === '{{ $tab }}'
                            ? 'bg-phoenix-500 text-white shadow-md dark:bg-phoenix-400 dark:text-phoenix-950'
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-card'"
                        class="px-[20px] py-[10px] rounded-md text-sm font-semibold transition-all duration-200 ease-phoenix"
                    >
                        @lang('phonix::app.featured.' . $tab)
                    </button>
                @endforeach
            </div>

            @php
                $tabProducts = [
                    'bestsellers' => $featured,
                    'new_arrivals' => $newArrivals,
                    'trending' => $trending,
                ];
            @endphp

            {{-- Tab Panels --}}
            @foreach ($tabProducts as $tabKey => $products)
                <div
                    x-show="activeTab === '{{ $tabKey }}'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-[8px]"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-cloak
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-[16px] md:gap-[24px]" data-gsap="stagger">
                        @foreach ($products as $product)
                            @php
                                $productImage = product_image()->getProductBaseImage($product);
                                $avgRating = $product->reviews->count() > 0 ? round($product->reviews->avg('rating')) : 0;
                                $hasSpecialPrice = $product->getTypeInstance()->haveDiscount();
                            @endphp
                            <x-phonix::product-card
                                :productId="$product->id"
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
                        <p class="text-center py-[32px] text-slate-500 dark:text-slate-400">
                            @lang('phonix::app.general.no_products')
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
