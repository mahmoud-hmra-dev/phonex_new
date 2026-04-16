@props([
    'title',
    'subtitle' => null,
    'viewAllUrl' => null,
])

<div {{ $attributes->merge(['class' => 'text-center mb-[32px] lg:mb-[48px]']) }} data-gsap="fade-up">
    <h2 class="text-fluid-2xl font-bold text-slate-900 dark:text-white mb-[8px]">
        {{ $title }}
    </h2>

    {{-- Decorative underline --}}
    <div class="flex items-center justify-center gap-[8px] mb-[12px]">
        <span class="w-[32px] h-[2px] bg-phoenix-300 dark:bg-phoenix-600 rounded-full"></span>
        <span class="w-[48px] h-[3px] bg-phoenix-500 rounded-full"></span>
        <span class="w-[32px] h-[2px] bg-phoenix-300 dark:bg-phoenix-600 rounded-full"></span>
    </div>

    @if ($subtitle)
        <p class="text-fluid-sm text-slate-500 dark:text-slate-400 max-w-[480px] mx-auto">
            {{ $subtitle }}
        </p>
    @endif

    @if ($viewAllUrl)
        <a
            href="{{ $viewAllUrl }}"
            class="inline-flex items-center gap-[6px] mt-[16px] text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors"
        >
            @lang('phonix::app.general.view_all')
            <svg class="w-[16px] h-[16px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </a>
    @endif
</div>
