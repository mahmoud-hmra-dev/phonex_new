@props([
    'items' => [],
])

<nav
    {{ $attributes->merge(['class' => 'py-[12px]']) }}
    aria-label="@lang('phonix::app.general.breadcrumb', [], 'Breadcrumb')"
>
    <ol class="flex flex-wrap items-center gap-[4px] text-sm">
        @foreach ($items as $index => $item)
            <li class="flex items-center gap-[4px]">
                @if ($index > 0)
                    {{-- Chevron separator --}}
                    <svg
                        class="w-[14px] h-[14px] text-slate-400 dark:text-slate-600 rtl:rotate-180 shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                @endif

                @if (! empty($item['url']) && $index < count($items) - 1)
                    <a
                        href="{{ $item['url'] }}"
                        class="text-slate-500 dark:text-slate-400 hover:text-phoenix-600 dark:hover:text-phoenix-400 transition-colors"
                    >
                        {{ $item['label'] }}
                    </a>
                @else
                    <span
                        class="text-slate-800 dark:text-slate-200 font-medium"
                        aria-current="page"
                    >
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
