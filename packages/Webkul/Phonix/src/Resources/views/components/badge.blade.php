@props([
    'type' => 'new',
])

@php
    $typeClass = match ($type) {
        'sale' => 'badge-sale',
        'new'  => 'badge-new',
        'hot'  => 'badge-hot',
        default => 'badge-new',
    };
@endphp

<span {{ $attributes->merge(['class' => $typeClass]) }}>
    {{ $slot }}
</span>
