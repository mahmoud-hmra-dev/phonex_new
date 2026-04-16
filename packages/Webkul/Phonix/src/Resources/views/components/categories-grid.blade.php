{{-- Categories Grid Section --}}
<section class="section-padding" data-gsap="fade-up">
    <div class="container">
        <x-phonix::section-heading
            :title="__('phonix::app.categories.title')"
            :viewAllUrl="'#'"
        />

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-[16px] md:gap-[24px]" data-gsap="stagger">
            @php
                $categories = [
                    ['key' => 'phones', 'icon' => 'M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3', 'count' => '2,500+'],
                    ['key' => 'laptops', 'icon' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25', 'count' => '1,200+'],
                    ['key' => 'tablets', 'icon' => 'M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 002.25-2.25v-15a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25z', 'count' => '800+'],
                    ['key' => 'smartwatches', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'count' => '600+'],
                    ['key' => 'audio', 'icon' => 'M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z', 'count' => '1,500+'],
                    ['key' => 'accessories', 'icon' => 'M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25', 'count' => '3,000+'],
                ];
            @endphp

            @foreach ($categories as $category)
                <a
                    href="#"
                    class="group flex flex-col items-center gap-[12px] p-[20px] md:p-[24px] rounded-lg bg-white dark:bg-dark-card border border-slate-100 dark:border-dark-border hover:border-phoenix-300 dark:hover:border-phoenix-600 hover:shadow-lg hover:-translate-y-[2px] transition-all duration-200 ease-phoenix"
                >
                    <div class="flex items-center justify-center w-[56px] h-[56px] md:w-[64px] md:h-[64px] rounded-xl bg-phoenix-50 dark:bg-phoenix-900/30 text-phoenix-500 dark:text-phoenix-400 group-hover:bg-phoenix-500 group-hover:text-white dark:group-hover:bg-phoenix-500 transition-colors duration-200">
                        <svg class="w-[28px] h-[28px] md:w-[32px] md:h-[32px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $category['icon'] }}" />
                        </svg>
                    </div>
                    <div class="text-center">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-phoenix-600 dark:group-hover:text-phoenix-400 transition-colors">
                            @lang('phonix::app.categories.' . $category['key'])
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-[4px]">
                            {{ $category['count'] }} @lang('phonix::app.stats.products_count')
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
