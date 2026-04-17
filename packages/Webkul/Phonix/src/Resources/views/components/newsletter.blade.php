{{-- Newsletter Section --}}
<section class="gradient-hero section-padding" data-gsap="fade-up">
    <div class="container">
        <div class="relative max-w-[640px] mx-auto text-center">
            {{-- Decorative elements --}}
            <div class="absolute -top-[40px] -start-[60px] w-[120px] h-[120px] rounded-full bg-phoenix-500/10 blur-2xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute -bottom-[30px] -end-[40px] w-[80px] h-[80px] rounded-full bg-phoenix-400/10 blur-xl pointer-events-none" aria-hidden="true"></div>

            {{-- Icon --}}
            <div class="flex items-center justify-center w-[56px] h-[56px] rounded-full bg-white/10 mx-auto mb-[16px]">
                <svg class="w-[28px] h-[28px] text-phoenix-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>

            {{-- Heading --}}
            <h2 class="text-fluid-2xl font-bold text-white mb-[8px]">
                @lang('phonix::app.newsletter.title')
            </h2>

            {{-- Decorative underline --}}
            <div class="flex items-center justify-center gap-[8px] mb-[12px]">
                <span class="w-[32px] h-[2px] bg-phoenix-400/30 rounded-full"></span>
                <span class="w-[48px] h-[3px] bg-phoenix-400 rounded-full"></span>
                <span class="w-[32px] h-[2px] bg-phoenix-400/30 rounded-full"></span>
            </div>

            {{-- Subtitle --}}
            <p class="text-slate-300 text-fluid-sm mb-[28px] max-w-[480px] mx-auto">
                @lang('phonix::app.newsletter.subtitle')
            </p>

            {{-- Form --}}
            <div
                x-data="{ email: '', subscribed: false }"
                class="relative"
            >
                <div x-show="!subscribed" class="flex flex-col sm:flex-row items-stretch gap-[12px] max-w-[480px] mx-auto">
                    <input
                        x-model="email"
                        type="email"
                        class="flex-1 px-[20px] py-[14px] rounded-md bg-white/10 border border-white/20 text-white placeholder-slate-400 text-sm focus:outline-none focus:border-phoenix-400 focus:ring-2 focus:ring-phoenix-400/20 transition-all"
                        placeholder="@lang('phonix::app.newsletter.placeholder')"
                    />
                    <button
                        @click="if(email.includes('@')) { subscribed = true; }"
                        class="btn-phoenix px-[28px] py-[14px] whitespace-nowrap"
                    >
                        <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                        @lang('phonix::app.newsletter.subscribe')
                    </button>
                </div>

                {{-- Success state --}}
                <div
                    x-show="subscribed"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="flex items-center justify-center gap-[8px] text-phoenix-300 font-medium"
                    x-cloak
                >
                    <svg class="w-[24px] h-[24px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @lang('phonix::app.messages.success.subscribed')
                </div>
            </div>

            {{-- Privacy note --}}
            <p class="text-xs text-slate-400/70 mt-[16px]">
                @lang('phonix::app.newsletter.privacy')
            </p>
        </div>
    </div>
</section>
