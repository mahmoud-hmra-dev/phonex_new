@props([
    'title',
    'subtitle' => null,
    'viewAllUrl' => null,
    'eyebrow' => null,
    'align' => 'center',
])

@php
    $isCenter = $align === 'center';
@endphp

<div {{ $attributes->merge(['class' => 'mb-[32px] lg:mb-[48px] flex flex-col ' . ($isCenter ? 'md:flex-row md:items-end md:justify-between gap-[16px]' : 'gap-[8px]')]) }} data-gsap="fade-up">
    <div class="{{ $isCenter ? 'text-center md:text-start' : '' }}">
        @if ($eyebrow)
            <p class="inline-flex items-center gap-[8px] mb-[10px] text-[11px] font-bold tracking-[0.2em] uppercase text-phoenix-600 dark:text-phoenix-400">
                <span class="inline-block w-[20px] h-[2px] rounded-full bg-phoenix-500"></span>
                {{ $eyebrow }}
            </p>
        @endif

        <h2 class="font-display text-fluid-2xl md:text-fluid-3xl font-bold text-slate-900 dark:text-white leading-[1.1] tracking-tight text-balance">
            {{ $title }}
        </h2>

        @if ($subtitle)
            <p class="mt-[10px] text-sm md:text-base text-slate-500 dark:text-slate-400 max-w-[560px] leading-relaxed">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if ($viewAllUrl)
        <a
            href="{{ $viewAllUrl }}"
            class="inline-flex items-center gap-[8px] self-start md:self-end text-sm font-semibold text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 group transition-colors"
        >
            @lang('phonix::app.general.view_all')
            <span class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-full border border-phoenix-500/30 group-hover:border-phoenix-500 group-hover:bg-phoenix-500 group-hover:text-white transition-all">
                <svg class="w-[12px] h-[12px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </span>
        </a>
    @endif
</div>
