{{-- Deal of the Day Section --}}
@php
    use Webkul\Product\Repositories\ProductRepository;

    $productRepo = app(ProductRepository::class);

    $dealProduct      = null;
    $dealDiscount     = 0;
    $dealFinalPrice   = 0;
    $dealRegularPrice = 0;
    $dealImage        = null;
    $dealRating       = 5;
    $dealReviewsCount = 0;

    // Get all simple/configurable products and pick one with a discount, or fallback to any
    $allProducts = $productRepo->scopeQuery(function ($query) {
        return $query->distinct()
            ->addSelect('products.*')
            ->whereIn('products.type', ['simple', 'configurable'])
            ->inRandomOrder()
            ->limit(20);
    })->get();

    // Prefer a product that has a real discount
    $candidates = $allProducts->first(function ($p) {
        try { return $p->getTypeInstance()->haveDiscount(); } catch (\Throwable $e) { return false; }
    });

    // Fallback to any product
    if (! $candidates) {
        $candidates = $allProducts->first();
    }

    if ($candidates) {
        $dealProduct      = $candidates;
        $imageData        = product_image()->getProductBaseImage($dealProduct);
        $dealImage        = $imageData['large_image_url'] ?? ($imageData['medium_image_url'] ?? null);
        $hasDiscount      = $dealProduct->getTypeInstance()->haveDiscount();
        $dealFinalPrice   = $hasDiscount
            ? $dealProduct->getTypeInstance()->getMinimalPrice()
            : ($dealProduct->price ?? 0);
        $dealRegularPrice = $dealProduct->price ?? $dealFinalPrice;
        $dealDiscount     = ($hasDiscount && $dealRegularPrice > $dealFinalPrice && $dealRegularPrice > 0)
            ? round((1 - $dealFinalPrice / $dealRegularPrice) * 100)
            : 0;
        $dealReviewsCount = $dealProduct->reviews->count();
        $dealRating       = $dealReviewsCount > 0
            ? round($dealProduct->reviews->avg('rating'))
            : 5;
    }
@endphp

