/**
 * Phonix Theme - Main JavaScript Entry Point
 */

/* ---------------------------------------------------------------
 * Alpine.js - Lightweight reactive framework
 * --------------------------------------------------------------- */
import Alpine from "alpinejs";
import focus from "@alpinejs/focus";
import intersect from "@alpinejs/intersect";
import persist from "@alpinejs/persist";

Alpine.plugin(focus);
Alpine.plugin(intersect);
Alpine.plugin(persist);

window.Alpine = Alpine;
Alpine.start();

/* ---------------------------------------------------------------
 * GSAP - Animation library
 * Scroll-triggered reveals, hero timelines, counters, sticky nav,
 * product card hover effects, and fly-to-cart animation.
 * --------------------------------------------------------------- */
import "./animations.js";

/* ---------------------------------------------------------------
 * Swiper - Touch slider
 * Hero slider, product carousels, testimonials, brand carousel.
 * --------------------------------------------------------------- */
import "./swiper-init.js";
