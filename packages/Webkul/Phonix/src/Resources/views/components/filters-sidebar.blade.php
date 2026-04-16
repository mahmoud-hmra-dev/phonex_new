@props([
    'brands' => ['Apple', 'Samsung', 'Xiaomi', 'Dell', 'HP', 'Lenovo', 'Asus', 'Sony'],
    'colors' => [
        ['name' => 'Black', 'hex' => '#000000'],
        ['name' => 'White', 'hex' => '#FFFFFF'],
        ['name' => 'Silver', 'hex' => '#C0C0C0'],
        ['name' => 'Blue', 'hex' => '#3B82F6'],
        ['name' => 'Gold', 'hex' => '#EAB308'],
        ['name' => 'Rose Gold', 'hex' => '#E8A0BF'],
    ],
    'ramOptions' => ['4GB', '6GB', '8GB', '12GB', '16GB'],
    'storageOptions' => ['64GB', '128GB', '256GB', '512GB', '1TB'],
])

{{-- Mobile Filter Toggle --}}
<button
    @click="filtersOpen = true"
    class="lg:hidden flex items-center gap-[8px] btn-phoenix-outline px-[16px] py-[8px] text-xs"
    aria-label="@lang('phonix::app.listing.filters.title')"
>
    <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
    </svg>
    @lang('phonix::app.listing.filters.title')
</button>

{{-- Sidebar / Drawer --}}
<div
    x-data="{
        priceMin: 0,
        priceMax: 10000,
        brandSearch: '',
        sections: {
            price: true,
            brand: true,
            color: true,
            rating: false,
            availability: false,
            ram: false,
            storage: false,
        },
        selectedBrands: [],
        selectedColors: [],
        selectedRating: 0,
        selectedAvailability: [],
        selectedRam: [],
        selectedStorage: [],
        clearAll() {
            this.priceMin = 0;
            this.priceMax = 10000;
            this.selectedBrands = [];
            this.selectedColors = [];
            this.selectedRating = 0;
            this.selectedAvailability = [];
            this.selectedRam = [];
            this.selectedStorage = [];
            this.brandSearch = '';
        }
    }"
    data-gsap="fade-in"
    {{ $attributes }}
