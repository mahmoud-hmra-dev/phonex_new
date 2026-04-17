{{-- 404 Page Not Found --}}
<x-phonix::layouts.index :title="__('phonix::app.misc.404.title')">
    <div class="min-h-[80vh] flex items-center justify-center section-padding">
        <div class="text-center max-w-xl mx-auto" data-gsap="fade-up">
            {{-- Large 404 with Gradient --}}
            <div class="relative mb-[32px]">
                <h1 class="text-[120px] sm:text-[160px] md:text-[200px] font-black leading-none text-gradient-phoenix opacity-90 select-none" aria-hidden="true">
                    404
                </h1>

                {{-- Phoenix Shape SVG --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none" aria-hidden="true">
                    <svg class="w-[100px] h-[100px] sm:w-[140px] sm:h-[140px] text-phoenix-500/20 dark:text-phoenix-400/15 animate-float" viewBox="0 0 100 100" fill="currentColor">
                        <path d="M50 5C30 5 15 25 15 45c0 15 10 28 25 35l10 15 10-15c15-7 25-20 25-35C85 25 70 5 50 5zm0 55c-8.284 0-15-6.716-15-15 0-8.284 6.716-15 15-15 8.284 0 15 6.716 15 15 0 8.284-6.716 15-15 15z"/>
                    </svg>
                </div>
            </div>

            {{-- Heading --}}
            <h2 class="text-fluid-2xl font-bold text-slate-800 dark:text-slate-100 mb-[12px]">
                @lang('phonix::app.misc.404.title')
            </h2>

            {{-- Message --}}
            <p class="text-fluid-base text-slate-500 dark:text-slate-400 mb-[32px] max-w-md mx-auto">
                @lang('phonix::app.misc.404.tagline')
            </p>

            {{-- Search Bar --}}
            <div class="max-w-sm mx-auto mb-[32px]">
                <form action="{{ route('phonix.products.index') }}" method="GET" class="relative" role="search">
                    <input
                        type="search"
                        name="q"
                        class="input-phoenix pe-[48px] py-[14px]"
                        placeholder="@lang('phonix::app.misc.404.search_placeholder')"
                        aria-label="@lang('phonix::app.misc.404.search_placeholder')"
                    />
                    <button
                        type="submit"
                        class="absolute inset-y-0 end-0 flex items-center pe-[14px] text-slate-400 hover:text-phoenix-500 transition-colors"
                        aria-label="@lang('phonix::app.misc.404.search_button')"
                    >
                        <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>
                </form>
            </div>

            {{-- Back to Home --}}
            <a href="{{ route('phonix.home') }}" class="btn-phoenix">
                <svg class="w-[18px] h-[18px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                @lang('phonix::app.misc.404.back_home')
            </a>

            {{-- Suggested Links --}}
            <div class="mt-[48px] pt-[32px] border-t border-slate-200 dark:border-dark-border">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-[16px]">
                    @lang('phonix::app.misc.404.suggested_links')
                </p>
                <div class="flex flex-wrap justify-center gap-[12px]">
                    <a href="{{ route('phonix.home') }}" class="text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors px-[12px] py-[6px] rounded-full bg-phoenix-50 dark:bg-phoenix-900/20 hover:bg-phoenix-100 dark:hover:bg-phoenix-900/30">
                        @lang('phonix::app.general.home')
                    </a>
                    <a href="{{ route('phonix.products.index') }}" class="text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors px-[12px] py-[6px] rounded-full bg-phoenix-50 dark:bg-phoenix-900/20 hover:bg-phoenix-100 dark:hover:bg-phoenix-900/30">
                        @lang('phonix::app.general.shop')
                    </a>
                    <a href="#" class="text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors px-[12px] py-[6px] rounded-full bg-phoenix-50 dark:bg-phoenix-900/20 hover:bg-phoenix-100 dark:hover:bg-phoenix-900/30">
                        @lang('phonix::app.general.contact')
                    </a>
                    <a href="#" class="text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors px-[12px] py-[6px] rounded-full bg-phoenix-50 dark:bg-phoenix-900/20 hover:bg-phoenix-100 dark:hover:bg-phoenix-900/30">
                        @lang('phonix::app.general.faq')
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-phonix::layouts.index>
