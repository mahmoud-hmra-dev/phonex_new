@props(['products' => collect()])

{{-- Flash Deals Section --}}
<section class="section-padding bg-slate-50 dark:bg-dark-surface" data-gsap="fade-up">
    <div class="container">
        {{-- Header with badge and countdown --}}
        <div class="flex flex-col md:flex-row items-center justify-between gap-[16px] mb-[32px] lg:mb-[48px]">
            <div class="text-center md:text-start">
                <div class="flex items-center justify-center md:justify-start gap-[12px] mb-[8px]">
                    <h2 class="text-fluid-2xl font-bold text-slate-900 dark:text-white">
                        @lang('phonix::app.deals.flash_deal')
                    </h2>
                    <x-phonix::badge type="hot">HOT</x-phonix::badge>
                </div>
                <div class="flex items-center justify-center gap-[8px]">
                    <span class="w-[32px] h-[2px] bg-phoenix-300 dark:bg-phoenix-600 rounded-full"></span>
                    <span class="w-[48px] h-[3px] bg-phoenix-500 rounded-full"></span>
                    <span class="w-[32px] h-[2px] bg-phoenix-300 dark:bg-phoenix-600 rounded-full"></span>
                </div>
            </div>

            {{-- Countdown Timer --}}
            <div
                x-data="countdownTimer()"
                x-init="startCountdown()"
                class="flex items-center gap-[8px] md:gap-[12px]"
            >
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400 me-[4px]">
                    @lang('phonix::app.deals.ends_in'):
                </span>
                <template x-for="(unit, index) in units" :key="index">
                    <div class="flex items-center gap-[8px] md:gap-[12px]">
                        <div class="flex flex-col items-center">
                            <span
                                class="flex items-center justify-center w-[44px] h-[44px] md:w-[52px] md:h-[52px] rounded-md bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border text-lg md:text-xl font-bold text-phoenix-600 dark:text-phoenix-400 shadow-sm"
                                x-text="String(unit.value).padStart(2, '0')"
                            ></span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-[4px] uppercase tracking-wider" x-text="unit.label"></span>
                        </div>
                        <span
                            x-show="index < units.length - 1"
                            class="text-lg font-bold text-slate-300 dark:text-slate-600 -mt-[16px]"
                        >:</span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Products horizontal scroll --}}
        <div class="relative">
            <div class="flex gap-[16px] md:gap-[24px] overflow-x-auto scrollbar-thin pb-[16px] snap-x snap-mandatory">
                @foreach ($products as $product)
                    @php
                        $productImage = product_image()->getProductBaseImage($product);
                        $hasSpecialPrice = $product->getTypeInstance()->haveDiscount();
                        $avgRating = $product->reviews->count() > 0 ? round($product->reviews->avg('rating')) : 0;
                    @endphp
                    <div class="flex-shrink-0 w-[260px] md:w-[280px] snap-start">
                        <x-phonix::product-card
                            :name="$product->name"
                            :price="$hasSpecialPrice ? core()->currency($product->getTypeInstance()->getMinimalPrice()) : core()->currency($product->price)"
                            :originalPrice="$hasSpecialPrice ? core()->currency($product->price) : null"
                            :rating="$avgRating"
                            :reviewsCount="$product->reviews->count()"
                            :badge="$hasSpecialPrice ? 'sale' : 'hot'"
                            :imageUrl="$productImage['medium_image_url']"
                            :url="route('phonix.products.view', ['slug' => $product->url_key])"
                        />
                    </div>
                @endforeach
            </div>
        </div>

        {{-- View All link --}}
        <div class="text-center mt-[24px]">
            <a
                href="{{ route('phonix.products.index', ['sort' => 'price-asc']) }}"
                class="inline-flex items-center gap-[6px] text-sm font-semibold text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors"
            >
                @lang('phonix::app.deals.view_all_deals')
                <svg class="w-[16px] h-[16px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

@pushOnce('scripts')
<script>
    function countdownTimer() {
        return {
            units: [
                { value: 0, label: '{{ __("phonix::app.deals.timer.days") }}' },
                { value: 0, label: '{{ __("phonix::app.deals.timer.hours") }}' },
                { value: 0, label: '{{ __("phonix::app.deals.timer.minutes") }}' },
                { value: 0, label: '{{ __("phonix::app.deals.timer.seconds") }}' },
            ],
            startCountdown() {
                const endDate = new Date();
                endDate.setDate(endDate.getDate() + 2);
                endDate.setHours(23, 59, 59, 0);

                const update = () => {
                    const now = new Date().getTime();
                    const distance = endDate.getTime() - now;

                    if (distance < 0) {
                        this.units.forEach(u => u.value = 0);
                        return;
                    }

                    this.units[0].value = Math.floor(distance / (1000 * 60 * 60 * 24));
                    this.units[1].value = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    this.units[2].value = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    this.units[3].value = Math.floor((distance % (1000 * 60)) / 1000);
                };

                update();
                setInterval(update, 1000);
            }
        };
    }
</script>
@endPushOnce
