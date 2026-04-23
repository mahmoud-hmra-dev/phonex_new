@props(['categories' => collect()])

@php
/**
 * Premium category imagery for the hero.
 */
$categoryImages = [
    'samsung-phones'  => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=1400&h=1400&fit=crop&q=92',
    'apple-iphones'   => 'https://images.unsplash.com/photo-1632661674596-df8be070a5c5?w=1400&h=1400&fit=crop&q=92',
    'tecno-phones'    => 'https://images.unsplash.com/photo-1601784551446-20c9e07cdbdb?w=1400&h=1400&fit=crop&q=92',
    'xiaomi-phones'   => 'https://images.unsplash.com/photo-1619406060869-96a6c7bd024e?w=1400&h=1400&fit=crop&q=92',
    'tablets'         => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=1400&h=1400&fit=crop&q=92',
    'accessories'     => 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=1400&h=1400&fit=crop&q=92',
    'gaming'          => 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=1400&h=1400&fit=crop&q=92',
];
$fallbackImage = 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=1400&h=1400&fit=crop&q=92';

$heroCategories = $categories->filter(fn($c) => $c->id !== 1)->values()->take(5);
$sideCategories = $heroCategories->slice(1, 2);
@endphp

{{-- Phonix Hero — Full-bleed Premium Carousel --}}
<section class="relative overflow-hidden bg-slate-950" data-gsap="hero">

    <div class="swiper swiper-hero">
        <div class="swiper-wrapper">

            @forelse ($heroCategories as $index => $category)
                @php
                    $slideImage = $categoryImages[$category->slug]
                        ?? ($category->banner_url ?: ($category->logo_url ?: $fallbackImage));
                    $discountLabel = 15 + (($index * 7) % 35);
                @endphp

                <div class="swiper-slide">
                    <div class="relative min-h-[520px] md:min-h-[600px] lg:min-h-[680px] flex items-center gradient-hero">

                        {{-- Background image with radial spotlight --}}
                        <div class="absolute inset-0 z-0">
                            <img
                                src="{{ $slideImage }}"
                                alt="{{ $category->name }}"
                                class="w-full h-full object-cover opacity-30 scale-110"
                                loading="eager"
                            />
                            <div class="absolute inset-0 bg-gradient-to-r from-phoenix-950/95 via-phoenix-900/80 to-transparent"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>
                        </div>

                        {{-- Decorative grid + orbs --}}
                        <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
                            <div class="absolute top-[10%] start-[60%] w-[600px] h-[600px] rounded-full bg-phoenix-500/20 blur-3xl animate-float"></div>
                            <div class="absolute bottom-[-10%] start-[10%] w-[400px] h-[400px] rounded-full bg-plasma-500/10 blur-3xl"></div>
                            <div class="absolute inset-0 opacity-[0.04]" style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 60px 60px;"></div>
                        </div>

                        {{-- Content --}}
                        <div class="container relative z-10 grid lg:grid-cols-12 items-center gap-[32px] lg:gap-[48px] py-[56px] md:py-[80px]">

                            {{-- Text column --}}
                            <div class="lg:col-span-7 text-center lg:text-start">
                                {{-- Eyebrow --}}
                                <div class="inline-flex items-center gap-[8px] mb-[24px] ps-[4px] pe-[14px] py-[4px] rounded-full border border-phoenix-400/30 bg-phoenix-400/10 backdrop-blur-sm">
                                    <span class="inline-flex items-center justify-center w-[22px] h-[22px] rounded-full gradient-plasma text-white text-[10px] font-bold">-{{ $discountLabel }}%</span>
                                    <span class="text-[11px] font-semibold tracking-[0.18em] uppercase text-phoenix-200">@lang('phonix::app.deals.limited_offer')</span>
                                </div>

                                <h1 class="font-display text-fluid-4xl md:text-fluid-5xl font-bold text-white leading-[1.02] mb-[20px] tracking-tight text-balance">
                                    {{ $category->name }}
                                    <span class="block text-gradient-phoenix">@lang('phonix::app.hero.title')</span>
                                </h1>

                                <p class="text-[15px] md:text-base text-slate-300/90 mb-[32px] max-w-[560px] mx-auto lg:mx-0 leading-relaxed">
                                    @if($category->description)
                                        {{ Str::limit(strip_tags($category->description), 140) }}
                                    @else
                                        @lang('phonix::app.hero.subtitle')
                                    @endif
                                </p>

                                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-[12px] mb-[40px]">
                                    <a href="{{ route('phonix.products.index', ['category_ids' => [$category->id]]) }}" class="btn-phoenix !px-[28px] !py-[14px] text-[15px]">
                                        @lang('phonix::app.hero.cta_primary')
                                        <svg class="w-[16px] h-[16px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                    </a>
                                    <a href="{{ route('phonix.products.index', ['sort' => 'created_at-desc']) }}" class="inline-flex items-center gap-[10px] px-[24px] py-[14px] text-[15px] font-semibold text-white border border-white/25 hover:border-white/50 hover:bg-white/5 rounded-lg transition-all backdrop-blur-sm">
                                        <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5"/></svg>
                                        @lang('phonix::app.hero.cta_secondary')
                                    </a>
                                </div>

                                {{-- Micro stats --}}
                                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-x-[32px] gap-y-[16px] pt-[24px] border-t border-white/10">
                                    @foreach ([
                                        ['label' => 'stats.happy_customers', 'value' => '250K+'],
                                        ['label' => 'stats.brands_count',    'value' => '80+'],
                                        ['label' => 'stats.products_count',  'value' => '15K+'],
                                    ] as $stat)
                                        <div>
                                            <p class="text-fluid-xl font-bold font-display text-white leading-none mb-[4px]">{{ $stat['value'] }}</p>
                                            <p class="text-[11px] uppercase tracking-wider text-slate-400">@lang('phonix::app.' . $stat['label'])</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Image column --}}
                            <div class="lg:col-span-5 flex items-center justify-center">
                                <div class="relative w-full max-w-[440px] aspect-square">
                                    {{-- Glow behind --}}
                                    <div class="absolute inset-[8%] rounded-full bg-phoenix-500/40 blur-[80px] animate-pulse-glow"></div>

                                    {{-- Outer ring --}}
                                    <div class="absolute inset-0 rounded-full border border-white/10"></div>
                                    <div class="absolute inset-[8%] rounded-full border border-phoenix-400/20"></div>

                                    {{-- Product card --}}
                                    <div class="absolute inset-[14%] rounded-3xl overflow-hidden border border-white/20 bg-white/5 backdrop-blur-md shadow-[0_40px_80px_-20px_rgba(0,0,0,0.8)]">
                                        <img src="{{ $slideImage }}" alt="{{ $category->name }}" class="w-full h-full object-cover" loading="eager"/>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-white/10 pointer-events-none"></div>
                                    </div>

                                    {{-- Floating stat chip --}}
                                    <div class="absolute top-[4%] -end-[6%] card-glass !p-[14px] !rounded-2xl w-[140px] animate-float" style="animation-delay: 0.4s;">
                                        <div class="flex items-center gap-[8px]">
                                            <span class="flex items-center justify-center w-[34px] h-[34px] rounded-full gradient-plasma text-white">
                                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-[10px] uppercase tracking-wide text-slate-500">@lang('phonix::app.deals.flash_deal')</p>
                                                <p class="text-sm font-bold text-slate-900">-{{ $discountLabel }}%</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Floating rating chip --}}
                                    <div class="absolute bottom-[6%] -start-[4%] card-glass !p-[14px] !rounded-2xl animate-float" style="animation-delay: 1.2s;">
                                        <div class="flex items-center gap-[4px] mb-[4px]">
                                            @for ($i = 0; $i < 5; $i++)
                                                <svg class="w-[12px] h-[12px] text-gold" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                        </div>
                                        <p class="text-[10px] text-slate-600"><span class="font-bold text-slate-900">4.9/5</span> — 12,450 reviews</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="swiper-slide">
                    <div class="relative min-h-[520px] md:min-h-[600px] flex items-center gradient-hero">
                        <div class="absolute inset-0 z-0">
                            <img src="{{ $fallbackImage }}" alt="Phonix" class="w-full h-full object-cover opacity-25"/>
                            <div class="absolute inset-0 bg-gradient-to-r from-phoenix-950/95 via-phoenix-900/80 to-transparent"></div>
                        </div>
                        <div class="container relative z-10 py-[80px] text-center lg:text-start max-w-[640px]">
                            <h1 class="font-display text-fluid-5xl font-bold text-white leading-tight mb-[20px]">@lang('phonix::app.hero.title')</h1>
                            <p class="text-lg text-slate-300 mb-[32px]">@lang('phonix::app.hero.subtitle')</p>
                            <a href="{{ route('phonix.products.index') }}" class="btn-phoenix !px-[28px] !py-[14px] text-[15px]">
                                @lang('phonix::app.hero.cta_primary')
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse

        </div>

        {{-- Nav arrows --}}
        <button class="swiper-button-prev !hidden md:!flex !w-[52px] !h-[52px] !rounded-full !bg-white/10 hover:!bg-phoenix-500 !backdrop-blur-md !border !border-white/20 hover:!border-phoenix-500 transition-all after:!text-white after:!text-[14px] after:!font-black !mt-0 !top-1/2 !-translate-y-1/2" aria-label="Previous slide"></button>
        <button class="swiper-button-next !hidden md:!flex !w-[52px] !h-[52px] !rounded-full !bg-white/10 hover:!bg-phoenix-500 !backdrop-blur-md !border !border-white/20 hover:!border-phoenix-500 transition-all after:!text-white after:!text-[14px] after:!font-black !mt-0 !top-1/2 !-translate-y-1/2" aria-label="Next slide"></button>

        <div class="swiper-pagination !bottom-[24px]"></div>
    </div>
</section>
