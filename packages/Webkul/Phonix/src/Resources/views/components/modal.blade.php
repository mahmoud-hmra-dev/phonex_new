@props([
    'name',
    'maxWidth' => 'lg',
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-lg',
    };
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $name }}.window="open = true"
    x-on:close-modal-{{ $name }}.window="open = false"
    @keydown.escape.window="open = false"
    x-cloak
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm"
        @click="open = false"
        aria-hidden="true"
    ></div>

    {{-- Modal Content --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-[61] flex items-center justify-center p-[16px]"
        role="dialog"
        aria-modal="true"
        :aria-label="'{{ $name }}'"
        x-trap.inert.noscroll="open"
    >
        <div
            class="w-full {{ $maxWidthClass }} bg-white dark:bg-dark-card rounded-lg shadow-modal overflow-hidden"
            @click.stop
        >
            {{-- Close Button --}}
            <div class="flex items-center justify-end p-[12px] pb-0">
                <button
                    @click="open = false"
                    class="p-[8px] text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors rounded-md"
                    aria-label="@lang('phonix::app.general.close')"
                >
                    <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-[24px] pt-[8px]">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
