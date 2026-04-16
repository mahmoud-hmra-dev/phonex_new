{{-- Testimonials Section --}}
<section class="section-padding" data-gsap="fade-up">
    <div class="container">
        <x-phonix::section-heading :title="__('phonix::app.testimonials.title')" />

        <div
            x-data="testimonialSlider()"
            x-init="init()"
            class="relative max-w-[1100px] mx-auto"
        >
            {{-- Slides container --}}
            <div class="overflow-hidden">
                <div
                    class="flex transition-transform duration-500 ease-phoenix"
                    :style="'transform: translateX(-' + (current * 100) + '%)'"
                >
                    @php
                        $testimonials = __('phonix::app.testimonials.items');
                        $ratings = [5, 5, 5];
                    @endphp

                    @foreach ($testimonials as $index => $testimonial)
                        <div class="w-full flex-shrink-0 px-[8px] md:px-[16px]">
                            <div class="card-glass p-[24px] md:p-[32px] lg:p-[40px] text-center max-w-[700px] mx-auto">
                                {{-- Quote icon --}}
                                <div class="flex items-center justify-center mb-[16px]">
                                    <svg class="w-[32px] h-[32px] md:w-[40px] md:h-[40px] text-phoenix-400/30" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zM0 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151C7.546 6.068 5.983 8.789 5.983 11H10v10H0z"/>
                                    </svg>
                                </div>

                                {{-- Stars --}}
                                <div class="flex items-center justify-center gap-[2px] mb-[16px]">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-[16px] h-[16px] {{ $i <= ($ratings[$index] ?? 5) ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>

                                {{-- Quote text --}}
                                <blockquote class="text-fluid-base text-slate-600 dark:text-slate-300 leading-relaxed mb-[20px] italic">
                                    "{{ $testimonial['text'] }}"
                                </blockquote>

                                {{-- Customer info --}}
                                <div>
                                    {{-- Avatar placeholder --}}
                                    <div class="w-[48px] h-[48px] rounded-full bg-gradient-to-br from-phoenix-400 to-phoenix-600 flex items-center justify-center text-white font-bold text-lg mx-auto mb-[8px]">
                                        {{ mb_substr($testimonial['name'], 0, 1) }}
                                    </div>
                                    <p class="font-semibold text-slate-800 dark:text-white text-sm">
                                        {{ $testimonial['name'] }}
                                    </p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">
                                        {{ $testimonial['role'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Navigation arrows --}}
            <button
                @click="prev()"
                class="absolute top-1/2 -translate-y-1/2 -start-[4px] md:-start-[20px] flex items-center justify-center w-[40px] h-[40px] rounded-full bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border shadow-md text-slate-500 dark:text-slate-400 hover:bg-phoenix-500 hover:text-white hover:border-phoenix-500 transition-all z-10"
                aria-label="@lang('phonix::app.general.previous')"
            >
                <svg class="w-[20px] h-[20px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
            <button
                @click="next()"
                class="absolute top-1/2 -translate-y-1/2 -end-[4px] md:-end-[20px] flex items-center justify-center w-[40px] h-[40px] rounded-full bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border shadow-md text-slate-500 dark:text-slate-400 hover:bg-phoenix-500 hover:text-white hover:border-phoenix-500 transition-all z-10"
                aria-label="@lang('phonix::app.general.next')"
            >
                <svg class="w-[20px] h-[20px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            {{-- Pagination dots --}}
            <div class="flex items-center justify-center gap-[8px] mt-[24px]">
                @for ($i = 0; $i < count($testimonials); $i++)
                    <button
                        @click="current = {{ $i }}"
                        :class="current === {{ $i }} ? 'w-[24px] bg-phoenix-500' : 'w-[8px] bg-slate-300 dark:bg-slate-600'"
                        class="h-[8px] rounded-full transition-all duration-300"
                        aria-label="Slide {{ $i + 1 }}"
                    ></button>
                @endfor
            </div>
        </div>
    </div>
</section>

@pushOnce('scripts')
<script>
    function testimonialSlider() {
        return {
            current: 0,
            total: {{ count(__('phonix::app.testimonials.items')) }},
            autoplayId: null,
            init() {
                this.autoplayId = setInterval(() => this.next(), 5000);
            },
            next() {
                this.current = (this.current + 1) % this.total;
            },
            prev() {
                this.current = (this.current - 1 + this.total) % this.total;
            },
        };
    }
</script>
@endPushOnce