@if ($dealProduct)
<section class="section-padding" data-gsap="fade-up">
    <div class="container">
        <x-phonix::section-heading
            :title="__('phonix::app.deal_of_day.title')"
            :subtitle="__('phonix::app.deal_of_day.subtitle')"
        />

        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-phoenix-950 via-phoenix-900 to-dark-bg border border-phoenix-800/30">
            {{-- Background decorations --}}
            <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                <div class="absolute -top-[100px] -end-[100px] w-[300px] h-[300px] rounded-full bg-phoenix-500/10 blur-3xl"></div>
                <div class="absolute -bottom-[60px] -start-[60px] w-[200px] h-[200px] rounded-full bg-phoenix-400/10 blur-2xl"></div>
            </div>

            <div class="relative z-10 flex flex-col lg:flex-row items-center gap-[32px] lg:gap-[48px] p-[24px] md:p-[40px] lg:p-[56px]">
                {{-- Product Image --}}
                <div class="flex-1 flex items-center justify-center w-full max-w-[400px]">
                    <div class="relative w-full aspect-square">
                        {{-- Glow effect --}}
                        <div class="absolute inset-[15%] rounded-full bg-phoenix-500/15 blur-2xl"></div>
                        {{-- Product image --}}
                        <div class="relative w-full h-full rounded-2xl border border-phoenix-500/20 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-phoenix-500/5 to-transparent z-10"></div>
                            @if ($dealImage)
                                <img src="{{ $dealImage }}"
                                     alt="{{ $dealProduct->name }}"
                                     class="w-full h-full object-cover"
                                     loading="lazy" />
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-phoenix-900/50">
                                    <svg class="w-[80px] h-[80px] text-phoenix-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        {{-- Discount badge --}}
                        @if ($dealDiscount > 0)
                            <div class="absolute -top-[8px] -end-[8px] flex items-center justify-center w-[64px] h-[64px] md:w-[72px] md:h-[72px] rounded-full bg-coral text-white font-bold text-sm md:text-base shadow-lg animate-pulse-glow" style="box-shadow: 0 0 20px rgba(255,107,107,0.4);">
                                -{{ $dealDiscount }}%
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Product Details --}}
                <div class="flex-1 text-center lg:text-start">
                    {{-- Category tag --}}
                    @if ($dealProduct->categories->isNotEmpty())
                        <span class="inline-block px-[12px] py-[4px] text-xs font-medium tracking-wider uppercase rounded-full bg-phoenix-500/20 text-phoenix-300 mb-[12px]">
                            {{ $dealProduct->categories->first()->name }}
                        </span>
                    @endif

                    {{-- Product name --}}
                    <h3 class="text-fluid-2xl font-bold text-white mb-[12px]">
                        {{ $dealProduct->name }}
                    </h3>

                    {{-- Rating --}}
                    <div class="flex items-center justify-center lg:justify-start gap-[4px] mb-[16px]">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-[18px] h-[18px] {{ $i <= $dealRating ? 'text-gold' : 'text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                        <span class="text-sm text-slate-400 ms-[8px]">({{ $dealReviewsCount }} @lang('phonix::app.product.reviews'))</span>
                    </div>

                    {{-- Description --}}
                    @if ($dealProduct->short_description)
                        <p class="text-slate-300 text-fluid-sm mb-[20px] max-w-[500px] mx-auto lg:mx-0 line-clamp-3">
                            {{ strip_tags($dealProduct->short_description) }}
                        </p>
                    @elseif ($dealProduct->description)
                        <p class="text-slate-300 text-fluid-sm mb-[20px] max-w-[500px] mx-auto lg:mx-0 line-clamp-3">
                            {{ Str::limit(strip_tags($dealProduct->description), 160) }}
                        </p>
                    @endif

                    {{-- Price --}}
                    <div class="flex items-center justify-center lg:justify-start gap-[12px] mb-[20px]">
                        <span class="text-fluid-3xl font-bold text-phoenix-400">{{ core()->currency($dealFinalPrice) }}</span>
                        @if ($dealDiscount > 0)
                            <span class="text-fluid-lg text-slate-500 line-through">{{ core()->currency($dealRegularPrice) }}</span>
                            <span class="badge-sale">@lang('phonix::app.product.save_percent', ['percent' => $dealDiscount])</span>
                        @endif
                    </div>

                    {{-- Countdown --}}
                    <div
                        x-data="dealCountdown()"
                        x-init="startCountdown()"
                        class="flex items-center justify-center lg:justify-start gap-[8px] mb-[24px]"
                    >
                        <span class="text-xs font-medium text-slate-400 me-[4px]">@lang('phonix::app.deals.ends_in'):</span>
                        <template x-for="(unit, index) in units" :key="index">
                            <div class="flex items-center gap-[8px]">
                                <div class="flex items-center justify-center w-[40px] h-[36px] rounded bg-white/10 text-sm font-bold text-white" x-text="String(unit.value).padStart(2, '0')"></div>
                                <span x-show="index < units.length - 1" class="text-slate-500 font-bold">:</span>
                            </div>
                        </template>
                    </div>

                    {{-- CTA --}}
                    <a href="{{ route('phonix.products.view', $dealProduct->url_key) }}" class="btn-phoenix px-[32px] py-[14px] text-base">
                        <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        @lang('phonix::app.deals.shop_deal')
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@pushOnce('scripts')
<script>
    function dealCountdown() {
        return {
            units: [
                { value: 0, label: 'h' },
                { value: 0, label: 'm' },
                { value: 0, label: 's' },
            ],
            startCountdown() {
                const endDate = new Date();
                endDate.setHours(23, 59, 59, 0);
                if (endDate.getTime() - new Date().getTime() < 3600000) {
                    endDate.setDate(endDate.getDate() + 1);
                }

                const update = () => {
                    const now = new Date().getTime();
                    const distance = endDate.getTime() - now;

                    if (distance < 0) {
                        this.units.forEach(u => u.value = 0);
                        return;
                    }

                    this.units[0].value = Math.floor(distance / (1000 * 60 * 60));
                    this.units[1].value = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    this.units[2].value = Math.floor((distance % (1000 * 60)) / 1000);
                };

                update();
                setInterval(update, 1000);
            }
        };
    }
</script>
@endPushOnce
