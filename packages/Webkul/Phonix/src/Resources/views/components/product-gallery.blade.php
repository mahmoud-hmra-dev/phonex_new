@props([
    'images' => [],
    'badge' => null,
    'productName' => '',
])

<div
    x-data="{
        activeIndex: 0,
        lightboxOpen: false,
        zoomActive: false,
        zoomX: 50,
        zoomY: 50,
        images: {{ json_encode($images) }},
        setActive(index) {
            this.activeIndex = index;
        },
        handleMouseMove(e) {
            if (!this.zoomActive) return;
            const rect = e.currentTarget.getBoundingClientRect();
            this.zoomX = ((e.clientX - rect.left) / rect.width) * 100;
            this.zoomY = ((e.clientY - rect.top) / rect.height) * 100;
        },
        next() {
            this.activeIndex = (this.activeIndex + 1) % this.images.length;
        },
        prev() {
            this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length;
        }
    }"
    {{ $attributes->merge(['class' => 'space-y-[12px]']) }}
>
    {{-- Main Image --}}
    <div
        class="relative aspect-square bg-slate-50 dark:bg-dark-surface rounded-lg overflow-hidden cursor-zoom-in group"
        @mouseenter="zoomActive = true"
        @mouseleave="zoomActive = false"
        @mousemove="handleMouseMove($event)"
        @click="lightboxOpen = true"
    >
        {{-- Badge --}}
        @if ($badge)
            <div class="absolute top-[12px] start-[12px] z-10">
                <x-phonix::badge :type="$badge">
                    @lang('phonix::app.product.' . $badge)
                </x-phonix::badge>
            </div>
        @endif

        {{-- Image display --}}
        <template x-for="(img, index) in images" :key="index">
            <div
                x-show="activeIndex === index"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="absolute inset-0"
            >
                <div
                    class="w-full h-full bg-cover bg-no-repeat transition-transform duration-100"
                    :style="{
                        backgroundImage: img.url ? 'url(' + img.url + ')' : 'none',
                        backgroundPosition: zoomActive ? zoomX + '% ' + zoomY + '%' : 'center',
                        transform: zoomActive ? 'scale(1.8)' : 'scale(1)',
                        backgroundColor: img.color || '#E8EDED'
                    }"
                    :aria-label="'{{ $productName }} - Image ' + (index + 1)"
                >
                    <template x-if="!img.url">
                        <div class="w-full h-full flex items-center justify-center" :style="{ background: img.color || 'linear-gradient(135deg, #1A8A96 0%, #4FC3D0 100%)' }">
                            <svg class="w-[64px] h-[64px] text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                            </svg>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- Nav Arrows --}}
        <button
            @click.stop="prev()"
            class="absolute start-[8px] top-1/2 -translate-y-1/2 z-10 w-[36px] h-[36px] flex items-center justify-center bg-white/80 dark:bg-dark-card/80 rounded-full shadow-md text-slate-600 dark:text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white dark:hover:bg-dark-card"
            aria-label="@lang('phonix::app.general.previous')"
        >
            <svg class="w-[16px] h-[16px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>
        <button
            @click.stop="next()"
            class="absolute end-[8px] top-1/2 -translate-y-1/2 z-10 w-[36px] h-[36px] flex items-center justify-center bg-white/80 dark:bg-dark-card/80 rounded-full shadow-md text-slate-600 dark:text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white dark:hover:bg-dark-card"
            aria-label="@lang('phonix::app.general.next')"
        >
            <svg class="w-[16px] h-[16px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>

    {{-- Thumbnails --}}
    <div class="flex gap-[8px] overflow-x-auto scrollbar-thin pb-[4px]">
        <template x-for="(img, index) in images" :key="'thumb-' + index">
            <button
                @click="setActive(index)"
                :class="activeIndex === index ? 'ring-2 ring-phoenix-500 ring-offset-2 dark:ring-offset-dark-bg' : 'ring-1 ring-slate-200 dark:ring-dark-border hover:ring-phoenix-300'"
                class="shrink-0 w-[64px] h-[64px] rounded overflow-hidden transition-all duration-200"
                :aria-label="'Thumbnail ' + (index + 1)"
                :aria-pressed="(activeIndex === index).toString()"
            >
                <div
                    class="w-full h-full bg-cover bg-center"
                    :style="{
                        backgroundImage: img.url ? 'url(' + img.url + ')' : 'none',
                        backgroundColor: img.color || '#E8EDED'
                    }"
                >
                    <template x-if="!img.url">
                        <div class="w-full h-full flex items-center justify-center" :style="{ background: img.color || 'linear-gradient(135deg, #1A8A96 0%, #4FC3D0 100%)' }">
                            <svg class="w-[20px] h-[20px] text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                            </svg>
                        </div>
                    </template>
                </div>
            </button>
        </template>
    </div>

    {{-- Lightbox --}}
    <div
        x-show="lightboxOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/90 p-[16px]"
        @click="lightboxOpen = false"
        @keydown.escape.window="lightboxOpen = false"
        @keydown.left.window="prev()"
        @keydown.right.window="next()"
        x-cloak
        role="dialog"
        aria-modal="true"
        aria-label="Image lightbox"
    >
        {{-- Close --}}
        <button
            @click="lightboxOpen = false"
            class="absolute top-[16px] end-[16px] z-10 p-[8px] text-white/70 hover:text-white transition-colors"
            aria-label="@lang('phonix::app.general.close')"
        >
            <svg class="w-[28px] h-[28px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Image --}}
        <div class="max-w-[900px] max-h-[80vh] w-full" @click.stop>
            <template x-for="(img, index) in images" :key="'lb-' + index">
                <div
                    x-show="activeIndex === index"
                    x-transition
                    class="w-full aspect-square rounded-lg overflow-hidden"
                >
                    <div
                        class="w-full h-full bg-cover bg-center"
                        :style="{
                            backgroundImage: img.url ? 'url(' + img.url + ')' : 'none',
                            backgroundColor: img.color || '#E8EDED'
                        }"
                    >
                        <template x-if="!img.url">
                            <div class="w-full h-full flex items-center justify-center" :style="{ background: img.color || 'linear-gradient(135deg, #1A8A96 0%, #4FC3D0 100%)' }">
                                <svg class="w-[80px] h-[80px] text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                </svg>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Lightbox Nav --}}
        <button
            @click.stop="prev()"
            class="absolute start-[16px] top-1/2 -translate-y-1/2 w-[44px] h-[44px] flex items-center justify-center bg-white/10 hover:bg-white/20 rounded-full text-white transition-colors"
            aria-label="@lang('phonix::app.general.previous')"
        >
            <svg class="w-[20px] h-[20px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>
        <button
            @click.stop="next()"
            class="absolute end-[16px] top-1/2 -translate-y-1/2 w-[44px] h-[44px] flex items-center justify-center bg-white/10 hover:bg-white/20 rounded-full text-white transition-colors"
            aria-label="@lang('phonix::app.general.next')"
        >
            <svg class="w-[20px] h-[20px] rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>
</div>
