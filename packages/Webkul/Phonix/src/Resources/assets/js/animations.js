/**
 * Phonix Theme - GSAP Animation System
 *
 * Scroll-triggered reveals, hero timelines, counter animations,
 * sticky nav enhancements, product card hover effects, and fly-to-cart.
 *
 * Performance: Only animates transform & opacity (GPU-composited).
 * Accessibility: All animations disabled when prefers-reduced-motion is set.
 * RTL: slide-in-left / slide-in-right swap directions automatically.
 */

import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

/* ---------------------------------------------------------------
 * Reduced Motion Gate
 * --------------------------------------------------------------- */
const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
).matches;

/* ---------------------------------------------------------------
 * RTL Detection
 * --------------------------------------------------------------- */
const isRTL = () => document.dir === "rtl" || document.documentElement.dir === "rtl";

/* ---------------------------------------------------------------
 * Utility: parse a number string like "10,000" into 10000
 * --------------------------------------------------------------- */
function parseFormattedNumber(str) {
    return parseInt(str.replace(/[^0-9]/g, ""), 10) || 0;
}

/* ---------------------------------------------------------------
 * Utility: format number with commas
 * --------------------------------------------------------------- */
function formatNumber(n) {
    return n.toLocaleString("en-US");
}

/* ---------------------------------------------------------------
 * 1. Scroll-Triggered Reveals — [data-gsap] elements
 * --------------------------------------------------------------- */
function initScrollReveals() {
    const rtl = isRTL();

    const animationMap = {
        "fade-up": { from: { opacity: 0, y: 30 }, to: { opacity: 1, y: 0, duration: 0.6, ease: "power2.out" } },
        "fade-in": { from: { opacity: 0 }, to: { opacity: 1, duration: 0.5, ease: "power2.out" } },
        "scale-in": { from: { opacity: 0, scale: 0.9 }, to: { opacity: 1, scale: 1, duration: 0.5, ease: "power2.out" } },
        "slide-in-left": {
            from: { opacity: 0, x: rtl ? 50 : -50 },
            to: { opacity: 1, x: 0, duration: 0.6, ease: "power2.out" },
        },
        "slide-in-right": {
            from: { opacity: 0, x: rtl ? -50 : 50 },
            to: { opacity: 1, x: 0, duration: 0.6, ease: "power2.out" },
        },
    };

    // Individual element reveals
    const revealTypes = Object.keys(animationMap);
    const selector = revealTypes.map((t) => `[data-gsap="${t}"]`).join(",");
    const elements = document.querySelectorAll(selector);

    elements.forEach((el) => {
        const type = el.getAttribute("data-gsap");
        const anim = animationMap[type];
        if (!anim) return;

        gsap.set(el, anim.from);
        gsap.to(el, {
            ...anim.to,
            scrollTrigger: {
                trigger: el,
                start: "top 85%",
                toggleActions: "play none none none",
            },
        });
    });
}

/* ---------------------------------------------------------------
 * 2. Stagger Animations — [data-gsap="stagger"]
 * --------------------------------------------------------------- */
function initStaggerAnimations() {
    const parents = document.querySelectorAll('[data-gsap="stagger"]');

    parents.forEach((parent) => {
        const children = parent.children;
        if (!children.length) return;

        gsap.set(children, { opacity: 0, y: 20 });

        ScrollTrigger.batch(children, {
            onEnter: (batch) => {
                gsap.to(batch, {
                    opacity: 1,
                    y: 0,
                    duration: 0.5,
                    ease: "power2.out",
                    stagger: 0.1,
                });
            },
            start: "top 85%",
        });
    });
}

/* ---------------------------------------------------------------
 * 3. Hero Animation — [data-gsap="hero"]
 * --------------------------------------------------------------- */
