@props([
    'title'     => null,
    'hasHeader' => true,
    'hasFooter' => true,
])

<!DOCTYPE html>

<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
    x-data="{ darkMode: localStorage.getItem('phonix-dark') === 'true' }"
    x-init="$watch('darkMode', val => { localStorage.setItem('phonix-dark', val); })"
    :class="{ 'dark': darkMode }"
>
    <head>
        <meta charset="UTF-8">
        <meta
            http-equiv="X-UA-Compatible"
            content="IE=edge"
        >
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"
        >
        <meta
            name="csrf-token"
            content="{{ csrf_token() }}"
        >
        <meta
            name="base-url"
            content="{{ url()->to('/') }}"
        >
        <meta
            name="currency"
            content="{{ core()->getCurrentCurrency()->toJson() }}"
        >

        <title>{{ $title ?? config('app.name', 'Phonix - Rise With Technology') }}</title>

        @stack('meta')

        <link
            rel="icon"
            sizes="16x16"
            href="{{ core()->getCurrentChannel()->favicon_url ?? bagisto_asset('images/favicon.ico') }}"
        />

        {{-- Fonts --}}
        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
            crossorigin
        />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        />
        <link
            rel="preload"
            as="style"
            href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        />
        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        />

        {{-- Vite Assets --}}
        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

        @stack('styles')

        <style>
            {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        </style>

        <style>
            @keyframes phonix-splash-hide {
                0%   { opacity: 1; }
                80%  { opacity: 1; }
                100% { opacity: 0; }
            }
            #phonix-splash {
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100%;
                z-index: 999999;
                background: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: phonix-splash-hide 3.5s ease forwards;
            }
        </style>
    </head>

    <body class="font-inter antialiased bg-white text-slate-800 dark:bg-dark-bg dark:text-slate-200 transition-colors duration-300">

        {{-- Splash Screen --}}
        <div id="phonix-splash">
            <img src="{{ asset('phonix-logo.png') }}" alt="Phonix" style="width:200px;height:auto;">
        </div>

        <script>
            document.getElementById('phonix-splash').addEventListener('animationend', function () {
                this.style.display = 'none';
            });
        </script>

        {{-- Skip to content --}}
        <a
            href="#main"
            class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:inset-x-2 focus:z-[100] focus:p-3 focus:bg-phoenix-500 focus:text-white focus:rounded-md focus:text-center"
        >
            @lang('phonix::app.general.skip_to_content', [], 'Skip to main content')
        </a>

        <div id="app" class="overflow-x-hidden">
            {{-- Page Header (re-renders each visit so cart count stays fresh) --}}
            @if ($hasHeader)
                <x-phonix::layouts.header />
            @endif

            {{-- Main Content --}}
            <main
                id="main"
                class="min-h-screen bg-white dark:bg-dark-bg transition-colors duration-300"
            >
                {{ $slot }}
            </main>

            {{-- Page Footer --}}
            @if ($hasFooter)
                <div id="phonix-footer" data-turbo-permanent>
                    <x-phonix::layouts.footer />
                </div>
            @endif

            {{-- Compare Bar (global, persisted in localStorage) --}}
            <div id="phonix-compare-bar" data-turbo-permanent>
                <x-phonix::compare-bar />
            </div>

            {{-- Toast host for cart / wishlist feedback. JS creates it on demand
                 but we pre-render so it also survives Turbo navigation. --}}
            <div
                id="phonix-toasts"
                data-turbo-permanent
                class="fixed top-[84px] end-[16px] z-[100] flex flex-col gap-[8px] pointer-events-none"
            ></div>
        </div>

        @stack('scripts')

        <script>
            /**
             * Mount the Vue app after all components are registered.
             */
            window.addEventListener("load", function (event) {
                if (typeof app !== 'undefined') {
                    app.mount("#app");
                }
            });
        </script>

        <script type="text/javascript">
            {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
        </script>
    </body>
</html>
