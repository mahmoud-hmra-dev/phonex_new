@props([
    'featured' => collect(),
    'newArrivals' => collect(),
    'trending' => collect(),
])

{{-- Featured Products with Tabs --}}
<section class="section-padding" data-gsap="fade-up">
    <div class="container">

        <div x-data="{ activeTab: 'bestsellers' }">
            {{-- Heading + Tab Nav --}}
            <div class="flex flex-col lg:flex-row items-center lg:items-end justify-between gap-[24px] mb-[36px] lg:mb-[48px]">
                <div class="text-center lg:text-start">
                    <p class="inline-flex items-center gap-[8px] mb-[10px] text-[11px] font-bold tracking-[0.2em] uppercase text-phoenix-600 dark:text-phoenix-400">
                        <span class="inline-block w-[20px] h-[2px] rounded-full bg-phoenix-500"></span>
                        @lang('phonix::app.featured.title')
                    </p>
                    <h2 class="font-display text-fluid-2xl md:text-fluid-3xl font-bold text-slate-900 dark:text-white leading-[1.1] tracking-tight">
                        Handpicked for you
                    </h2>
                </div>

                <div class="flex items-center gap-[6px] p-[4px] rounded-full bg-slate-100 dark:bg-dark-surface">
                    @foreach (['bestsellers', 'new_arrivals', 'trending'] as $tab)
                        <button
                            @click="activeTab = '{{ $tab }}'"
                            :class="activeTab === '{{ $tab }}'
                                ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            class="px-[18px] py-[9px] rounded-full text-[13px] font-semibold transition-all duration-200 ease-phoenix"
                        >
                            @lang('phonix::app.featured.' . $tab)
                        </button>
                    @endforeach
                </div>
            </div>

            @php
                $tabProducts = [
                    'bestsellers' => $featured,
                    'new_arrivals' => $newArrivals,
                    'trending' => $trending,
                ];
            @endphp

            @foreach ($tabProducts as $tabKey => $products)
                <div
                    x-show="activeTab === '{{ $tabKey }}'" x-cloak
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-[8px]"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-[12px] md:gap-[20px]" data-gsap="stagger">
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
                        <p class="text-center py-[48px] text-slate-500 dark:text-slate-400">
                            @lang('phonix::app.general.no_products')
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