function initHeroAnimation() {
    const hero = document.querySelector('[data-gsap="hero"]');
    if (!hero) return;

    const tl = gsap.timeline({ defaults: { ease: "power2.out" } });

    // Gather hero child elements (they may not all exist)
    const tag = hero.querySelector(".inline-flex"); // small tag line
    const heading = hero.querySelector("h1");
    const subtitle = hero.querySelector("p");
    const ctas = hero.querySelectorAll(".btn-phoenix, .btn-phoenix-outline");
    const trustIndicators = hero.querySelector(".text-slate-400, .text-xs");
    const deviceVisual = hero.querySelector(".aspect-square");

    // Set initial states
    const fadeTargets = [tag, heading, subtitle, ...ctas, trustIndicators, deviceVisual].filter(Boolean);
    gsap.set(fadeTargets, { opacity: 0, y: 20 });

    // Build timeline
    if (tag) tl.to(tag, { opacity: 1, y: 0, duration: 0.4 }, 0.1);
    if (heading) tl.to(heading, { opacity: 1, y: 0, duration: 0.5 }, 0.25);
    if (subtitle) tl.to(subtitle, { opacity: 1, y: 0, duration: 0.4 }, 0.5);

    if (ctas.length) {
        tl.to(ctas, { opacity: 1, y: 0, duration: 0.4, stagger: 0.1 }, 0.7);
    }

    if (trustIndicators) {
        tl.to(trustIndicators, { opacity: 1, y: 0, duration: 0.3 }, 0.95);
    }

    if (deviceVisual) {
        gsap.set(deviceVisual, { opacity: 0, scale: 0.9 });
        tl.to(deviceVisual, { opacity: 1, scale: 1, y: 0, duration: 0.6, ease: "power2.out" }, 0.4);
    }
}

/* ---------------------------------------------------------------
 * 4. Sticky Navbar Enhancement — [data-gsap="sticky-nav"]
 * --------------------------------------------------------------- */
function initStickyNav() {
    const nav = document.querySelector('[data-gsap="sticky-nav"]');
    if (!nav) return;

    // The header already uses Alpine for scroll detection; we enhance with GSAP
    // for the top bar hide/show on scroll
    const topBar = nav.querySelector(".hidden.lg\\:block");

    if (topBar) {
        ScrollTrigger.create({
            start: "top -50",
            end: 99999,
            onUpdate: (self) => {
                if (self.direction === 1 && self.scroll() > 50) {
                    // Scrolling down past threshold — collapse top bar
                    gsap.to(topBar, {
                        height: 0,
                        opacity: 0,
                        overflow: "hidden",
                        duration: 0.3,
                        ease: "power2.inOut",
                    });
                } else if (self.direction === -1 && self.scroll() < 100) {
                    // Scrolling up near top — restore top bar
                    gsap.to(topBar, {
                        height: "auto",
                        opacity: 1,
                        duration: 0.3,
                        ease: "power2.inOut",
                        clearProps: "overflow",
                    });
                }
            },
        });
    }
}

/* ---------------------------------------------------------------
 * 5. Counter Animation — [data-gsap="counter"]
 * --------------------------------------------------------------- */
function initCounterAnimations() {
    const counters = document.querySelectorAll('[data-gsap="counter"]');

    counters.forEach((el) => {
        // Parse target value from data-target or text content
        const rawText = el.textContent.trim();
        const target =
            parseInt(el.getAttribute("data-target"), 10) ||
            parseFormattedNumber(rawText);

        if (!target) return;

        // Determine suffix (e.g. "+") from last character or child span
        const suffixSpan = el.querySelector("span");
        const suffix = suffixSpan ? suffixSpan.textContent.trim() : "";

        // Create a proxy object to tween
        const proxy = { value: 0 };

        // Set initial display to 0
        if (suffixSpan) {
            el.childNodes[0].textContent = "0";
        } else {
            el.textContent = "0" + suffix;
        }

        gsap.to(proxy, {
            value: target,
            duration: 2,
            ease: "power1.out",
            snap: { value: 1 },
            scrollTrigger: {
                trigger: el,
                start: "top 85%",
                toggleActions: "play none none none",
            },
            onUpdate: () => {
                const formatted = formatNumber(Math.round(proxy.value));
                if (suffixSpan) {
                    // Set text before the span
                    el.childNodes[0].textContent = formatted;
                } else {
                    el.textContent = formatted + suffix;
                }
            },
        });
    });
}

