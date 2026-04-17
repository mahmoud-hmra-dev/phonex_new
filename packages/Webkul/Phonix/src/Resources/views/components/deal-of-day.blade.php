{{-- Deal of the Day Section --}}
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
                            <img src="https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&h=400&fit=crop"
                                 alt="Samsung Galaxy S24 Ultra 512GB"
                                 class="w-full h-full object-cover"
                                 loading="lazy" />
                        </div>
                        {{-- Discount badge --}}
                        <div class="absolute -top-[8px] -end-[8px] flex items-center justify-center w-[64px] h-[64px] md:w-[72px] md:h-[72px] rounded-full bg-coral text-white font-bold text-sm md:text-base shadow-lg animate-pulse-glow" style="box-shadow: 0 0 20px rgba(255,107,107,0.4);">
                            -35%
                        </div>
                    </div>
                </div>

                {{-- Product Details --}}
                <div class="flex-1 text-center lg:text-start">
                    {{-- Category tag --}}
                    <span class="inline-block px-[12px] py-[4px] text-xs font-medium tracking-wider uppercase rounded-full bg-phoenix-500/20 text-phoenix-300 mb-[12px]">
                        @lang('phonix::app.categories.phones')
                    </span>

                    {{-- Product name --}}
                    <h3 class="text-fluid-2xl font-bold text-white mb-[12px]">
                        Samsung Galaxy S24 Ultra 512GB
                    </h3>

                    {{-- Rating --}}
                    <div class="flex items-center justify-center lg:justify-start gap-[4px] mb-[16px]">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-[18px] h-[18px] {{ $i <= 5 ? 'text-gold' : 'text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                        <span class="text-sm text-slate-400 ms-[8px]">(347 @lang('phonix::app.product.reviews'))</span>
                    </div>

                    {{-- Description --}}
                    <p class="text-slate-300 text-fluid-sm mb-[20px] max-w-[500px] mx-auto lg:mx-0">
                        Experience the ultimate smartphone with a 200MP camera, Snapdragon 8 Gen 3 processor, and all-day battery life. Premium titanium build with S Pen included.
                    </p>

                    {{-- Price --}}
                    <div class="flex items-center justify-center lg:justify-start gap-[12px] mb-[20px]">
                        <span class="text-fluid-3xl font-bold text-phoenix-400">$849</span>
                        <span class="text-fluid-lg text-slate-500 line-through">$1,299</span>
                        <span class="badge-sale">@lang('phonix::app.product.save_percent', ['percent' => 35])</span>
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

                    {{-- Sold progress bar --}}
                    <div class="mb-[24px] max-w-[400px] mx-auto lg:mx-0">
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-[6px]">
                            <span>@lang('phonix::app.deals.hurry')</span>
                            <span>@lang('phonix::app.deal_of_day.items_sold', ['percent' => 75])</span>
                        </div>
                        <div class="w-full h-[6px] rounded-full bg-white/10 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-coral to-gold transition-all duration-1000" style="width: 75%;"></div>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <a href="{{ route('phonix.products.index', ['sort' => 'price-asc']) }}" class="btn-phoenix px-[32px] py-[14px] text-base">
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
