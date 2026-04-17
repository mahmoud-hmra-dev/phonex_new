# Build "Phonix" - Premium Electronics Store Theme for Bagisto 2.3

Create a professional, beautifully animated Bagisto theme package for an electronics store (phones, laptops, tablets, smartwatches, and accessories).

## Brand
- **Name**: Phonix (phoenix-inspired)
- **Package**: `packages/Webkul/Phonix/` with namespace `Webkul\Phonix`
- **Theme code**: `phonix`
- **Logo concept**: Phoenix bird in teal gradient
- **Colors** (derived from teal phoenix logo):
  - Primary: `#1A8A96` (teal)
  - Light accent: `#4FC3D0` / `#7DD8E0`
  - Dark: `#0F5F6B` / `#063138`
  - Gradient: `#4FC3D0 → #1A8A96 → #0F5F6B`
  - Accent pop: `#FF6B6B` (coral for badges)

## Must-Haves
1. **Proper Bagisto package structure** — Service Provider, composer.json, registered in `bootstrap/providers.php` and `config/themes.php`
2. **Vite build** with separate output directory
3. **Tailwind CSS v3** + custom config with brand tokens
4. **Alpine.js** + **GSAP 3** (ScrollTrigger) + **Swiper.js**
5. **Bilingual**: Arabic (RTL) + English (LTR) with full language files, no hardcoded strings
6. **Fonts**: Cairo (AR), Inter (EN) via Google Fonts
7. **Dark mode** with smooth toggle (teal-tinted, not pure black)
8. **Fully responsive** mobile-first
9. **Accessible** (ARIA, keyboard nav, `prefers-reduced-motion`)

## Design Vibe
Premium like Apple Store / Samsung.com but with distinctive teal identity. Glassmorphism, gradient meshes, subtle phoenix-inspired curves, teal glow on hover, rounded corners (12-16px), layered shadows.

## Required Pages
- **Homepage**: hero with 3D device + animations, categories grid, brands scroll, featured products, deal of the day countdown, testimonials, newsletter, trust badges
- **Product listing**: filters (brand, price, RAM, storage, color, rating), grid/list toggle, animated sort
- **Product detail**: image gallery + zoom + lightbox, variants, sticky add-to-cart, tabs (description/specs/reviews), related products
- **Cart drawer** (slide-in) + full cart + checkout flow
- **Customer account**, 404, about, contact
- **Global**: mega menu header with live search, language/currency/dark-mode switchers, footer, WhatsApp float, back-to-top

## Animations
- Phoenix-rise logo animation on load
- GSAP scroll-triggered fade-ups and stagger reveals
- Fly-to-cart with particle burst
- Product card lift + image zoom on hover
- Sticky blurred navbar on scroll
- Skeleton loaders
- Respect `prefers-reduced-motion`

## Rules
- **Don't modify core Bagisto files** — use package overrides
- Follow Bagisto conventions (Blade components, events, view structure)
- Use `<x-shop::layouts>` as base, create custom `<x-phonix::...>` components
- Commit incrementally with conventional commits (`feat:`, `style:`, etc.)
- Use Unsplash placeholders for images, document replacement points
- Quality bar: premium ThemeForest-level theme

## Execute Autonomously

You have full autonomy. Don't ask for approval between steps — just build it end-to-end:

1. Explore project structure, confirm Bagisto version
2. Scaffold package (directories, composer.json, ServiceProvider)
3. Register in root composer.json + providers + themes.php
4. Configure Vite + Tailwind + install npm deps
5. Build layouts (header, footer, master)
6. Create all language files (AR + EN)
7. Build all pages with animations
8. Implement RTL + dark mode
9. Run `npm run build` and verify
10. Write README.md with install + customization docs

Start now. Report progress at each phase. Only stop to ask if you hit a blocker that truly requires my decision.
