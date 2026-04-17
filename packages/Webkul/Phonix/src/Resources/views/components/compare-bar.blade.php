{{--
    Compare Bar Component
    ─────────────────────────────────────────────────────────────────────────
    A sticky floating bar fixed at the bottom of the viewport.
    State lives in localStorage under the key "phonix_compare" as a JSON
    array of objects: [{ id, name, imageUrl }, ...].

    The component exposes a global Alpine.js store function `compareBarStore()`
    registered via @pushOnce so it is available to product-card and any other
    component that needs to call `compareBarStore().addItem(...)`.

    @phonix-animation: slide-up entrance on `compareItems.length > 0`
    @phonix-animation: item thumbnails scale-in when pushed to array
--}}

{{-- ── Compare Bar ─────────────────────────────────────────────────────── --}}
<div
    id="phonix-compare-bar"
    x-data="compareBarStore()"
    x-show="compareItems.length > 0"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-full opacity-0"
    class="fixed bottom-0 inset-x-0 z-50 h-[80px] bg-slate-900 dark:bg-slate-950 border-t border-slate-700/60 shadow-2xl"
    role="region"
    aria-label="@lang('phonix::app.misc.compare.bar_label')"
    x-cloak
>
    <div class="container mx-auto h-full flex items-center gap-[12px] lg:gap-[20px] px-[16px] lg:px-[24px]">

        {{-- ── Left: Label ──────────────────────────────────────────── --}}
        <div class="hidden md:flex items-center gap-[10px] shrink-0">
            <div class="flex items-center justify-center w-[36px] h-[36px] rounded-lg bg-phoenix-500/20 text-phoenix-400">
                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
            </div>
            <div class="leading-tight">
                <p class="text-xs font-semibold text-white uppercase tracking-wider">
                    @lang('phonix::app.misc.compare.bar_comparing')
                </p>
                <p class="text-xs text-slate-400">
                    <span x-text="compareItems.length"></span>
                    @lang('phonix::app.misc.compare.bar_of_four')
                </p>
            </div>
        </div>

        {{-- ── Divider (desktop) ─────────────────────────────────── --}}
        <div class="hidden md:block h-[40px] w-px bg-slate-700 shrink-0"></div>

        {{-- ── Middle: 4 product slots ───────────────────────────── --}}
        <div class="flex-1 flex items-center gap-[8px] overflow-x-auto scrollbar-none py-[4px]">
            <template x-for="(slot, index) in [0, 1, 2, 3]" :key="index">
                <div class="flex items-center gap-[8px] shrink-0">

                    {{-- Filled slot --}}
                    <template x-if="compareItems[index]">
                        <div
                            class="relative flex items-center gap-[8px] bg-slate-800 dark:bg-slate-900 border border-slate-700 rounded-lg px-[10px] py-[6px] min-w-[140px] max-w-[180px]"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-90"
                            x-transition:enter-end="opacity-100 scale-100"
                        >
                            {{-- Product thumbnail --}}
                            <div class="w-[36px] h-[36px] rounded-full overflow-hidden border-2 border-phoenix-500/50 shrink-0 bg-slate-700">
                                <template x-if="compareItems[index].imageUrl">
                                    <img
                                        :src="compareItems[index].imageUrl"
                                        :alt="compareItems[index].name"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    />
                                </template>
                                <template x-if="!compareItems[index].imageUrl">
                                    <div class="w-full h-full flex items-center justify-center text-slate-500">
                                        <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            {{-- Product name --}}
                            <span
                                class="text-xs text-slate-200 font-medium truncate flex-1 leading-snug"
                                x-text="compareItems[index].name"
                            ></span>

                            {{-- Remove ×  --}}
                            <button
                                @click="removeItem(compareItems[index].id)"
                                class="shrink-0 w-[18px] h-[18px] flex items-center justify-center rounded-full text-slate-400 hover:text-coral hover:bg-slate-700 transition-colors"
                                :aria-label="'{{ __('phonix::app.misc.compare.remove') }}'"
                            >
                                <svg class="w-[10px] h-[10px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>

                    {{-- Empty placeholder slot --}}
                    <template x-if="!compareItems[index]">
                        <div
                            class="flex items-center justify-center w-[48px] h-[48px] rounded-lg border-2 border-dashed border-slate-600 text-slate-600 shrink-0"
                            aria-hidden="true"
                        >
                            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                    </template>

                </div>
            </template>
        </div>

        {{-- ── Right: CTA buttons ────────────────────────────────── --}}
        <div class="flex items-center gap-[8px] shrink-0">

            {{-- Compare Now --}}
            <button
                @click="compareNow()"
                :disabled="compareItems.length < 2"
                :class="{
                    'opacity-50 cursor-not-allowed': compareItems.length < 2,
                    'hover:opacity-90': compareItems.length >= 2,
                }"
                class="btn-phoenix px-[16px] py-[10px] text-sm font-semibold whitespace-nowrap transition-opacity"
                :aria-label="'{{ __('phonix::app.misc.compare.compare_now') }}'"
            >
                <span class="hidden sm:inline">@lang('phonix::app.misc.compare.compare_now')</span>
                {{-- Mobile: icon only --}}
                <svg class="sm:hidden w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
            </button>

            {{-- Clear All --}}
            <button
                @click="clearAll()"
                class="text-xs text-slate-400 hover:text-coral transition-colors whitespace-nowrap px-[8px] py-[10px] underline underline-offset-2"
            >
                @lang('phonix::app.misc.compare.clear_all')
            </button>

        </div>
    </div>
