{{-- Brands Carousel Section --}}
<section class="section-padding bg-slate-50 dark:bg-dark-surface" data-gsap="fade-up">
    <div class="container">
        <x-phonix::section-heading :title="__('phonix::app.brands.title')" />

        {{-- Brands auto-scrolling carousel --}}
        <div
            x-data="brandsCarousel()"
            x-init="init()"
            class="relative overflow-hidden"
        >
            {{-- Gradient fades --}}
            <div class="absolute inset-y-0 start-0 w-[48px] md:w-[80px] bg-gradient-to-e from-slate-50 dark:from-dark-surface to-transparent z-10 pointer-events-none"></div>
            <div class="absolute inset-y-0 end-0 w-[48px] md:w-[80px] bg-gradient-to-s from-slate-50 dark:from-dark-surface to-transparent z-10 pointer-events-none" style="background: linear-gradient(to left, var(--color-surface), transparent);"></div>

            <div
                class="flex gap-[24px] md:gap-[40px]"
                x-ref="track"
                :style="'transform: translateX(' + offset + 'px); transition: transform 0.05s linear;'"
            >
                @php
                    $brands = [
                        'Apple', 'Samsung', 'Xiaomi', 'Dell', 'HP',
                        'Lenovo', 'Asus', 'Sony', 'JBL', 'Anker',
                        'Apple', 'Samsung', 'Xiaomi', 'Dell', 'HP',
                        'Lenovo', 'Asus', 'Sony', 'JBL', 'Anker',
                    ];
                @endphp

                @foreach ($brands as $brand)
                    <div class="flex-shrink-0 flex items-center justify-center w-[120px] md:w-[160px] h-[64px] md:h-[80px] rounded-lg bg-white dark:bg-dark-card border border-slate-100 dark:border-dark-border grayscale hover:grayscale-0 opacity-60 hover:opacity-100 transition-all duration-300 cursor-pointer group">
                        <span class="text-sm md:text-base font-bold text-slate-500 dark:text-slate-400 group-hover:text-phoenix-600 dark:group-hover:text-phoenix-400 tracking-wide transition-colors">
                            {{ $brand }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

@pushOnce('scripts')
<script>
    function brandsCarousel() {
        return {
            offset: 0,
            speed: 0.5,
            animationId: null,
            init() {
                const animate = () => {
                    this.offset -= this.speed;
                    const track = this.$refs.track;
                    if (track) {
                        const halfWidth = track.scrollWidth / 2;
                        if (Math.abs(this.offset) >= halfWidth) {
                            this.offset = 0;
                        }
                    }
                    this.animationId = requestAnimationFrame(animate);
                };
                this.animationId = requestAnimationFrame(animate);
            },
        };
    }
</script>
@endPushOnce
