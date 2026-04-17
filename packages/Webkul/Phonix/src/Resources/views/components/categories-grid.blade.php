@props(['categories' => collect()])

{{-- Categories Grid Section --}}
<section class="section-padding" data-gsap="fade-up">
    <div class="container">
        <x-phonix::section-heading
            :title="__('phonix::app.categories.title')"
        />

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-[16px] md:gap-[24px]" data-gsap="stagger">
            @foreach ($categories as $category)
                <a
                    href="{{ route('phonix.categories.view', $category->slug) }}"
                    class="group flex flex-col items-center gap-[12px] p-[20px] md:p-[24px] rounded-lg bg-white dark:bg-dark-card border border-slate-100 dark:border-dark-border hover:border-phoenix-300 dark:hover:border-phoenix-600 hover:shadow-lg hover:-translate-y-[2px] transition-all duration-200 ease-phoenix"
                >
                    <div class="relative flex items-center justify-center w-[56px] h-[56px] md:w-[64px] md:h-[64px] rounded-xl bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-500 dark:text-phoenix-400 group-hover:bg-phoenix-500 group-hover:text-white dark:group-hover:bg-phoenix-500 transition-colors duration-200 overflow-hidden">
                        @if($category->logo_url)
                            <img src="{{ $category->logo_url }}" alt="{{ $category->name }}" class="w-full h-full object-cover rounded-xl" loading="lazy" />
                        @elseif($category->banner_url)
                            <img src="{{ $category->banner_url }}" alt="{{ $category->name }}" class="absolute inset-0 w-full h-full object-cover opacity-20 group-hover:opacity-30 transition-opacity" loading="lazy" />
                            <svg class="relative z-10 w-[28px] h-[28px] md:w-[32px] md:h-[32px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z" />
                            </svg>
                        @else
                            <svg class="relative z-10 w-[28px] h-[28px] md:w-[32px] md:h-[32px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                            </svg>
                        @endif
                    </div>
                    <div class="text-center">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-phoenix-600 dark:group-hover:text-phoenix-400 transition-colors">
                            {{ $category->name }}
                        </h3>
                        @if($category->products_count ?? false)
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-[4px]">
                                {{ $category->products_count }} @lang('phonix::app.stats.products_count')
                            </p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
