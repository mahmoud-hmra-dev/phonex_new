@php
    $footerCategories = app(\Webkul\Category\Repositories\CategoryRepository::class)
        ->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id)
        ->filter(fn ($c) => $c->id !== 1)
        ->take(6);
@endphp

{{-- Phonix — Premium Footer --}}
<footer class="mt-[64px] relative isolate">
    {{-- Newsletter CTA slab --}}
    @if (core()->getConfigData('customer.settings.newsletter.subscription'))
        <section class="container -mb-[48px] relative z-10 px-[16px] md:px-[24px] lg:px-[32px]">
            <div class="relative overflow-hidden rounded-3xl p-[32px] md:p-[48px] lg:p-[56px] bg-gradient-to-br from-phoenix-600 via-phoenix-700 to-phoenix-900 shadow-[0_32px_64px_-20px_rgba(79,70,229,0.4)]">
                <div class="absolute -top-[60px] -start-[60px] w-[300px] h-[300px] rounded-full bg-phoenix-400/20 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-[80px] -end-[80px] w-[400px] h-[400px] rounded-full bg-plasma-500/15 blur-3xl pointer-events-none"></div>
                <div class="relative grid md:grid-cols-2 gap-[24px] md:gap-[48px] items-center">
                    <div>
                        <p class="inline-flex items-center gap-[8px] text-xs font-semibold uppercase tracking-[0.18em] text-phoenix-200 mb-[12px]">
                            <span class="inline-block w-[24px] h-[1px] bg-phoenix-300"></span>
                            @lang('phonix::app.newsletter.title')
                        </p>
                        <h2 class="font-display text-fluid-2xl md:text-fluid-3xl font-bold text-white leading-tight mb-[8px]">
                            @lang('phonix::app.footer.newsletter.subtitle')
                        </h2>
                        <p class="text-phoenix-100/90 text-sm">
                            @lang('phonix::app.newsletter.privacy')
                        </p>
                    </div>
                    <form action="{{ route('shop.subscription.store') }}" method="POST" data-turbo="false" class="w-full">
                        @csrf
                        <div class="flex flex-col sm:flex-row gap-[10px]">
                            <label class="relative flex-1">
                                <svg class="absolute start-[16px] top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-phoenix-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                <input type="email" name="email" required class="w-full ps-[46px] pe-[16px] py-[14px] text-sm bg-white/10 backdrop-blur-sm text-white placeholder-phoenix-200/70 border border-white/20 rounded-full focus:outline-none focus:bg-white/15 focus:border-white/40 focus:ring-4 focus:ring-white/10 transition-all" placeholder="@lang('phonix::app.footer.newsletter.placeholder')" aria-label="@lang('phonix::app.footer.newsletter.placeholder')"/>
                            </label>
                            <button type="submit" class="px-[28px] py-[14px] text-sm font-semibold bg-white text-phoenix-700 rounded-full hover:bg-phoenix-50 active:scale-[0.98] shadow-lg transition-all shrink-0">
                                @lang('phonix::app.footer.newsletter.subscribe')
                                <svg class="inline-block w-[14px] h-[14px] ms-[6px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    @endif

    {{-- Main footer --}}
    <div class="relative bg-slate-950 text-slate-300 pt-[96px] md:pt-[120px]">
        {{-- Subtle mesh --}}
        <div class="absolute inset-0 opacity-[0.45] pointer-events-none"
             style="background:
                radial-gradient(ellipse at 20% 0%, rgba(79, 70, 229, 0.18) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(255, 71, 87, 0.08) 0%, transparent 50%);">
        </div>

        <div class="relative container section-padding !pt-[40px]">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-12 gap-[32px] lg:gap-[40px]">

                {{-- Brand & Description --}}
                <div class="col-span-2 lg:col-span-4">
                    <a href="{{ route('phonix.home') }}" class="inline-flex mb-[20px]" aria-label="@lang('phonix::app.theme.name')">
                        <img src="{{ asset('phonix-logo.png') }}" alt="Phonix" class="h-[44px] w-auto">
                    </a>

                    <p class="text-sm text-slate-400 leading-relaxed mb-[24px] max-w-[420px]">
                        @lang('phonix::app.footer.about.description')
                    </p>

                    {{-- Trust highlights --}}
                    <div class="grid grid-cols-2 gap-[12px] mb-[28px] max-w-[420px]">
                        <div class="flex items-center gap-[10px] text-xs text-slate-400">
                            <span class="flex items-center justify-center w-[36px] h-[36px] rounded-xl bg-phoenix-500/15 text-phoenix-300">
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-white">@lang('phonix::app.features.secure_payment.title')</p>
                                <p class="text-[11px] text-slate-500">SSL Encrypted</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-[10px] text-xs text-slate-400">
                            <span class="flex items-center justify-center w-[36px] h-[36px] rounded-xl bg-plasma-500/15 text-plasma-300">
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .956-.343 1.087-.835l2.25-8.482a.75.75 0 00-.725-.952H5.106m0 0L4.32 2.272"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-white">@lang('phonix::app.features.fast_delivery.title')</p>
                                <p class="text-[11px] text-slate-500">24-48 hours</p>
                            </div>
                        </div>
                    </div>

                    {{-- Social --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 mb-[12px]">@lang('phonix::app.footer.social.follow_us')</p>
                        <div class="flex items-center gap-[8px]">
                            @foreach ([
                                ['aria' => 'Facebook',  'path' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
                                ['aria' => 'X',         'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
                                ['aria' => 'Instagram', 'path' => 'M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z'],
                                ['aria' => 'YouTube',   'path' => 'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z'],
                                ['aria' => 'TikTok',    'path' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z'],
                            ] as $s)
                                <a href="#" aria-label="{{ $s['aria'] }}" class="flex items-center justify-center w-[38px] h-[38px] rounded-xl bg-white/5 hover:bg-phoenix-500 border border-white/5 hover:border-phoenix-500 text-slate-400 hover:text-white transition-all hover:-translate-y-[2px]">
                                    <svg class="w-[16px] h-[16px]" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $s['path'] }}"/></svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Shop categories --}}
                <div class="lg:col-span-2">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-[0.14em] mb-[16px]">
                        @lang('phonix::app.header.nav.categories')
                    </h3>
                    <ul class="space-y-[10px]">
                        @foreach ($footerCategories as $fc)
                            <li>
                                <a href="{{ route('phonix.products.index', ['category_ids' => [$fc->id]]) }}" class="text-sm text-slate-400 hover:text-phoenix-300 hover:ps-[4px] transition-all">
                                    {{ $fc->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Help --}}
                <div class="lg:col-span-2">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-[0.14em] mb-[16px]">
                        @lang('phonix::app.header.nav.support')
                    </h3>
                    <ul class="space-y-[10px]">
                        @foreach ([
                            ['key' => 'header.nav.track_order',    'url' => route('phonix.account.orders')],
                            ['key' => 'footer.links.shipping_info','url' => route('phonix.products.index')],
                            ['key' => 'footer.links.return_policy','url' => route('phonix.products.index')],
                            ['key' => 'footer.links.faq',          'url' => route('phonix.products.index')],
                            ['key' => 'footer.links.contact_us',   'url' => route('phonix.products.index')],
                        ] as $link)
                            <li>
                                <a href="{{ $link['url'] }}" class="text-sm text-slate-400 hover:text-phoenix-300 hover:ps-[4px] transition-all">
                                    @lang('phonix::app.' . $link['key'])
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Contact --}}
                <div class="col-span-2 md:col-span-2 lg:col-span-4">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-[0.14em] mb-[16px]">
                        @lang('phonix::app.footer.links.contact_us')
                    </h3>
                    <ul class="space-y-[16px]">
                        <li class="flex items-start gap-[12px]">
                            <span class="flex items-center justify-center w-[36px] h-[36px] rounded-xl bg-phoenix-500/15 text-phoenix-300 shrink-0">
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            </span>
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wide mb-[2px]">@lang('phonix::app.footer.contact.phone')</p>
                                <a href="tel:+966500000000" dir="ltr" class="text-sm font-semibold text-white hover:text-phoenix-300 transition-colors">+966 50 000 0000</a>
                            </div>
                        </li>
                        <li class="flex items-start gap-[12px]">
                            <span class="flex items-center justify-center w-[36px] h-[36px] rounded-xl bg-phoenix-500/15 text-phoenix-300 shrink-0">
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            </span>
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wide mb-[2px]">@lang('phonix::app.footer.contact.email')</p>
                                <a href="mailto:support@phonix.store" class="text-sm font-semibold text-white hover:text-phoenix-300 transition-colors">support@phonix.store</a>
                            </div>
                        </li>
                        <li class="flex items-start gap-[12px]">
                            <span class="flex items-center justify-center w-[36px] h-[36px] rounded-xl bg-phoenix-500/15 text-phoenix-300 shrink-0">
                                <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wide mb-[2px]">@lang('phonix::app.footer.contact.working_hours')</p>
                                <p class="text-sm font-semibold text-white">9:00 AM — 10:00 PM</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="relative border-t border-white/10">
            <div class="container flex flex-col md:flex-row items-center justify-between gap-[16px] py-[22px]">
                <p class="text-xs text-slate-500 text-center md:text-start">
                    @lang('phonix::app.footer.copyright')
                </p>

                <div class="flex items-center gap-[16px]">
                    <span class="text-[11px] text-slate-500 uppercase tracking-wider hidden sm:inline">
                        @lang('phonix::app.footer.payment.methods')
                    </span>
                    <div class="flex items-center gap-[6px]">
                        {{-- Visa --}}
                        <span class="flex items-center justify-center w-[46px] h-[30px] rounded-md bg-white px-[6px]" aria-label="Visa">
                            <svg viewBox="0 0 48 16" class="w-full h-full" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path fill="#1434CB" d="M19.56 15.47h-3.84l2.4-14.46h3.84l-2.4 14.46zM12.79 1.01l-3.66 9.94-.43-2.19L7.4 2.73s-.16-1.72-2.33-1.72H.3l-.07.24s2.41.5 5.23 2.2l3.34 12.02h4L19 1.01h-6.2zM45.11 15.47h3.53l-3.08-14.46h-3.09c-1.43 0-1.78 1.1-1.78 1.1L35.2 15.47h4l.8-2.19h4.88l.23 2.19zm-4.23-5.22l2.01-5.5 1.13 5.5h-3.14zM35.5 4.56l.55-3.17s-1.69-.64-3.44-.64c-1.9 0-6.41.83-6.41 4.86 0 3.79 5.29 3.84 5.29 5.83 0 1.99-4.74 1.63-6.31.37l-.57 3.31s1.71.83 4.34.83c2.62 0 6.59-1.36 6.59-5.03 0-3.81-5.34-4.17-5.34-5.83 0-1.66 3.72-1.44 5.3-.53z"/>
                            </svg>
                        </span>
                        {{-- Mastercard --}}
                        <span class="flex items-center justify-center w-[46px] h-[30px] rounded-md bg-white" aria-label="Mastercard">
                            <svg viewBox="0 0 48 32" class="w-[32px] h-[22px]" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <circle fill="#EB001B" cx="18" cy="16" r="12"/>
                                <circle fill="#F79E1B" cx="30" cy="16" r="12"/>
                                <path fill="#FF5F00" d="M24 7a12 12 0 000 18 12 12 0 000-18z"/>
                            </svg>
                        </span>
                        {{-- Apple Pay --}}
                        <span class="flex items-center justify-center w-[46px] h-[30px] rounded-md bg-white px-[6px]" aria-label="Apple Pay">
                            <svg viewBox="0 0 48 20" class="w-full h-full" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="#000">
                                <path d="M8.3 4.4a2.7 2.7 0 001.7-3 2.8 2.8 0 00-1.7.9 2.6 2.6 0 00-.7 1.8c0 .1.3.3.7.3zM10.2 5c-.9 0-1.7.5-2.2.5s-1.1-.5-1.9-.5c-1 0-2 .6-2.5 1.5-1 1.8-.3 4.4.7 5.8.5.7 1 1.5 1.8 1.5s1-.5 2-.5 1.2.5 1.9.4c.8 0 1.3-.7 1.8-1.4a6 6 0 00.8-1.7 2.6 2.6 0 01-1.6-2.4 2.6 2.6 0 011.2-2.1A2.9 2.9 0 0010.2 5zM17.2 3.6h3c2.3 0 3.9 1.6 3.9 4 0 2.3-1.6 4-3.9 4h-1.6v3.4h-1.4V3.6zm1.4 1.2v5.7h1.5c1.5 0 2.5-1 2.5-2.8 0-1.8-1-2.9-2.5-2.9h-1.5zm6.2 7.5c0-1.5 1.1-2.4 3-2.5l2.3-.2v-.7c0-1-.7-1.5-1.8-1.5-1 0-1.7.4-1.9 1.1h-1.4c.1-1.5 1.4-2.5 3.4-2.5 2 0 3.1 1 3.1 2.7V15h-1.4v-1.3c-.4.9-1.4 1.5-2.5 1.5-1.7 0-2.8-1-2.8-2.9zm5.3-.7v-.7l-2 .1c-1 .1-1.6.6-1.6 1.3s.6 1.2 1.5 1.2c1.1 0 2-.8 2-1.9zm3.2 5.2v-1.1c.2.1.5.1.7.1.8 0 1.2-.4 1.5-1.2l.2-.6L32 7.9h1.5l2 6.5h.1l2-6.5h1.5l-2.9 8.3c-.7 1.9-1.5 2.5-3.1 2.5-.2 0-.6 0-.8-.1z"/>
                            </svg>
                        </span>
                        {{-- Google Pay --}}
                        <span class="flex items-center justify-center w-[46px] h-[30px] rounded-md bg-white px-[6px]" aria-label="Google Pay">
                            <svg viewBox="0 0 48 20" class="w-full h-full" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path fill="#5F6368" d="M22.8 10.1v3.5h-1.1V4.9h3a2.7 2.7 0 011.9.8 2.6 2.6 0 010 3.7 2.7 2.7 0 01-1.9.7h-1.9zm0-4.2v3.1h2a1.5 1.5 0 001.1-.4 1.5 1.5 0 000-2.2 1.5 1.5 0 00-1.1-.5h-2zM29.6 7.2c.8 0 1.4.2 1.9.6.4.4.7 1 .7 1.8v3.5h-1v-.8c-.5.7-1.1 1-1.9 1-.7 0-1.3-.2-1.7-.6a2 2 0 01-.7-1.5 1.8 1.8 0 01.8-1.5c.5-.4 1.1-.6 2-.6.7 0 1.3.2 1.8.5v-.3a1.3 1.3 0 00-.5-1 1.6 1.6 0 00-1.1-.4 1.8 1.8 0 00-1.6.9l-.9-.6a2.7 2.7 0 012.3-1.1zm-1.5 4.5a1 1 0 00.4.8c.3.2.6.3 1 .3a2 2 0 001.4-.6 1.8 1.8 0 00.6-1.3 2.5 2.5 0 00-1.6-.5c-.5 0-.9.1-1.3.4a1 1 0 00-.5.9zM37.5 7.4l-3.7 8.6h-1.1l1.4-3-2.5-5.6h1.2L35 12l1.8-4.6h1.1z"/>
                                <path fill="#4285F4" d="M14.8 10a7 7 0 00-.1-1.1h-5v2h2.9a2.5 2.5 0 01-1.1 1.7v1.4h1.8a5.4 5.4 0 001.5-4z"/>
                                <path fill="#34A853" d="M9.7 15.1a5 5 0 003.5-1.3l-1.8-1.4a3.2 3.2 0 01-4.7-1.6h-1.8v1.4a5.3 5.3 0 004.8 2.9z"/>
                                <path fill="#FBBC04" d="M6.7 10.8a3.2 3.2 0 010-2.1V7.3H4.9a5.3 5.3 0 000 4.8l1.8-1.4z"/>
                                <path fill="#EA4335" d="M9.7 6.6a2.9 2.9 0 012 .8l1.6-1.6a5 5 0 00-3.6-1.4A5.3 5.3 0 004.9 7.3l1.8 1.4a3.2 3.2 0 013-2z"/>
                            </svg>
                        </span>
                        {{-- Mada --}}
                        <span class="flex items-center justify-center w-[46px] h-[30px] rounded-md bg-white text-[9px] font-extrabold text-slate-900" aria-label="Mada">
                            <span>mada</span>
                        </span>
                        {{-- COD --}}
                        <span class="flex items-center justify-center w-[46px] h-[30px] rounded-md bg-white text-[8px] font-extrabold text-slate-900" aria-label="Cash on Delivery">
                            <span>COD</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
