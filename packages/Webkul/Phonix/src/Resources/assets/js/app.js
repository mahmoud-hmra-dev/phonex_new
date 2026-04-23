/**
 * Phonix Theme - Main JavaScript Entry Point
 *
 * The theme is rendered server-side (Blade) but navigates like a SPA via
 * Hotwired Turbo Drive: link clicks and GET form submits fetch the target
 * page, only <body> is swapped, and elements marked data-turbo-permanent
 * survive the swap. Alpine, GSAP, Swiper, and cart/wishlist handlers are
 * wired up so they keep working across visits.
 */

/* ---------------------------------------------------------------
 * Turbo Drive — SPA-style navigation
 * --------------------------------------------------------------- */
import * as Turbo from "@hotwired/turbo";

// Show the progress bar only for visits that take longer than 150ms so
// instant cache-hit navigations don't flash the bar.
Turbo.session.drive = true;
Turbo.setProgressBarDelay(150);

/* ---------------------------------------------------------------
 * Alpine.js - Lightweight reactive framework
 * --------------------------------------------------------------- */
import Alpine from "alpinejs";
import focus from "@alpinejs/focus";
import intersect from "@alpinejs/intersect";
import persist from "@alpinejs/persist";
import collapse from "@alpinejs/collapse";

Alpine.plugin(focus);
Alpine.plugin(intersect);
Alpine.plugin(persist);
Alpine.plugin(collapse);

window.Alpine = Alpine;
Alpine.start();

// Alpine uses a MutationObserver, so new `x-data` components inside swapped
// body content initialize automatically on every turbo:load.

/* ---------------------------------------------------------------
 * GSAP & Swiper — re-run on each Turbo visit
 * Both modules export init() / destroy() and we call them here so that
 * page-local animations and carousels work across SPA navigations.
 * --------------------------------------------------------------- */
import { initAnimations, destroyAnimations } from "./animations.js";
import { initSwipers, destroySwipers } from "./swiper-init.js";

function boot() {
    initAnimations();
    initSwipers();
}

function teardown() {
    destroyAnimations();
    destroySwipers();
}

// Initial load (no Turbo involvement yet).
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
} else {
    boot();
}

// Every Turbo navigation.
document.addEventListener("turbo:load", boot);

// Before Turbo caches the current page for instant back-nav, drop timers /
// ScrollTriggers / Swiper instances so they don't leak into the next page.
document.addEventListener("turbo:before-cache", teardown);

/* ---------------------------------------------------------------
 * Global cart-badge + toast helpers — used by the add-to-cart and
 * wishlist AJAX handlers in product-card.blade.php / products/view.blade.php.
 * Exposed on window so inline Alpine components can call them.
 * --------------------------------------------------------------- */
window.phonix = window.phonix ?? {};

window.phonix.updateCartBadge = function (count) {
    const wrap = document.querySelector("[data-cart-badge-wrap]");
    if (!wrap) return;

    let badge = wrap.querySelector("[data-cart-badge]");

    if (count > 0) {
        if (!badge) {
            badge = document.createElement("span");
            badge.setAttribute("data-cart-badge", "");
            badge.className =
                "absolute top-[2px] end-[2px] flex items-center justify-center min-w-[18px] h-[18px] px-[4px] text-[10px] font-bold text-white bg-plasma-500 rounded-full shadow-lg shadow-plasma-500/40 ring-2 ring-white dark:ring-dark-bg";
            wrap.appendChild(badge);
        }
        badge.textContent = count > 99 ? "99+" : String(count);
        badge.animate(
            [
                { transform: "scale(1.4)" },
                { transform: "scale(1)" },
            ],
            { duration: 300, easing: "ease-out" }
        );
    } else if (badge) {
        badge.remove();
    }
};

window.phonix.toast = function (message, type = "info") {
    const root = document.querySelector("#phonix-toasts") ?? (() => {
        const el = document.createElement("div");
        el.id = "phonix-toasts";
        el.setAttribute("data-turbo-permanent", "");
        el.className =
            "fixed top-[84px] end-[16px] z-[100] flex flex-col gap-[8px] pointer-events-none";
        document.body.appendChild(el);
        return el;
    })();

    const tone =
        type === "error"
            ? "bg-red-500 text-white"
            : type === "success"
              ? "bg-emerald-500 text-white"
              : "bg-slate-900 text-white";

    const el = document.createElement("div");
    el.className = `pointer-events-auto px-[14px] py-[10px] rounded-xl shadow-lg text-sm font-medium ${tone} transition-all translate-x-[110%] opacity-0`;
    el.textContent = message;
    root.appendChild(el);

    requestAnimationFrame(() => {
        el.classList.remove("translate-x-[110%]", "opacity-0");
    });

    setTimeout(() => {
        el.classList.add("translate-x-[110%]", "opacity-0");
        setTimeout(() => el.remove(), 300);
    }, 2400);
};
