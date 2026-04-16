@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
])

@php
    $variantClasses = match ($variant) {
        'primary' => 'btn-phoenix',
        'outline' => 'btn-phoenix-outline',
        'ghost'   => 'btn-phoenix-ghost',
        default   => 'btn-phoenix',
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-[16px] py-[8px] text-xs',
        'md' => 'px-[24px] py-[12px] text-sm',
        'lg' => 'px-[32px] py-[16px] text-base',
        default => 'px-[24px] py-[12px] text-sm',
    };

    $classes = $variantClasses . ' ' . $sizeClasses;
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($disabled) aria-disabled="true" tabindex="-1" @endif
    >
        @if ($loading)
            <svg
                class="animate-spin w-[16px] h-[16px]"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($disabled || $loading) disabled @endif
    >
        @if ($loading)
            <svg
                class="animate-spin w-[16px] h-[16px]"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif
