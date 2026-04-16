{{-- Phonix Theme - Premium Footer --}}
<footer class="mt-[48px]" data-gsap="fade-up">
    {{-- Newsletter Section --}}
    @if (core()->getConfigData('customer.settings.newsletter.subscription'))
        <section class="gradient-phoenix section-padding">
            <div class="container">
                <div class="max-w-[600px] mx-auto text-center">
                    <h2 class="text-fluid-2xl font-bold text-white mb-[8px]">
                        @lang('phonix::app.footer.newsletter.title')
                    </h2>
                    <p class="text-phoenix-100 text-fluid-sm mb-[24px]">
                        @lang('phonix::app.footer.newsletter.subtitle')
                    </p>

                    <form
                        action="{{ route('shop.subscription.store') }}"
                        method="POST"
                        class="flex flex-col sm:flex-row gap-[12px]"
                    >
                        @csrf
                        <div class="relative flex-1">
                            <input
                                type="email"
                                name="email"
                                required
                                class="w-full px-[20px] py-[14px] text-sm bg-white/10 backdrop-blur-sm text-white placeholder-phoenix-200 border border-white/20 rounded-md focus:outline-none focus:border-white/50 focus:ring-2 focus:ring-white/20 transition-all"
                                placeholder="@lang('phonix::app.footer.newsletter.placeholder')"
                                aria-label="@lang('phonix::app.footer.newsletter.placeholder')"
                            />
                        </div>
                        <button
                            type="submit"
                            class="px-[28px] py-[14px] text-sm font-semibold bg-white text-phoenix-700 rounded-md hover:bg-phoenix-50 transition-colors shrink-0"
                        >
                            @lang('phonix::app.footer.newsletter.subscribe')
                        </button>
                    </form>
                </div>
            </div>
        </section>
    @endif

    {{-- Main Footer --}}
    <div class="bg-phoenix-950 dark:bg-dark-bg text-slate-300">
        <div class="container section-padding">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-[32px] lg:gap-[48px]">
                {{-- Column 1: Brand --}}
                <div>
                    <a
                        href="{{ url('/') }}"
                        class="inline-block mb-[16px]"
                        aria-label="@lang('phonix::app.theme.name')"
                    >
                        <span class="text-fluid-xl font-poppins font-bold tracking-tight text-gradient-phoenix">
                            PHONIX
                        </span>
                    </a>
                    <p class="text-sm text-slate-400 leading-relaxed mb-[20px]">
                        @lang('phonix::app.footer.about.description')
                    </p>

                    {{-- Social Icons --}}
                    <div>
                        <p class="text-sm font-semibold text-white mb-[12px]">
                            @lang('phonix::app.footer.social.follow_us')
                        </p>
                        <div class="flex items-center gap-[12px]">
                            {{-- Facebook --}}
                            <a
                                href="#"
                                class="flex items-center justify-center w-[36px] h-[36px] rounded-md bg-white/5 hover:bg-phoenix-500 text-slate-400 hover:text-white transition-colors"
                                aria-label="@lang('phonix::app.footer.social.facebook')"
                            >
                                <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            {{-- Twitter/X --}}
                            <a
                                href="#"
                                class="flex items-center justify-center w-[36px] h-[36px] rounded-md bg-white/5 hover:bg-phoenix-500 text-slate-400 hover:text-white transition-colors"
                                aria-label="@lang('phonix::app.footer.social.twitter')"
                            >
                                <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                            {{-- Instagram --}}
                            <a
                                href="#"
                                class="flex items-center justify-center w-[36px] h-[36px] rounded-md bg-white/5 hover:bg-phoenix-500 text-slate-400 hover:text-white transition-colors"
                                aria-label="@lang('phonix::app.footer.social.instagram')"
                            >
                                <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/>
                                </svg>
                            </a>
                            {{-- YouTube --}}
                            <a
                                href="#"
                                class="flex items-center justify-center w-[36px] h-[36px] rounded-md bg-white/5 hover:bg-phoenix-500 text-slate-400 hover:text-white transition-colors"
                                aria-label="@lang('phonix::app.footer.social.youtube')"
                            >
                                <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                            {{-- TikTok --}}
                            <a
                                href="#"
                                class="flex items-center justify-center w-[36px] h-[36px] rounded-md bg-white/5 hover:bg-phoenix-500 text-slate-400 hover:text-white transition-colors"
                                aria-label="@lang('phonix::app.footer.social.tiktok')"
                            >
                                <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Column 2: Quick Links --}}
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-[16px]">
                        @lang('phonix::app.footer.about.title')
                    </h3>
                    <ul class="space-y-[10px]">
                        @php
                            $quickLinks = ['about_us', 'contact_us', 'blog', 'careers', 'privacy_policy', 'terms_conditions'];
                        @endphp
                        @foreach ($quickLinks as $link)
                            <li>
                                <a
                                    href="#"
                                    class="text-sm text-slate-400 hover:text-phoenix-400 transition-colors"
                                >
                                    @lang('phonix::app.footer.links.' . $link)
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Column 3: Customer Service --}}
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-[16px]">
                        @lang('phonix::app.header.nav.support')
                    </h3>
                    <ul class="space-y-[10px]">
                        @php
                            $serviceLinks = [
                                ['key' => 'header.nav.track_order', 'url' => '#'],
                                ['key' => 'footer.links.return_policy', 'url' => '#'],
                                ['key' => 'footer.links.shipping_info', 'url' => '#'],
                                ['key' => 'footer.links.faq', 'url' => '#'],
                            ];
                        @endphp
                        @foreach ($serviceLinks as $sLink)
                            <li>
                                <a
                                    href="{{ $sLink['url'] }}"
                                    class="text-sm text-slate-400 hover:text-phoenix-400 transition-colors"
                                >
                                    @lang('phonix::app.' . $sLink['key'])
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Column 4: Contact Info --}}
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-[16px]">
                        @lang('phonix::app.footer.links.contact_us')
                    </h3>
                    <ul class="space-y-[12px]">
                        {{-- Phone --}}
                        <li class="flex items-start gap-[10px]">
                            <svg class="w-[18px] h-[18px] text-phoenix-400 shrink-0 mt-[2px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                            <div>
                                <p class="text-xs text-slate-500 mb-[2px]">@lang('phonix::app.footer.contact.phone')</p>
                                <a href="tel:+966500000000" class="text-sm text-slate-300 hover:text-phoenix-400 transition-colors" dir="ltr">
                                    +966 50 000 0000
                                </a>
                            </div>
                        </li>
                        {{-- Email --}}
                        <li class="flex items-start gap-[10px]">
                            <svg class="w-[18px] h-[18px] text-phoenix-400 shrink-0 mt-[2px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            <div>
                                <p class="text-xs text-slate-500 mb-[2px]">@lang('phonix::app.footer.contact.email')</p>
                                <a href="mailto:support@phonix.store" class="text-sm text-slate-300 hover:text-phoenix-400 transition-colors">
                                    support@phonix.store
                                </a>
                            </div>
                        </li>
                        {{-- Working Hours --}}
                        <li class="flex items-start gap-[10px]">
                            <svg class="w-[18px] h-[18px] text-phoenix-400 shrink-0 mt-[2px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-xs text-slate-500 mb-[2px]">@lang('phonix::app.footer.contact.working_hours')</p>
                                <p class="text-sm text-slate-300">9:00 AM - 10:00 PM</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="border-t border-white/10">
            <div class="container flex flex-col md:flex-row items-center justify-between py-[16px] gap-[12px]">
                {{-- Copyright --}}
                <p class="text-xs text-slate-500 text-center md:text-start">
                    @lang('phonix::app.footer.copyright')
                </p>

                {{-- Payment Method Icons --}}
                <div class="flex items-center gap-[16px]">
                    <span class="text-xs text-slate-500 hidden sm:inline">
                        @lang('phonix::app.footer.payment.secure_payment')
                    </span>
                    <div class="flex items-center gap-[8px]">
                        {{-- Visa --}}
                        <div class="w-[40px] h-[26px] bg-white/10 rounded flex items-center justify-center text-[10px] font-bold text-slate-400">
                            VISA
                        </div>
                        {{-- Mastercard --}}
                        <div class="w-[40px] h-[26px] bg-white/10 rounded flex items-center justify-center text-[10px] font-bold text-slate-400">
                            MC
                        </div>
                        {{-- Apple Pay --}}
                        <div class="w-[40px] h-[26px] bg-white/10 rounded flex items-center justify-center text-[10px] font-bold text-slate-400">
                            AP
                        </div>
                        {{-- Mada --}}
                        <div class="w-[40px] h-[26px] bg-white/10 rounded flex items-center justify-center text-[10px] font-bold text-slate-400">
                            mada
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
