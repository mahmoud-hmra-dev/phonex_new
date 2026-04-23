{{-- Register Page --}}
<x-phonix::layouts.index :hasHeader="false" :hasFooter="false">
    <div class="min-h-screen flex items-center justify-center p-[16px] bg-gradient-to-br from-slate-50 via-phoenix-50/30 to-slate-50 dark:from-dark-bg dark:via-phoenix-950/20 dark:to-dark-bg">
        {{-- Background Pattern --}}
        <div class="fixed inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-0 start-0 w-[400px] h-[400px] bg-phoenix-400/10 dark:bg-phoenix-400/5 rounded-full blur-3xl -translate-y-1/2 -translate-x-1/2"></div>
            <div class="absolute bottom-0 end-0 w-[300px] h-[300px] bg-phoenix-600/10 dark:bg-phoenix-600/5 rounded-full blur-3xl translate-y-1/2 translate-x-1/2"></div>
        </div>

        <div class="w-full max-w-[420px] relative z-10" data-gsap="fade-up">
            {{-- Logo --}}
            <div class="text-center mb-[32px]">
                <a href="{{ route('phonix.home') }}" class="inline-block" aria-label="@lang('phonix::app.theme.name')">
                    <span class="text-fluid-2xl font-bold text-gradient-phoenix">Phonix</span>
                </a>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-[8px]">
                    @lang('phonix::app.theme.title')
                </p>
            </div>

            {{-- Register Card --}}
            <div class="card-phoenix p-[32px]">
                <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 text-center mb-[24px]">
                    @lang('phonix::app.auth.register.title')
                </h1>

                @if ($errors->any())
                    <div class="mb-[16px] p-[12px] rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('phonix.auth.register.store') }}"
                    data-turbo="false"
                    x-data="{
                        first_name: '',
                        last_name: '',
                        email: '',
                        password: '',
                        confirmPassword: '',
                        terms: false,
                        showPass: false,
                        showConfirmPass: false,
                        errors: {},
                        validate() {
                            this.errors = {};
                            if (!this.first_name.trim()) this.errors.first_name = true;
                            if (!this.last_name.trim()) this.errors.last_name = true;
                            if (!this.email.trim()) this.errors.email = true;
                            if (this.password.length < 8) this.errors.password = true;
                            if (this.password !== this.confirmPassword) this.errors.confirmPassword = true;
                            return Object.keys(this.errors).length === 0;
                        }
                    }"
                    @submit.prevent="if (validate()) $el.submit()"
                    class="space-y-[16px]"
                    novalidate
                >
                    @csrf

                    {{-- First Name + Last Name --}}
                    <div class="grid grid-cols-2 gap-[12px]">
                        <div>
                            <label for="register-first-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                @lang('phonix::app.auth.register.first_name')
                            </label>
                            <input
                                type="text"
                                id="register-first-name"
                                name="first_name"
                                x-model="first_name"
                                class="input-phoenix"
                                :class="{ 'border-red-500 dark:border-red-400': errors.first_name }"
                                required
                                aria-required="true"
                                autocomplete="given-name"
                            />
                            <p x-show="errors.first_name" x-cloak class="text-xs text-red-500 mt-[4px]" role="alert">
                                @lang('phonix::app.messages.error.validation_failed')
                            </p>
                        </div>
                        <div>
                            <label for="register-last-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                @lang('phonix::app.auth.register.last_name')
                            </label>
                            <input
                                type="text"
                                id="register-last-name"
                                name="last_name"
                                x-model="last_name"
                                class="input-phoenix"
                                :class="{ 'border-red-500 dark:border-red-400': errors.last_name }"
                                required
                                aria-required="true"
                                autocomplete="family-name"
                            />
                            <p x-show="errors.last_name" x-cloak class="text-xs text-red-500 mt-[4px]" role="alert">
                                @lang('phonix::app.messages.error.validation_failed')
                            </p>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="register-email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                            @lang('phonix::app.auth.register.email')
                        </label>
                        <input
                            type="email"
                            id="register-email"
                            name="email"
                            x-model="email"
                            class="input-phoenix"
                            :class="{ 'border-red-500 dark:border-red-400': errors.email }"
                            placeholder="you@example.com"
                            required
                            aria-required="true"
                            autocomplete="email"
                        />
                        <p x-show="errors.email" x-cloak class="text-xs text-red-500 mt-[4px]" role="alert">
                            @lang('phonix::app.messages.error.validation_failed')
                        </p>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="register-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                            @lang('phonix::app.auth.register.password')
                        </label>
                        <div class="relative">
                            <input
                                :type="showPass ? 'text' : 'password'"
                                id="register-password"
                                name="password"
                                x-model="password"
                                class="input-phoenix pe-[44px]"
                                :class="{ 'border-red-500 dark:border-red-400': errors.password }"
                                required
                                aria-required="true"
                                minlength="8"
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                @click="showPass = !showPass"
                                class="absolute inset-y-0 end-0 flex items-center pe-[12px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                :aria-label="showPass ? 'Hide password' : 'Show password'"
                            >
                                <svg x-show="!showPass" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="showPass" x-cloak class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <p x-show="errors.password" x-cloak class="text-xs text-red-500 mt-[4px]" role="alert">
                            Password must be at least 8 characters.
                        </p>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="register-confirm-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                            @lang('phonix::app.auth.register.confirm_password')
                        </label>
                        <div class="relative">
                            <input
                                :type="showConfirmPass ? 'text' : 'password'"
                                id="register-confirm-password"
                                name="password_confirmation"
                                x-model="confirmPassword"
                                class="input-phoenix pe-[44px]"
                                :class="{ 'border-red-500 dark:border-red-400': errors.confirmPassword }"
                                required
                                aria-required="true"
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                @click="showConfirmPass = !showConfirmPass"
                                class="absolute inset-y-0 end-0 flex items-center pe-[12px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                :aria-label="showConfirmPass ? 'Hide password' : 'Show password'"
                            >
                                <svg x-show="!showConfirmPass" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="showConfirmPass" x-cloak class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <p x-show="errors.confirmPassword" x-cloak class="text-xs text-red-500 mt-[4px]" role="alert">
                            Passwords do not match.
                        </p>
                    </div>

                    {{-- Terms --}}
                    <div>
                        <label class="flex items-start gap-[8px] cursor-pointer">
                            <input
                                type="checkbox"
                                x-model="terms"
                                class="w-[16px] h-[16px] mt-[2px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400"
                                :class="{ 'border-red-500': errors.terms }"
                            />
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                @lang('phonix::app.auth.register.terms_agree')
                            </span>
                        </label>
                        <p x-show="errors.terms" x-cloak class="text-xs text-red-500 mt-[4px] ms-[24px]" role="alert">
                            @lang('phonix::app.messages.error.validation_failed')
                        </p>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn-phoenix w-full py-[14px]">
                        @lang('phonix::app.auth.register.submit')
                    </button>
                </form>

                {{-- Divider --}}
                <div class="flex items-center gap-[16px] my-[24px]">
                    <div class="flex-1 h-px bg-slate-200 dark:bg-dark-border"></div>
                    <span class="text-xs text-slate-400 dark:text-slate-500 uppercase font-medium">
                        @lang('phonix::app.auth.social.or')
                    </span>
                    <div class="flex-1 h-px bg-slate-200 dark:bg-dark-border"></div>
                </div>

                {{-- Social Register --}}
                <div class="space-y-[12px]">
                    <button
                        type="button"
                        class="flex items-center justify-center gap-[10px] w-full py-[12px] px-[16px] rounded-md border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-surface text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-dark-card transition-colors"
                        aria-label="@lang('phonix::app.auth.social.google')"
                    >
                        <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        @lang('phonix::app.auth.social.google')
                    </button>

                    <button
                        type="button"
                        class="flex items-center justify-center gap-[10px] w-full py-[12px] px-[16px] rounded-md border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-surface text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-dark-card transition-colors"
                        aria-label="@lang('phonix::app.auth.social.apple')"
                    >
                        <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.4C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                        </svg>
                        @lang('phonix::app.auth.social.apple')
                    </button>
                </div>
            </div>

            {{-- Login Link --}}
            <p class="text-center text-sm text-slate-500 dark:text-slate-400 mt-[24px]">
                @lang('phonix::app.auth.register.has_account')
                <a
                    href="{{ route('phonix.auth.login') }}"
                    class="font-semibold text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors"
                >
                    @lang('phonix::app.auth.register.login_link')
                </a>
            </p>
        </div>
    </div>
</x-phonix::layouts.index>
