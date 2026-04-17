@props([
    'variant' => 'default',
])

@php
    $variantClass = match ($variant) {
        'glass'   => 'card-glass',
        'default' => 'card-phoenix',
        default   => 'card-phoenix',
    };
@endphp

<div {{ $attributes->merge(['class' => $variantClass . ' p-[24px]']) }}>
    {{ $slot }}
</div>
