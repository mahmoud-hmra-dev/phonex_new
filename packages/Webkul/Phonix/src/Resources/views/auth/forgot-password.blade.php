{{-- Forgot Password Page --}}
<x-phonix::layouts.index :hasHeader="false" :hasFooter="false">
    <div class="min-h-screen flex items-center justify-center p-[16px] bg-gradient-to-br from-slate-50 via-phoenix-50/30 to-slate-50 dark:from-dark-bg dark:via-phoenix-950/20 dark:to-dark-bg">
        {{-- Background Pattern --}}
        <div class="fixed inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-1/2 start-1/2 w-[500px] h-[500px] bg-phoenix-400/10 dark:bg-phoenix-400/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        </div>

        <div
            class="w-full max-w-[420px] relative z-10"
            data-gsap="fade-up"
            x-data="{ sent: false, email: '' }"
        >
            {{-- Logo --}}
            <div class="text-center mb-[32px]">
                <a href="{{ route('phonix.home') }}" class="inline-block" aria-label="@lang('phonix::app.theme.name')">
                    <span class="text-fluid-2xl font-bold text-gradient-phoenix">Phonix</span>
                </a>
            </div>

            {{-- Card --}}
            <div class="card-phoenix p-[32px]">
                {{-- Default State: Form --}}
                <div x-show="!sent">
                    {{-- Lock Icon --}}
                    <div class="flex justify-center mb-[20px]">
                        <div class="w-[56px] h-[56px] rounded-full bg-phoenix-100 dark:bg-phoenix-900/30 flex items-center justify-center">
                            <svg class="w-[28px] h-[28px] text-phoenix-600 dark:text-phoenix-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </div>
                    </div>

                    <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 text-center mb-[8px]">
                        @lang('phonix::app.auth.forgot.title')
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 text-center mb-[24px]">
                        @lang('phonix::app.auth.forgot.description')
                    </p>

                    <form @submit.prevent="sent = true" class="space-y-[16px]" novalidate>
                        <div>
                            <label for="forgot-email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                @lang('phonix::app.auth.forgot.email')
                            </label>
                            <input
                                type="email"
                                id="forgot-email"
                                x-model="email"
                                class="input-phoenix"
                                placeholder="you@example.com"
                                required
                                aria-required="true"
                                autocomplete="email"
                            />
                        </div>

                        <button type="submit" class="btn-phoenix w-full py-[14px]">
                            @lang('phonix::app.auth.forgot.submit')
                        </button>
                    </form>
                </div>

                {{-- Success State --}}
                <div x-show="sent" x-cloak x-transition>
                    <div class="flex justify-center mb-[20px]">
                        <div class="w-[56px] h-[56px] rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-[28px] h-[28px] text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </div>
                    </div>

                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 text-center mb-[8px]">
                        @lang('phonix::app.auth.forgot.check_email')
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 text-center mb-[24px]">
                        @lang('phonix::app.auth.forgot.check_email_message')
                    </p>

                    <button
                        @click="sent = false"
                        class="btn-phoenix-outline w-full"
                    >
                        @lang('phonix::app.auth.forgot.submit')
                    </button>
                </div>

                {{-- Back to Login --}}
                <div class="mt-[24px] text-center">
                    <a
                        href="{{ route('phonix.auth.login') }}"
                        class="inline-flex items-center gap-[6px] text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors"
                    >
                        <svg class="w-[16px] h-[16px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        @lang('phonix::app.auth.forgot.back_to_login')
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-phonix::layouts.index>
