{{-- Profile Settings --}}
@php
    $customer = auth('customer')->user();
@endphp

<x-phonix::account.layout
    :title="__('phonix::app.account.profile.settings')"
    :breadcrumbs="[['label' => __('phonix::app.account.profile.settings')]]"
>
    <div
        class="space-y-[24px]"
        x-data="{
            showCurrentPassword: false,
            showNewPassword: false,
            showConfirmPassword: false,
        }"
    >
        {{-- Page Title --}}
        <h1 class="text-fluid-xl font-bold text-slate-800 dark:text-slate-100" data-gsap="fade-up">
            @lang('phonix::app.account.profile.settings')
        </h1>

        {{-- Personal Information --}}
        <div class="card-phoenix p-[24px]" data-gsap="fade-up">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-[20px]">
                @lang('phonix::app.account.profile.personal_info')
            </h2>

            {{-- Success Message --}}
            @if(session()->has('success'))
                <div
                    class="mb-[16px] p-[12px] rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-300"
                    role="alert"
                >
                    @lang('phonix::app.messages.success.profile_updated')
                </div>
            @endif

            @if($errors->has('email'))
                <div
                    class="mb-[16px] p-[12px] rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-300"
                    role="alert"
                >
                    {{ $errors->first('email') }}
                </div>
            @endif

            <form method="POST" action="{{ route('phonix.account.profile.update') }}" data-turbo="false" class="space-y-[16px]">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-[16px]">
                    <div>
                        <label for="profile-first-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                            @lang('phonix::app.account.profile.first_name') <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="profile-first-name"
                            name="first_name"
                            value="{{ old('first_name', $customer->first_name) }}"
                            class="input-phoenix"
                            required
                            aria-required="true"
                        />
                    </div>
                    <div>
                        <label for="profile-last-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                            @lang('phonix::app.account.profile.last_name') <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="profile-last-name"
                            name="last_name"
                            value="{{ old('last_name', $customer->last_name) }}"
                            class="input-phoenix"
                            required
                            aria-required="true"
                        />
                    </div>
                </div>

                <div>
                    <label for="profile-email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                        @lang('phonix::app.account.profile.email') <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="profile-email"
                        name="email"
                        value="{{ old('email', $customer->email) }}"
                        class="input-phoenix"
                        required
                        aria-required="true"
                    />
                </div>

                <div>
                    <label for="profile-phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                        @lang('phonix::app.account.profile.phone')
                    </label>
                    <input
                        type="tel"
                        id="profile-phone"
                        name="phone"
                        value="{{ old('phone', $customer->phone) }}"
                        class="input-phoenix"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-[16px]">
                    <div>
                        <label for="profile-gender" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                            @lang('phonix::app.account.profile.gender')
                        </label>
                        <select id="profile-gender" name="gender" class="input-phoenix">
                            <option value="">--</option>
                            <option value="male" {{ old('gender', $customer->gender) === 'male' ? 'selected' : '' }}>@lang('phonix::app.account.profile.gender_male')</option>
                            <option value="female" {{ old('gender', $customer->gender) === 'female' ? 'selected' : '' }}>@lang('phonix::app.account.profile.gender_female')</option>
                            <option value="other" {{ old('gender', $customer->gender) === 'other' ? 'selected' : '' }}>@lang('phonix::app.account.profile.gender_other')</option>
                        </select>
                    </div>
                    <div>
                        <label for="profile-dob" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                            @lang('phonix::app.account.profile.dob')
                        </label>
                        <input
                            type="date"
                            id="profile-dob"
                            name="date_of_birth"
                            value="{{ old('date_of_birth', $customer->date_of_birth ? \Carbon\Carbon::parse($customer->date_of_birth)->format('Y-m-d') : '') }}"
                            class="input-phoenix"
                        />
                    </div>
                </div>

                <div class="pt-[8px]">
                    <button type="submit" class="btn-phoenix">
                        @lang('phonix::app.account.profile.update')
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="card-phoenix p-[24px]" data-gsap="fade-up">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-[20px]">
                @lang('phonix::app.account.profile.change_password')
            </h2>

            {{-- Success Message --}}
            @if(session()->has('password_success'))
                <div
                    class="mb-[16px] p-[12px] rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-300"
                    role="alert"
                >
                    @lang('phonix::app.messages.success.password_changed')
                </div>
            @endif

            @if($errors->has('current_password'))
                <div
                    class="mb-[16px] p-[12px] rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-300"
                    role="alert"
                >
                    {{ $errors->first('current_password') }}
                </div>
            @endif

            <form method="POST" action="{{ route('phonix.account.password.update') }}" data-turbo="false" class="space-y-[16px]">
                @csrf
                {{-- Current Password --}}
                <div>
                    <label for="current-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                        @lang('phonix::app.account.profile.current_password') <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            :type="showCurrentPassword ? 'text' : 'password'"
                            id="current-password"
                            name="current_password"
                            class="input-phoenix pe-[44px]"
                            required
                            aria-required="true"
                        />
                        <button
                            type="button"
                            @click="showCurrentPassword = !showCurrentPassword"
                            class="absolute inset-y-0 end-0 flex items-center pe-[12px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                            :aria-label="showCurrentPassword ? 'Hide password' : 'Show password'"
                        >
                            <svg x-show="!showCurrentPassword" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="showCurrentPassword" x-cloak class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- New Password --}}
                <div>
                    <label for="new-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                        @lang('phonix::app.account.profile.new_password') <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            :type="showNewPassword ? 'text' : 'password'"
                            id="new-password"
                            name="new_password"
                            class="input-phoenix pe-[44px]"
                            required
                            aria-required="true"
                            minlength="8"
                        />
                        <button
                            type="button"
                            @click="showNewPassword = !showNewPassword"
                            class="absolute inset-y-0 end-0 flex items-center pe-[12px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                            :aria-label="showNewPassword ? 'Hide password' : 'Show password'"
                        >
                            <svg x-show="!showNewPassword" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="showNewPassword" x-cloak class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="confirm-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                        @lang('phonix::app.account.profile.confirm_password') <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            :type="showConfirmPassword ? 'text' : 'password'"
                            id="confirm-password"
                            name="new_password_confirmation"
                            class="input-phoenix pe-[44px]"
                            required
                            aria-required="true"
                            minlength="8"
                        />
                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 end-0 flex items-center pe-[12px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                            :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                        >
                            <svg x-show="!showConfirmPassword" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="showConfirmPassword" x-cloak class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pt-[8px]">
                    <button type="submit" class="btn-phoenix-outline">
                        @lang('phonix::app.account.profile.save_password')
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-phonix::account.layout>
