@props(['products' => collect()])

{{-- Flash Deals — urgency section with countdown --}}
<section class="section-padding relative overflow-hidden" data-gsap="fade-up">
    {{-- Ambient background --}}
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-plasma-50 via-white to-phoenix-50 dark:from-plasma-900/20 dark:via-dark-bg dark:to-phoenix-900/10"></div>
    <div class="absolute top-[-80px] end-[-80px] w-[360px] h-[360px] rounded-full bg-plasma-500/10 blur-3xl pointer-events-none"></div>

    <div class="container relative">
        <div class="flex flex-col lg:flex-row items-center lg:items-end justify-between gap-[24px] mb-[40px]">
            <div class="text-center lg:text-start">
                <p class="inline-flex items-center gap-[8px] mb-[12px] px-[12px] py-[6px] rounded-full bg-plasma-500/10 text-plasma-600 dark:text-plasma-400 text-[11px] font-bold uppercase tracking-[0.2em]">
                    <svg class="w-[12px] h-[12px] animate-pulse" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    @lang('phonix::app.deals.hurry')
                </p>
                <h2 class="font-display text-fluid-2xl md:text-fluid-3xl font-bold text-slate-900 dark:text-white leading-tight mb-[6px]">
                    @lang('phonix::app.deals.flash_deal')
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-[420px]">
                    @lang('phonix::app.deal_of_day.subtitle')
                </p>
            </div>

            {{-- Countdown --}}
            <div x-data="countdownTimer()" x-init="startCountdown()" class="flex items-center gap-[6px] md:gap-[10px]">
                <span class="hidden sm:inline text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 me-[8px]">
                    @lang('phonix::app.deals.ends_in')
                </span>
                <template x-for="(unit, index) in units" :key="index">
                    <div class="flex items-center gap-[4px] md:gap-[8px]">
                        <div class="flex flex-col items-center">
                            <span class="flex items-center justify-center w-[46px] h-[46px] md:w-[54px] md:h-[54px] rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-display text-lg md:text-xl font-bold shadow-[0_6px_16px_-4px_rgba(15,23,42,0.25)]" x-text="String(unit.value).padStart(2, '0')"></span>
                            <span class="text-[9px] text-slate-500 dark:text-slate-400 mt-[4px] uppercase tracking-wider font-semibold" x-text="unit.label"></span>
                        </div>
                        <span x-show="index < units.length - 1" class="text-lg font-bold text-plasma-500 -mt-[16px]">:</span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Products --}}
        <div class="relative">
            <div class="flex gap-[14px] md:gap-[20px] overflow-x-auto scrollbar-thin pb-[8px] snap-x snap-mandatory -mx-[16px] px-[16px] md:mx-0 md:px-0">
                @foreach ($products as $product)
                    @php
                        $productImage = product_image()->getProductBaseImage($product);
                        $hasSpecialPrice = $product->getTypeInstance()->haveDiscount();
                        $avgRating = $product->reviews->count() > 0 ? round($product->reviews->avg('rating')) : 0;
                    @endphp
                    <div class="flex-shrink-0 w-[220px] sm:w-[260px] md:w-[280px] snap-start">
                        <x-phonix::product-card
                            :productId="$product->id"
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

        <div class="text-center mt-[28px]">
            <a href="{{ route('phonix.products.index', ['sort' => 'price-asc']) }}" class="inline-flex items-center gap-[8px] text-sm font-semibold text-plasma-600 dark:text-plasma-400 hover:text-plasma-700 dark:hover:text-plasma-300 transition-colors group">
                @lang('phonix::app.deals.view_all_deals')
                <span class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-full border border-plasma-500/30 group-hover:border-plasma-500 group-hover:bg-plasma-500 group-hover:text-white transition-all">
                    <svg class="w-[12px] h-[12px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </span>
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
                    if (distance < 0) { this.units.forEach(u => u.value = 0); return; }
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
