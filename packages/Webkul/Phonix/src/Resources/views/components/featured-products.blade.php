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
                    'bestsellers' => [
                        ['name' => 'iPhone 15 Pro', 'price' => '$999', 'originalPrice' => null, 'rating' => 5, 'reviewsCount' => 421, 'badge' => 'bestseller'],
                        ['name' => 'Samsung Galaxy S24', 'price' => '$799', 'originalPrice' => null, 'rating' => 5, 'reviewsCount' => 356, 'badge' => 'bestseller'],
                        ['name' => 'AirPods Pro 2', 'price' => '$249', 'originalPrice' => null, 'rating' => 5, 'reviewsCount' => 892, 'badge' => null],
                        ['name' => 'MacBook Pro 14"', 'price' => '$1,599', 'originalPrice' => null, 'rating' => 5, 'reviewsCount' => 267, 'badge' => null],
                        ['name' => 'iPad Air M2', 'price' => '$599', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 198, 'badge' => null],
                        ['name' => 'Galaxy Watch 6', 'price' => '$299', 'originalPrice' => '$349', 'rating' => 4, 'reviewsCount' => 145, 'badge' => 'sale'],
                        ['name' => 'Dell XPS 15', 'price' => '$1,299', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 178, 'badge' => null],
                        ['name' => 'Anker PowerBank 26800', 'price' => '$59', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 534, 'badge' => null],
                    ],
                    'new_arrivals' => [
                        ['name' => 'Samsung Galaxy Z Fold 6', 'price' => '$1,799', 'originalPrice' => null, 'rating' => 5, 'reviewsCount' => 42, 'badge' => 'new'],
                        ['name' => 'iPhone 16 Case MagSafe', 'price' => '$49', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 18, 'badge' => 'new'],
                        ['name' => 'Pixel Watch 3', 'price' => '$349', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 23, 'badge' => 'new'],
                        ['name' => 'Sony WF-1000XM6', 'price' => '$299', 'originalPrice' => null, 'rating' => 5, 'reviewsCount' => 56, 'badge' => 'new'],
                        ['name' => 'Lenovo ThinkPad X1 Carbon', 'price' => '$1,449', 'originalPrice' => null, 'rating' => 5, 'reviewsCount' => 34, 'badge' => 'new'],
                        ['name' => 'Xiaomi 14 Ultra', 'price' => '$899', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 67, 'badge' => 'new'],
                        ['name' => 'ASUS ROG Phone 8', 'price' => '$999', 'originalPrice' => null, 'rating' => 5, 'reviewsCount' => 29, 'badge' => 'new'],
                        ['name' => 'JBL Charge 5 Wi-Fi', 'price' => '$199', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 45, 'badge' => 'new'],
                    ],
                    'trending' => [
                        ['name' => 'Nothing Phone (2a)', 'price' => '$349', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 189, 'badge' => 'hot'],
                        ['name' => 'Samsung Galaxy Buds3 Pro', 'price' => '$249', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 156, 'badge' => 'hot'],
                        ['name' => 'Apple Vision Pro', 'price' => '$3,499', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 78, 'badge' => null],
                        ['name' => 'Xiaomi Smart Band 8', 'price' => '$49', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 423, 'badge' => null],
                        ['name' => 'Anker Nano Charger 65W', 'price' => '$35', 'originalPrice' => null, 'rating' => 5, 'reviewsCount' => 678, 'badge' => null],
                        ['name' => 'HP Spectre x360 16', 'price' => '$1,399', 'originalPrice' => '$1,599', 'rating' => 5, 'reviewsCount' => 89, 'badge' => 'sale'],
                        ['name' => 'Google Pixel 8 Pro', 'price' => '$899', 'originalPrice' => '$999', 'rating' => 5, 'reviewsCount' => 234, 'badge' => 'sale'],
                        ['name' => 'Razer BlackShark V2 Pro', 'price' => '$179', 'originalPrice' => null, 'rating' => 4, 'reviewsCount' => 312, 'badge' => 'hot'],
                    ],
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
                            <x-phonix::product-card
                                :name="$product['name']"
                                :price="$product['price']"
                                :originalPrice="$product['originalPrice']"
                                :rating="$product['rating']"
                                :reviewsCount="$product['reviewsCount']"
                                :badge="$product['badge']"
                            />
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
