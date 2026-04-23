{{-- Trust Strip — premium horizontal badges --}}
<section class="relative -mt-[24px] z-20 px-[16px] md:px-[24px] lg:px-[32px]" data-gsap="fade-up">
    <div class="container">
        <div class="rounded-2xl bg-white dark:bg-dark-card border border-slate-100 dark:border-dark-border shadow-[0_8px_32px_-12px_rgba(15,23,42,0.12)] overflow-hidden">
            <div class="grid grid-cols-2 md:grid-cols-4 divide-x rtl:divide-x-reverse divide-slate-100 dark:divide-dark-border">
                @php
                    $features = [
                        ['key' => 'free_shipping', 'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .956-.343 1.087-.835l2.25-8.482a.75.75 0 00-.725-.952H5.106m0 0L4.32 2.272M7.5 14.25a3 3 0 00-3 3h15.75m-8.25 3.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm7.5 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z'],
                        ['key' => 'secure_payment', 'icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
                        ['key' => 'money_back', 'icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182'],
                        ['key' => 'support_24_7', 'icon' => 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951'],
                    ];
                @endphp
                @foreach ($features as $f)
                    <div class="flex items-center gap-[14px] p-[20px] md:p-[24px] group hover:bg-slate-50 dark:hover:bg-dark-surface transition-colors">
                        <span class="flex items-center justify-center w-[44px] h-[44px] rounded-xl bg-gradient-to-br from-phoenix-50 to-phoenix-100 dark:from-phoenix-900/40 dark:to-phoenix-900/20 text-phoenix-600 dark:text-phoenix-400 shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[13px] md:text-sm font-semibold text-slate-900 dark:text-white leading-tight mb-[2px] line-clamp-1">
                                @lang('phonix::app.features.' . $f['key'] . '.title')
                            </p>
                            <p class="text-[11px] md:text-xs text-slate-500 dark:text-slate-400 leading-tight line-clamp-2 hidden sm:block">
                                @lang('phonix::app.features.' . $f['key'] . '.description')
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