</div>

{{-- ── Compare Bar spacer: prevents content from hiding behind the bar ── --}}
<div
    x-data="{ visible: false }"
    x-init="
        const bar = document.getElementById('phonix-compare-bar');
        if (bar) {
            const observer = new MutationObserver(() => {
                visible = bar.style.display !== 'none';
            });
            observer.observe(bar, { attributes: true, attributeFilter: ['style'] });
        }
    "
    x-show="visible"
    class="h-[80px]"
    aria-hidden="true"
></div>

@pushOnce('scripts')
<script>
    /**
     * compareBarStore()
     * ─────────────────────────────────────────────────────────────────────
     * Alpine.js data factory for the compare bar.
     *
     * External API (callable from any Alpine component, e.g. product-card):
     *   window.__phonixCompare.addItem(id, name, imageUrl)
     *   window.__phonixCompare.removeItem(id)
     *   window.__phonixCompare.hasItem(id)
     *
     * The window.__phonixCompare bridge is created once the Alpine component
     * initialises (x-init) so other components can call it without tight coupling.
     */
    function compareBarStore() {
        return {
            compareItems: JSON.parse(localStorage.getItem('phonix_compare') || '[]'),

            get count() {
                return this.compareItems.length;
            },

            _persist() {
                localStorage.setItem('phonix_compare', JSON.stringify(this.compareItems));
            },

            addItem(id, name, imageUrl) {
                if (this.compareItems.length >= 4) return;
                const idStr = String(id);
                if (!this.compareItems.find(i => String(i.id) === idStr)) {
                    this.compareItems.push({ id: idStr, name, imageUrl: imageUrl || '' });
                    this._persist();
                }
            },

            removeItem(id) {
                const idStr = String(id);
                this.compareItems = this.compareItems.filter(i => String(i.id) !== idStr);
                this._persist();
            },

            hasItem(id) {
                const idStr = String(id);
                return !!this.compareItems.find(i => String(i.id) === idStr);
            },

            toggleItem(id, name, imageUrl) {
                if (this.hasItem(id)) {
                    this.removeItem(id);
                } else {
                    this.addItem(id, name, imageUrl);
                }
            },

            clearAll() {
                this.compareItems = [];
                localStorage.removeItem('phonix_compare');
            },

            compareNow() {
                if (this.compareItems.length < 2) return;
                const ids = this.compareItems.map(i => i.id).join(',');
                window.location.href = `{{ route('phonix.compare.index') }}?ids=` + ids;
            },

            init() {
                // Bridge for non-Alpine code (e.g. Vanilla JS buttons on product cards).
                window.__phonixCompare = {
                    addItem:    (id, name, imageUrl) => this.addItem(id, name, imageUrl),
                    removeItem: (id)                 => this.removeItem(id),
                    toggleItem: (id, name, imageUrl) => this.toggleItem(id, name, imageUrl),
                    hasItem:    (id)                 => this.hasItem(id),
                    getCount:   ()                   => this.count,
                };

                // Listen for the custom DOM event so product-card buttons can trigger
                // add/remove without needing a direct Alpine.js parent reference.
                // Dispatch: document.dispatchEvent(new CustomEvent('phonix:compare:toggle',
                //            { detail: { id, name, imageUrl } }))
                document.addEventListener('phonix:compare:toggle', (e) => {
                    const { id, name, imageUrl } = e.detail || {};
                    if (id) this.toggleItem(id, name, imageUrl);
                });

                document.addEventListener('phonix:compare:add', (e) => {
                    const { id, name, imageUrl } = e.detail || {};
                    if (id) this.addItem(id, name, imageUrl);
                });

                document.addEventListener('phonix:compare:remove', (e) => {
                    const { id } = e.detail || {};
                    if (id) this.removeItem(id);
                });
            },
        };
    }
</script>
@endPushOnce
