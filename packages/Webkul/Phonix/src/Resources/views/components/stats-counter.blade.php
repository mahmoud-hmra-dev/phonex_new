{{-- Stats Counter Section --}}
<section class="gradient-phoenix section-padding" data-gsap="fade-up">
    <div class="container">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-[24px] md:gap-[32px]" data-gsap="stagger">
            @php
                $stats = [
                    [
                        'number' => '10,000',
                        'suffix' => '+',
                        'label' => __('phonix::app.stats.products_count'),
                        'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
                    ],
                    [
                        'number' => '50,000',
                        'suffix' => '+',
                        'label' => __('phonix::app.stats.happy_customers'),
                        'icon' => 'M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z',
                    ],
                    [
                        'number' => '100',
                        'suffix' => '+',
                        'label' => __('phonix::app.stats.brands_count'),
                        'icon' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z',
                    ],
                    [
                        'number' => '5',
                        'suffix' => '+',
                        'label' => __('phonix::app.stats.years_experience'),
                        'icon' => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
                    ],
                ];
            @endphp

            @foreach ($stats as $stat)
                <div class="text-center">
                    {{-- Icon --}}
                    <div class="flex items-center justify-center w-[48px] h-[48px] md:w-[56px] md:h-[56px] rounded-xl bg-white/15 mx-auto mb-[12px]">
                        <svg class="w-[24px] h-[24px] md:w-[28px] md:h-[28px] text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                        </svg>
                    </div>

                    {{-- Number --}}
                    <div class="text-fluid-3xl font-bold text-white mb-[4px]" data-gsap="counter">
                        {{ $stat['number'] }}<span>{{ $stat['suffix'] }}</span>
                    </div>

                    {{-- Label --}}
                    <p class="text-sm md:text-base text-white/70 font-medium">
                        {{ $stat['label'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
