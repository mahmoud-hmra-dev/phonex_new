/**
 * Phonix Theme - Swiper.js Carousel Initialization
 *
 * Exposes init / destroy so Swiper instances are re-created on each Turbo
 * visit and torn down before Turbo caches the page for back-nav.
 */

import Swiper from "swiper";
import {
    Navigation,
    Pagination,
    Autoplay,
    EffectFade,
    FreeMode,
} from "swiper/modules";

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import "swiper/css/effect-fade";

const isRTL = () =>
    document.dir === "rtl" || document.documentElement.dir === "rtl";

// Registry of live Swiper instances, flushed on every destroy call.
let _instances = [];

function push(sw) {
    if (sw) _instances.push(sw);
    return sw;
}

function initHeroSlider() {
    const el = document.querySelector(".swiper-hero");
    if (!el) return;

    push(
        new Swiper(el, {
            modules: [EffectFade, Autoplay, Pagination, Navigation],
            effect: "fade",
            fadeEffect: { crossFade: true },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: el.querySelector(".swiper-pagination"),
                clickable: true,
                bulletActiveClass:
                    "swiper-pagination-bullet-active bg-phoenix-500",
            },
            navigation: {
                nextEl: el.querySelector(".swiper-button-next"),
                prevEl: el.querySelector(".swiper-button-prev"),
            },
            loop: true,
            speed: 600,
            rtl: isRTL(),
            a11y: {
                prevSlideMessage: "Previous slide",
                nextSlideMessage: "Next slide",
            },
        })
    );
}

function initProductCarousels() {
    const carousels = document.querySelectorAll(".swiper-products");
    carousels.forEach((el) => {
        push(
            new Swiper(el, {
                modules: [Navigation, Autoplay],
                slidesPerView: 1,
                spaceBetween: 16,
                navigation: {
                    nextEl:
                        el.querySelector(".swiper-button-next") ||
                        el.parentElement?.querySelector(".swiper-button-next"),
                    prevEl:
                        el.querySelector(".swiper-button-prev") ||
                        el.parentElement?.querySelector(".swiper-button-prev"),
                },
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: true,
                    pauseOnMouseEnter: true,
                },
                breakpoints: {
                    480: { slidesPerView: 2, spaceBetween: 16 },
                    768: { slidesPerView: 3, spaceBetween: 20 },
                    1024: { slidesPerView: 4, spaceBetween: 24 },
                },
                rtl: isRTL(),
                a11y: {
                    prevSlideMessage: "Previous products",
                    nextSlideMessage: "Next products",
                },
            })
        );
    });
}

function initTestimonialCarousel() {
    const el = document.querySelector(".swiper-testimonials");
    if (!el) return;

    push(
        new Swiper(el, {
            modules: [Pagination, Autoplay],
            slidesPerView: 1,
            spaceBetween: 24,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: el.querySelector(".swiper-pagination"),
                clickable: true,
                bulletActiveClass:
                    "swiper-pagination-bullet-active bg-phoenix-500",
            },
            breakpoints: {
                768: { slidesPerView: 2, spaceBetween: 24 },
                1024: { slidesPerView: 3, spaceBetween: 24 },
            },
            loop: true,
            speed: 500,
            rtl: isRTL(),
            a11y: {
                prevSlideMessage: "Previous testimonial",
                nextSlideMessage: "Next testimonial",
            },
        })
    );
}

function initBrandCarousel() {
    const el = document.querySelector(".swiper-brands");
    if (!el) return;

    push(
        new Swiper(el, {
            modules: [FreeMode, Autoplay],
            slidesPerView: "auto",
            spaceBetween: 32,
            freeMode: {
                enabled: true,
                momentum: false,
            },
            autoplay: {
                delay: 0,
                disableOnInteraction: false,
            },
            speed: 4000,
            loop: true,
            allowTouchMove: true,
            rtl: isRTL(),
            a11y: {
                containerMessage: "Brand logos",
            },
        })
    );
}

export function initSwipers() {
    initHeroSlider();
    initProductCarousels();
    initTestimonialCarousel();
    initBrandCarousel();
}

export function destroySwipers() {
    _instances.forEach((sw) => {
        try {
            sw.destroy(true, true);
        } catch (_) {}
    });
    _instances = [];
}
