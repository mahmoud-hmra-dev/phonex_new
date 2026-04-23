@props(['categories' => collect()])

@php
    $categoryList = $categories->filter(fn ($c) => $c->id !== 1)->values();
    $total = $categoryList->count();

    $defaultImages = [
        'samsung-phones'  => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=900&h=900&fit=crop&q=85',
        'apple-iphones'   => 'https://images.unsplash.com/photo-1592286927505-1def25115558?w=900&h=900&fit=crop&q=85',
        'tecno-phones'    => 'https://images.unsplash.com/photo-1601784551446-20c9e07cdbdb?w=900&h=900&fit=crop&q=85',
        'xiaomi-phones'   => 'https://images.unsplash.com/photo-1619406060869-96a6c7bd024e?w=900&h=900&fit=crop&q=85',
        'tablets'         => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=900&h=900&fit=crop&q=85',
        'accessories'     => 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=900&h=900&fit=crop&q=85',
        'gaming'          => 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=900&h=900&fit=crop&q=85',
    ];
@endphp

<section class="section-padding" data-gsap="fade-up">
    <div class="container">
        <x-phonix::section-heading
            :title="__('phonix::app.categories.title')"
            :subtitle="__('phonix::app.hero.subtitle')"
        />

        @if($total > 0)
            <div class="grid grid-cols-2 md:grid-cols-6 gap-[12px] md:gap-[16px]" data-gsap="stagger">

                @foreach ($categoryList as $index => $category)
                    @php
                        // Bento: first card is feature (2x2), next 2 are tall (1x2), rest small (1x1)
                        if ($index === 0 && $total >= 4) {
                            $span = 'col-span-2 md:col-span-3 md:row-span-2';
                            $aspect = 'aspect-[4/3] md:aspect-auto md:min-h-[320px]';
                            $textSize = 'text-fluid-xl md:text-fluid-2xl';
                            $featured = true;
                        } elseif ($index <= 2 && $total >= 5) {
                            $span = 'col-span-1 md:col-span-3 md:row-span-1';
                            $aspect = 'aspect-[16/9] md:aspect-[2/1] md:min-h-[152px]';
                            $textSize = 'text-base md:text-lg';
                            $featured = false;
                        } else {
                            $span = 'col-span-1 md:col-span-2';
                            $aspect = 'aspect-square md:aspect-[4/3]';
                            $textSize = 'text-sm md:text-base';
                            $featured = false;
                        }

                        $image = $defaultImages[$category->slug]
                            ?? ($category->banner_url ?: $category->logo_url ?: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=900&h=900&fit=crop&q=85');
                    @endphp

                    <a href="{{ route('phonix.products.index', ['category_ids' => [$category->id]]) }}" class="group relative overflow-hidden rounded-2xl {{ $span }} {{ $aspect }} bg-slate-100 dark:bg-dark-card isolate">
                        {{-- Image --}}
                        <img src="{{ $image }}" alt="{{ $category->name }}" loading="lazy"
                             class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"/>

                        {{-- Dark gradient --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-900/40 to-transparent"></div>

                        {{-- Color wash on hover --}}
                        <div class="absolute inset-0 bg-gradient-to-tr from-phoenix-600/0 via-transparent to-phoenix-400/0 group-hover:from-phoenix-600/40 group-hover:to-plasma-500/10 transition-all duration-500"></div>

                        {{-- Featured tag --}}
                        @if($featured)
                            <div class="absolute top-[14px] start-[14px] badge-featured !text-[10px]">
                                @lang('phonix::app.categories.featured')
                            </div>
                        @endif

                        {{-- Content --}}
                        <div class="absolute inset-x-0 bottom-0 p-[16px] md:p-[20px]">
                            <h3 class="font-display {{ $textSize }} font-bold text-white leading-tight mb-[6px] text-balance">
                                {{ $category->name }}
                            </h3>
                            <span class="inline-flex items-center gap-[6px] text-xs font-medium text-phoenix-200 opacity-90 group-hover:opacity-100 group-hover:translate-x-[4px] rtl:group-hover:-translate-x-[4px] transition-all">
                                @lang('phonix::app.hero.cta_primary')
                                <svg class="w-[12px] h-[12px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach

            </div>
        @endif
    </div>
</section>