>
    {{-- Mobile Drawer Backdrop --}}
    <div
        x-show="filtersOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[50] bg-black/50 backdrop-blur-sm lg:hidden"
        @click="filtersOpen = false"
        x-cloak
    ></div>

    {{-- Sidebar Panel --}}
    <aside
        :class="filtersOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed top-0 start-0 z-[51] h-full w-[300px] overflow-y-auto bg-white dark:bg-dark-surface border-e border-slate-200 dark:border-dark-border transition-transform duration-300 ease-phoenix lg:static lg:z-auto lg:h-auto lg:w-full lg:border-e-0 lg:bg-transparent lg:dark:bg-transparent scrollbar-thin"
    >
        {{-- Drawer Header (mobile only) --}}
        <div class="flex items-center justify-between p-[16px] border-b border-slate-200 dark:border-dark-border lg:hidden">
            <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                @lang('phonix::app.listing.filters.title')
            </h3>
            <button
                @click="filtersOpen = false"
                class="p-[8px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                aria-label="@lang('phonix::app.general.close')"
            >
                <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-[16px] lg:p-0 space-y-[16px]">
            {{-- Filter Header (desktop) --}}
            <div class="hidden lg:flex items-center justify-between mb-[8px]">
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                    @lang('phonix::app.listing.filters.title')
                </h3>
                <button
                    @click="clearAll()"
                    class="text-xs font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors"
                >
                    @lang('phonix::app.listing.filters.clear_all')
                </button>
            </div>

            {{-- Price Range --}}
            <div class="card-phoenix p-[16px]">
                <button
                    @click="sections.price = !sections.price"
                    class="flex items-center justify-between w-full text-sm font-semibold text-slate-800 dark:text-slate-200"
                    aria-expanded="true"
                >
                    @lang('phonix::app.listing.filters.price_range')
                    <svg
                        :class="sections.price ? 'rotate-180' : ''"
                        class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="sections.price" x-collapse class="mt-[12px]">
                    <div class="flex items-center gap-[8px] mb-[12px]">
                        <input
                            type="number"
                            x-model="priceMin"
                            min="0"
                            class="input-phoenix text-xs py-[8px] px-[12px] w-full"
                            placeholder="Min"
                            aria-label="Minimum price"
                        />
                        <span class="text-slate-400 text-xs">-</span>
                        <input
                            type="number"
                            x-model="priceMax"
                            min="0"
                            class="input-phoenix text-xs py-[8px] px-[12px] w-full"
                            placeholder="Max"
                            aria-label="Maximum price"
                        />
                    </div>
                    <input
                        type="range"
                        min="0"
                        max="20000"
                        x-model="priceMax"
                        class="w-full h-[4px] rounded-full appearance-none cursor-pointer accent-phoenix-500"
                        aria-label="Price range slider"
                    />
                    <div class="flex justify-between text-xs text-slate-400 dark:text-slate-500 mt-[4px]">
                        <span>$0</span>
                        <span>$20,000</span>
                    </div>
                </div>
            </div>

            {{-- Brand --}}
            <div class="card-phoenix p-[16px]">
                <button
                    @click="sections.brand = !sections.brand"
                    class="flex items-center justify-between w-full text-sm font-semibold text-slate-800 dark:text-slate-200"
                    aria-expanded="true"
                >
                    @lang('phonix::app.listing.filters.brand')
                    <svg
                        :class="sections.brand ? 'rotate-180' : ''"
                        class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="sections.brand" x-collapse class="mt-[12px]">
                    <input
                        type="text"
                        x-model="brandSearch"
                        class="input-phoenix text-xs py-[8px] px-[12px] mb-[8px]"
                        placeholder="@lang('phonix::app.general.search')..."
                        aria-label="Search brands"
                    />
                    <div class="space-y-[6px] max-h-[160px] overflow-y-auto scrollbar-thin">
                        @foreach ($brands as $brand)
                            <label
                                x-show="!brandSearch || '{{ strtolower($brand) }}'.includes(brandSearch.toLowerCase())"
                                class="flex items-center gap-[8px] cursor-pointer group"
                            >
                                <input
                                    type="checkbox"
                                    value="{{ $brand }}"
                                    x-model="selectedBrands"
                                    class="w-[16px] h-[16px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400"
                                />
                                <span class="text-xs text-slate-600 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors">
                                    {{ $brand }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Color --}}
            <div class="card-phoenix p-[16px]">
                <button
                    @click="sections.color = !sections.color"
                    class="flex items-center justify-between w-full text-sm font-semibold text-slate-800 dark:text-slate-200"
                    aria-expanded="true"
                >
                    @lang('phonix::app.listing.filters.color')
                    <svg
                        :class="sections.color ? 'rotate-180' : ''"
                        class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="sections.color" x-collapse class="mt-[12px]">
                    <div class="flex flex-wrap gap-[8px]">
                        @foreach ($colors as $color)
                            <button
                                @click="selectedColors.includes('{{ $color['name'] }}') ? selectedColors = selectedColors.filter(c => c !== '{{ $color['name'] }}') : selectedColors.push('{{ $color['name'] }}')"
                                :class="selectedColors.includes('{{ $color['name'] }}') ? 'ring-2 ring-phoenix-500 ring-offset-2 dark:ring-offset-dark-surface' : 'ring-1 ring-slate-200 dark:ring-dark-border'"
                                class="w-[28px] h-[28px] rounded-full transition-all duration-200 hover:scale-110"
                                style="background-color: {{ $color['hex'] }}"
                                :aria-pressed="selectedColors.includes('{{ $color['name'] }}').toString()"
                                aria-label="{{ $color['name'] }}"
                                title="{{ $color['name'] }}"
                            ></button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Rating --}}
            <div class="card-phoenix p-[16px]">
                <button
                    @click="sections.rating = !sections.rating"
                    class="flex items-center justify-between w-full text-sm font-semibold text-slate-800 dark:text-slate-200"
                    aria-expanded="false"
                >
                    @lang('phonix::app.listing.filters.rating')
                    <svg
                        :class="sections.rating ? 'rotate-180' : ''"
                        class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="sections.rating" x-collapse class="mt-[12px] space-y-[6px]">
                    @for ($stars = 4; $stars >= 1; $stars--)
                        <button
                            @click="selectedRating = selectedRating === {{ $stars }} ? 0 : {{ $stars }}"
                            :class="selectedRating === {{ $stars }} ? 'bg-phoenix-50 dark:bg-phoenix-900/30 border-phoenix-300 dark:border-phoenix-700' : 'border-transparent hover:bg-slate-50 dark:hover:bg-dark-card'"
                            class="flex items-center gap-[6px] w-full px-[8px] py-[6px] rounded border transition-colors"
                            aria-label="{{ $stars }} stars and up"
                        >
                            <div class="flex items-center">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg
                                        class="w-[14px] h-[14px] {{ $i <= $stars ? 'text-gold' : 'text-slate-300 dark:text-slate-600' }}"
                                        fill="currentColor" viewBox="0 0 20 20"
                                    >
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-xs text-slate-500 dark:text-slate-400">& up</span>
                        </button>
                    @endfor
                </div>
            </div>

            {{-- Availability --}}
            <div class="card-phoenix p-[16px]">
                <button
                    @click="sections.availability = !sections.availability"
                    class="flex items-center justify-between w-full text-sm font-semibold text-slate-800 dark:text-slate-200"
                    aria-expanded="false"
                >
                    @lang('phonix::app.listing.filters.availability')
                    <svg
                        :class="sections.availability ? 'rotate-180' : ''"
                        class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="sections.availability" x-collapse class="mt-[12px] space-y-[6px]">
                    <label class="flex items-center gap-[8px] cursor-pointer">
                        <input
                            type="checkbox"
                            value="in_stock"
                            x-model="selectedAvailability"
                            class="w-[16px] h-[16px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400"
                        />
                        <span class="text-xs text-slate-600 dark:text-slate-400">@lang('phonix::app.product.in_stock')</span>
                    </label>
                    <label class="flex items-center gap-[8px] cursor-pointer">
                        <input
                            type="checkbox"
                            value="out_of_stock"
                            x-model="selectedAvailability"
                            class="w-[16px] h-[16px] rounded border-slate-300 dark:border-dark-border text-phoenix-500 focus:ring-phoenix-400"
                        />
                        <span class="text-xs text-slate-600 dark:text-slate-400">@lang('phonix::app.product.out_of_stock')</span>
                    </label>
                </div>
            </div>

            {{-- RAM --}}
            <div class="card-phoenix p-[16px]">
                <button
                    @click="sections.ram = !sections.ram"
                    class="flex items-center justify-between w-full text-sm font-semibold text-slate-800 dark:text-slate-200"
                    aria-expanded="false"
                >
                    @lang('phonix::app.listing.filters.ram')
                    <svg
                        :class="sections.ram ? 'rotate-180' : ''"
                        class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="sections.ram" x-collapse class="mt-[12px]">
                    <div class="flex flex-wrap gap-[6px]">
                        @foreach ($ramOptions as $ram)
                            <button
                                @click="selectedRam.includes('{{ $ram }}') ? selectedRam = selectedRam.filter(r => r !== '{{ $ram }}') : selectedRam.push('{{ $ram }}')"
                                :class="selectedRam.includes('{{ $ram }}') ? 'bg-phoenix-500 text-white border-phoenix-500' : 'bg-white dark:bg-dark-card text-slate-600 dark:text-slate-400 border-slate-200 dark:border-dark-border hover:border-phoenix-400'"
                                class="px-[12px] py-[6px] text-xs font-medium rounded border transition-all duration-200"
                                :aria-pressed="selectedRam.includes('{{ $ram }}').toString()"
                            >
                                {{ $ram }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Storage --}}
            <div class="card-phoenix p-[16px]">
                <button
                    @click="sections.storage = !sections.storage"
                    class="flex items-center justify-between w-full text-sm font-semibold text-slate-800 dark:text-slate-200"
                    aria-expanded="false"
                >
                    @lang('phonix::app.listing.filters.storage')
                    <svg
                        :class="sections.storage ? 'rotate-180' : ''"
                        class="w-[16px] h-[16px] text-slate-400 transition-transform duration-200"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="sections.storage" x-collapse class="mt-[12px]">
                    <div class="flex flex-wrap gap-[6px]">
                        @foreach ($storageOptions as $storage)
                            <button
                                @click="selectedStorage.includes('{{ $storage }}') ? selectedStorage = selectedStorage.filter(s => s !== '{{ $storage }}') : selectedStorage.push('{{ $storage }}')"
                                :class="selectedStorage.includes('{{ $storage }}') ? 'bg-phoenix-500 text-white border-phoenix-500' : 'bg-white dark:bg-dark-card text-slate-600 dark:text-slate-400 border-slate-200 dark:border-dark-border hover:border-phoenix-400'"
                                class="px-[12px] py-[6px] text-xs font-medium rounded border transition-all duration-200"
                                :aria-pressed="selectedStorage.includes('{{ $storage }}').toString()"
                            >
                                {{ $storage }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-[8px] pt-[8px]">
                <button
                    @click="clearAll()"
                    class="flex-1 btn-phoenix-ghost px-[16px] py-[8px] text-xs"
                >
                    @lang('phonix::app.listing.filters.clear_all')
                </button>
                <button
                    @click="filtersOpen = false"
                    class="flex-1 btn-phoenix px-[16px] py-[8px] text-xs"
                >
                    @lang('phonix::app.listing.filters.apply')
                </button>
            </div>
        </div>
    </aside>
</div>