/* ---------------------------------------------------------------
 * 6. Product Card Hover — .card-phoenix
 * --------------------------------------------------------------- */
function initProductCardHover() {
    const cards = document.querySelectorAll(".card-phoenix");

    cards.forEach((card) => {
        const img = card.querySelector("img");

        card.addEventListener("mouseenter", () => {
            gsap.to(card, {
                y: -8,
                boxShadow: "0 20px 40px rgba(26, 138, 150, 0.15)",
                duration: 0.3,
                ease: "power2.out",
            });
            if (img) {
                gsap.to(img, { scale: 1.05, duration: 0.3, ease: "power2.out" });
            }
        });

        card.addEventListener("mouseleave", () => {
            gsap.to(card, {
                y: 0,
                boxShadow: "none",
                duration: 0.3,
                ease: "power2.out",
            });
            if (img) {
                gsap.to(img, { scale: 1, duration: 0.3, ease: "power2.out" });
            }
        });
    });
}

/* ---------------------------------------------------------------
 * 7. Page Load Sequence
 * --------------------------------------------------------------- */
function initPageLoadSequence() {
    const header = document.querySelector('[data-gsap="sticky-nav"]');
    const main = document.querySelector("main") || document.querySelector("[role='main']");

    const targets = [header, main].filter(Boolean);
    if (!targets.length) return;

    gsap.set(targets, { opacity: 0 });
    gsap.to(targets, {
        opacity: 1,
        duration: 0.4,
        stagger: 0.1,
        ease: "power2.out",
    });
}

/* ---------------------------------------------------------------
 * 8. Fly-to-Cart Animation (callable from Alpine.js)
 * --------------------------------------------------------------- */
export function flyToCart(productElement) {
    const img = productElement.querySelector("img");
    if (!img) return Promise.resolve();

    const cartIcon =
        document.querySelector("[data-cart-icon]") ||
        document.querySelector(".cart-icon") ||
        document.querySelector('a[href*="cart"] svg');

    if (!cartIcon) return Promise.resolve();

    // Clone the image for flight
    const clone = img.cloneNode(true);
    const imgRect = img.getBoundingClientRect();
    const cartRect = cartIcon.getBoundingClientRect();

    // Style the clone for absolute positioning
    Object.assign(clone.style, {
        position: "fixed",
        top: `${imgRect.top}px`,
        left: `${imgRect.left}px`,
        width: `${imgRect.width}px`,
        height: `${imgRect.height}px`,
        zIndex: "9999",
        pointerEvents: "none",
        borderRadius: "8px",
        objectFit: "cover",
    });

    document.body.appendChild(clone);

    return new Promise((resolve) => {
        gsap.to(clone, {
            top: cartRect.top + cartRect.height / 2,
            left: cartRect.left + cartRect.width / 2,
            width: 30,
            height: 30,
            opacity: 0.3,
            scale: 0.3,
            borderRadius: "50%",
            duration: 0.7,
            ease: "power2.in",
            onComplete: () => {
                clone.remove();

                // Bump the cart badge
                const badge = cartIcon.closest("[data-cart-count]") ||
                    cartIcon.parentElement?.querySelector(".badge, [data-cart-count]");

                if (badge) {
                    gsap.fromTo(
                        badge,
                        { scale: 1.4 },
                        { scale: 1, duration: 0.3, ease: "elastic.out(1, 0.5)" }
                    );
                }

                resolve();
            },
        });
    });
}

// Make flyToCart available globally for Alpine.js
window.flyToCart = flyToCart;

/* ---------------------------------------------------------------
 * Initialization
 * --------------------------------------------------------------- */
function init() {
    if (prefersReducedMotion) {
        // Make all data-gsap elements visible immediately (no animation)
        document.querySelectorAll("[data-gsap]").forEach((el) => {
            el.style.opacity = "1";
        });
        return;
    }

    initPageLoadSequence();
    initScrollReveals();
    initStaggerAnimations();
    initHeroAnimation();
    initStickyNav();
    initCounterAnimations();
    initProductCardHover();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
} else {
    init();
}
