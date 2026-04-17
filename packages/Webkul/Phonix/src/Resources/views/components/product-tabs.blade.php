@props([
    'description' => '',
    'specifications' => [],
    'reviews' => [],
    'averageRating' => 0,
    'totalReviews' => 0,
    'ratingBreakdown' => [],
])

<div
    x-data="{
        activeTab: 'description',
        reviewForm: false,
        reviewRating: 0,
        reviewText: '',
    }"
    {{ $attributes->merge(['class' => '']) }}
>
    {{-- Tab Buttons --}}
    <div class="border-b border-slate-200 dark:border-dark-border" role="tablist" aria-label="Product information tabs">
        <div class="flex gap-[24px] overflow-x-auto scrollbar-thin">
            <button
                @click="activeTab = 'description'"
                :class="activeTab === 'description' ? 'border-phoenix-500 text-phoenix-600 dark:text-phoenix-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
                class="pb-[12px] px-[4px] text-sm font-semibold border-b-2 transition-colors whitespace-nowrap"
                role="tab"
                :aria-selected="(activeTab === 'description').toString()"
                id="tab-description"
                aria-controls="panel-description"
            >
                @lang('phonix::app.product.description')
            </button>
            <button
                @click="activeTab = 'specifications'"
                :class="activeTab === 'specifications' ? 'border-phoenix-500 text-phoenix-600 dark:text-phoenix-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
                class="pb-[12px] px-[4px] text-sm font-semibold border-b-2 transition-colors whitespace-nowrap"
                role="tab"
                :aria-selected="(activeTab === 'specifications').toString()"
                id="tab-specifications"
                aria-controls="panel-specifications"
            >
                @lang('phonix::app.product.specifications')
            </button>
            <button
                @click="activeTab = 'reviews'"
                :class="activeTab === 'reviews' ? 'border-phoenix-500 text-phoenix-600 dark:text-phoenix-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
                class="pb-[12px] px-[4px] text-sm font-semibold border-b-2 transition-colors whitespace-nowrap"
                role="tab"
                :aria-selected="(activeTab === 'reviews').toString()"
                id="tab-reviews"
                aria-controls="panel-reviews"
            >
                @lang('phonix::app.product.reviews') ({{ $totalReviews }})
            </button>
        </div>
    </div>

    {{-- Description Panel --}}
    <div
        x-show="activeTab === 'description'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-[4px]"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="py-[24px]"
        role="tabpanel"
        id="panel-description"
        aria-labelledby="tab-description"
    >
        <div class="prose prose-sm dark:prose-invert max-w-none text-slate-600 dark:text-slate-400 leading-relaxed">
            {!! $description !!}
        </div>
    </div>

    {{-- Specifications Panel --}}
    <div
        x-show="activeTab === 'specifications'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-[4px]"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="py-[24px]"
        role="tabpanel"
        id="panel-specifications"
        aria-labelledby="tab-specifications"
        x-cloak
    >
        <div class="overflow-hidden rounded-lg border border-slate-200 dark:border-dark-border">
            <table class="w-full text-sm">
                <tbody>
                    @foreach ($specifications as $index => $spec)
                        <tr class="{{ $index % 2 === 0 ? 'bg-slate-50 dark:bg-dark-surface' : 'bg-white dark:bg-dark-card' }}">
                            <td class="px-[16px] py-[12px] font-semibold text-slate-700 dark:text-slate-300 w-[40%]">
                                {{ $spec['label'] }}
                            </td>
                            <td class="px-[16px] py-[12px] text-slate-600 dark:text-slate-400">
                                {{ $spec['value'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Reviews Panel --}}
    <div
        x-show="activeTab === 'reviews'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-[4px]"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="py-[24px]"
        role="tabpanel"
        id="panel-reviews"
        aria-labelledby="tab-reviews"
        x-cloak
    >
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-[32px]">
            {{-- Rating Summary --}}
            <div class="card-phoenix p-[24px] text-center">
                <div class="text-fluid-3xl font-bold text-slate-800 dark:text-white mb-[4px]">
                    {{ $averageRating }}
                </div>
                <div class="flex items-center justify-center gap-[2px] mb-[8px]">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg
                            class="w-[18px] h-[18px] {{ $i <= round($averageRating) ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                            fill="currentColor" viewBox="0 0 20 20"
                        >
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-[16px]">
                    @lang('phonix::app.product.reviews_count', ['count' => $totalReviews])
                </p>

                {{-- Rating Bars --}}
                <div class="space-y-[6px]">
                    @for ($star = 5; $star >= 1; $star--)
                        @php
                            $count = $ratingBreakdown[$star] ?? 0;
                            $pct = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                        @endphp
                        <div class="flex items-center gap-[8px] text-xs">
                            <span class="w-[12px] text-slate-500 dark:text-slate-400">{{ $star }}</span>
                            <svg class="w-[12px] h-[12px] text-gold" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <div class="flex-1 h-[6px] bg-slate-200 dark:bg-dark-border rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-gold rounded-full"
                                    style="width: {{ $pct }}%"
                                ></div>
                            </div>
                            <span class="w-[28px] text-end text-slate-400">{{ $count }}</span>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Review List --}}
            <div class="lg:col-span-2 space-y-[16px]">
                {{-- Write Review Button --}}
                <div class="flex justify-end mb-[8px]">
                    <button
                        @click="reviewForm = !reviewForm"
                        class="btn-phoenix-outline px-[16px] py-[8px] text-xs"
                    >
                        @lang('phonix::app.product.write_review')
                    </button>
                </div>

                {{-- Review Form --}}
                <div
                    x-show="reviewForm"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-[8px]"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="card-phoenix p-[20px] mb-[16px]"
                    x-cloak
                >
                    <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-[12px]">
                        @lang('phonix::app.product.write_review')
                    </h4>

                    {{-- Star Rating Input --}}
                    <div class="flex items-center gap-[4px] mb-[12px]">
                        <span class="text-xs text-slate-500 dark:text-slate-400 me-[8px]">@lang('phonix::app.product.rating'):</span>
                        @for ($i = 1; $i <= 5; $i++)
                            <button
                                @click="reviewRating = {{ $i }}"
                                class="transition-transform hover:scale-110"
                                aria-label="Rate {{ $i }} stars"
                            >
                                <svg
                                    :class="reviewRating >= {{ $i }} ? 'text-gold' : 'text-slate-300 dark:text-slate-600'"
                                    class="w-[24px] h-[24px] transition-colors"
                                    fill="currentColor" viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        @endfor
                    </div>

                    {{-- Review Text --}}
                    <textarea
                        x-model="reviewText"
                        rows="4"
                        class="input-phoenix text-sm mb-[12px]"
                        placeholder="Share your experience with this product..."
                    ></textarea>

                    <div class="flex justify-end gap-[8px]">
                        <button @click="reviewForm = false" class="btn-phoenix-ghost px-[16px] py-[8px] text-xs">
                            @lang('phonix::app.general.cancel')
                        </button>
                        <button class="btn-phoenix px-[16px] py-[8px] text-xs">
                            @lang('phonix::app.general.submit')
                        </button>
                    </div>
                </div>

                {{-- Individual Reviews --}}
                @forelse ($reviews as $review)
                    <div class="card-phoenix p-[20px]">
                        <div class="flex items-start justify-between mb-[8px]">
                            <div>
                                <h5 class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $review['name'] }}
                                </h5>
                                <span class="text-xs text-slate-400 dark:text-slate-500">
                                    {{ $review['date'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-[2px]">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg
                                        class="w-[14px] h-[14px] {{ $i <= $review['rating'] ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                        fill="currentColor" viewBox="0 0 20 20"
                                    >
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-[12px]">
                            {{ $review['text'] }}
                        </p>
                        <button class="flex items-center gap-[4px] text-xs text-slate-400 dark:text-slate-500 hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-colors">
                            <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48a4.53 4.53 0 01-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.228.22.442.399.622a3 3 0 002.121.878h.468a1.5 1.5 0 001.06-.44l.013-.012a1.5 1.5 0 00.44-1.06v-.504M5.904 18.75H5.25A2.25 2.25 0 013 16.5v-6A2.25 2.25 0 015.25 8.25h.654" />
                            </svg>
                            Helpful
                        </button>
                    </div>
                @empty
                    <div class="text-center py-[32px]">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            @lang('phonix::app.product.no_reviews')
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
