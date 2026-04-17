{{-- Hero Section — Premium full-width hero with gradient background --}}
<section
    class="relative overflow-hidden gradient-hero min-h-[400px] md:min-h-[500px] lg:min-h-[600px]"
    data-gsap="hero"
>
    {{-- Decorative background elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        {{-- Gradient orb top-right --}}
        <div class="absolute -top-[120px] -end-[120px] w-[400px] h-[400px] rounded-full bg-phoenix-500/10 blur-3xl"></div>
        {{-- Gradient orb bottom-left --}}
        <div class="absolute -bottom-[80px] -start-[80px] w-[300px] h-[300px] rounded-full bg-phoenix-400/10 blur-3xl"></div>
        {{-- Subtle grid pattern --}}
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, rgba(79,195,208,0.5) 1px, transparent 1px); background-size: 40px 40px;"></div>
        {{-- Animated floating circles --}}
        <div class="absolute top-[20%] end-[15%] w-[12px] h-[12px] rounded-full bg-phoenix-400/30 animate-float"></div>
        <div class="absolute top-[60%] end-[25%] w-[8px] h-[8px] rounded-full bg-coral/30 animate-float" style="animation-delay: 1s;"></div>
        <div class="absolute top-[40%] start-[10%] w-[6px] h-[6px] rounded-full bg-phoenix-300/40 animate-float" style="animation-delay: 0.5s;"></div>
        {{-- Decorative lines --}}
        <div class="absolute top-[30%] end-[35%] w-[80px] h-[1px] bg-gradient-to-r from-phoenix-400/0 via-phoenix-400/20 to-phoenix-400/0 rotate-45"></div>
        <div class="absolute bottom-[35%] start-[20%] w-[60px] h-[1px] bg-gradient-to-r from-phoenix-300/0 via-phoenix-300/20 to-phoenix-300/0 -rotate-12"></div>
    </div>

    <div class="container relative z-10 flex flex-col lg:flex-row items-center justify-between gap-[32px] lg:gap-[48px] py-[48px] md:py-[64px] lg:py-[80px]">
        {{-- Left: Text content --}}
        <div class="flex-1 text-center lg:text-start max-w-[600px]" data-gsap="fade-up">
            {{-- Small tag --}}
            <div class="inline-flex items-center gap-[8px] mb-[16px] px-[16px] py-[6px] rounded-full border border-phoenix-400/30 bg-phoenix-400/10 text-phoenix-300 text-xs font-medium tracking-wider uppercase">
                <span class="w-[6px] h-[6px] rounded-full bg-phoenix-400 animate-pulse-glow"></span>
                @lang('phonix::app.theme.name')
            </div>

            {{-- Main heading --}}
            <h1 class="text-fluid-5xl font-bold text-white mb-[16px] leading-tight">
                @lang('phonix::app.hero.title')
            </h1>

            {{-- Subtitle --}}
            <p class="text-fluid-base text-slate-300 mb-[32px] max-w-[500px] mx-auto lg:mx-0">
                @lang('phonix::app.hero.subtitle')
            </p>

            {{-- CTAs --}}
            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-[12px]">
                <a href="#" class="btn-phoenix px-[32px] py-[14px] text-base">
                    <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    @lang('phonix::app.hero.cta_primary')
                </a>
                <a href="#" class="btn-phoenix-outline border-white/30 text-white hover:bg-white/10 hover:border-white/50 px-[32px] py-[14px] text-base">
                    @lang('phonix::app.hero.cta_secondary')
                    <svg class="w-[20px] h-[20px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            {{-- Trust indicators --}}
            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-[24px] mt-[32px] text-slate-400 text-xs">
                <div class="flex items-center gap-[6px]">
                    <svg class="w-[16px] h-[16px] text-phoenix-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    @lang('phonix::app.features.secure_payment.title')
                </div>
                <div class="flex items-center gap-[6px]">
                    <svg class="w-[16px] h-[16px] text-phoenix-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    @lang('phonix::app.features.free_shipping.title')
                </div>
                <div class="flex items-center gap-[6px]">
                    <svg class="w-[16px] h-[16px] text-phoenix-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                    </svg>
                    @lang('phonix::app.features.money_back.title')
                </div>
            </div>
        </div>

        {{-- Right: Device visual placeholder --}}
        <div class="flex-1 flex items-center justify-center max-w-[500px] w-full" data-gsap="fade-up">
            <div class="relative w-full aspect-square max-w-[420px]">
                {{-- Glow ring --}}
                <div class="absolute inset-[10%] rounded-full bg-phoenix-500/10 blur-2xl animate-pulse-glow"></div>
                {{-- Main device image --}}
                <div class="relative w-full h-full rounded-2xl border border-phoenix-500/20 backdrop-blur-sm overflow-hidden">
                    {{-- Inner glow --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-phoenix-500/5 via-transparent to-phoenix-400/5 z-10"></div>
                    <img src="https://images.unsplash.com/photo-1616348436168-de43ad0db179?w=800&h=600&fit=crop"
                         alt="Latest smartphones and devices"
                         class="w-full h-full object-cover rounded-2xl"
                         loading="eager" />
                    {{-- Corner accents --}}
                    <div class="absolute top-[16px] end-[16px] w-[32px] h-[32px] border-t-2 border-e-2 border-phoenix-500/20 rounded-tr-lg z-10"></div>
                    <div class="absolute bottom-[16px] start-[16px] w-[32px] h-[32px] border-b-2 border-s-2 border-phoenix-500/20 rounded-bl-lg z-10"></div>
                </div>
            </div>
        </div>
    </div>
</section>
