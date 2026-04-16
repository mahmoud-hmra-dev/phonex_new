{{-- Features / Trust Badges Bar --}}
<section class="section-padding bg-slate-50 dark:bg-dark-surface" data-gsap="fade-up">
    <div class="container">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-[16px] md:gap-[24px]" data-gsap="stagger">
            @php
                $features = [
                    [
                        'key' => 'free_shipping',
                        'icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
                    ],
                    [
                        'key' => 'secure_payment',
                        'icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
                    ],
                    [
                        'key' => 'money_back',
                        'icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182',
                    ],
                    [
                        'key' => 'support_24_7',
                        'icon' => 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155',
                    ],
                    [
                        'key' => 'fast_delivery',
                        'icon' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
                    ],
                    [
                        'key' => 'warranty',
                        'icon' => 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z',
                    ],
                ];
            @endphp

            @foreach ($features as $feature)
                <div class="flex flex-col items-center text-center p-[16px] md:p-[20px] rounded-lg bg-white dark:bg-dark-card border border-slate-100 dark:border-dark-border hover:border-phoenix-200 dark:hover:border-phoenix-700 transition-colors">
                    <div class="flex items-center justify-center w-[44px] h-[44px] md:w-[48px] md:h-[48px] rounded-full bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-500 dark:text-phoenix-400 mb-[12px]">
                        <svg class="w-[22px] h-[22px] md:w-[24px] md:h-[24px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                        </svg>
                    </div>
                    <h3 class="text-xs md:text-sm font-semibold text-slate-800 dark:text-slate-200 mb-[4px]">
                        @lang('phonix::app.features.' . $feature['key'] . '.title')
                    </h3>
                    <p class="text-[11px] md:text-xs text-slate-400 dark:text-slate-500 leading-relaxed hidden md:block">
                        @lang('phonix::app.features.' . $feature['key'] . '.description')
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
