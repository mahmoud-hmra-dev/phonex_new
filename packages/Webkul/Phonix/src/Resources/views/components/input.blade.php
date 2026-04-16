@props([
    'type' => 'text',
    'label' => null,
    'error' => null,
    'name' => null,
])

<div class="w-full">
    @if ($label)
        <label
            @if ($name) for="{{ $name }}" @endif
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]"
        >
            {{ $label }}
        </label>
    @endif

    <input
        type="{{ $type }}"
        @if ($name) id="{{ $name }}" name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'input-phoenix' . ($error ? ' border-coral focus:border-coral focus:ring-coral/20' : ''),
        ]) }}
        @if ($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
    />

    @if ($error)
        <p
            @if ($name) id="{{ $name }}-error" @endif
            class="mt-[6px] text-xs text-coral"
            role="alert"
        >
            {{ $error }}
        </p>
    @endif
</div>
