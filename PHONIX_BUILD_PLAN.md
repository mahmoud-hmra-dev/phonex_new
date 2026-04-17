# Phonix Theme — Build Plan

Orchestration plan for building the Phonix premium electronics store theme for Bagisto 2.3.

---

## Phase 1 — Foundation

**Goal**: Establish the package structure, build pipeline, and design system foundation.

### Step 1.1: Package Scaffolding (`bagisto-architect`)
- [ ] Create `packages/Webkul/Phonix/` directory structure
- [ ] Write `PhonixServiceProvider.php` with boot/register methods
- [ ] Create package `composer.json` with PSR-4 autoload
- [ ] Update root `composer.json` with `Webkul\\Phonix\\` namespace
- [ ] Register provider in `bootstrap/providers.php`
- [ ] Register theme in `config/themes.php`
- [ ] Create `shop-routes.php` with route group
- [ ] Register Blade component namespace `phonix::`
- [ ] Run `composer dump-autoload` to verify

### Step 1.2: Build Pipeline (`build-engineer`)
- [ ] Create `package.json` with dependencies (Tailwind, Alpine.js, GSAP, Swiper.js)
- [ ] Create `vite.config.js` with Laravel plugin and correct output path
- [ ] Create `postcss.config.js` with Tailwind + Autoprefixer
- [ ] Create base `tailwind.config.js` (content paths, basic structure)
- [ ] Create CSS entry point: `src/Resources/assets/css/app.css`
- [ ] Create JS entry point: `src/Resources/assets/js/app.js`
- [ ] Run `npm install` and verify `npm run build`

### Step 1.3: Design Tokens (`ui-designer`)
- [ ] Configure full color palette in `tailwind.config.js` (phoenix-50 through phoenix-950)
- [ ] Add semantic color aliases (primary, accent, surface, muted)
- [ ] Configure dark mode colors (teal-tinted, not pure black)
- [ ] Set up font families (Cairo for AR, Inter for EN)
- [ ] Define shadow scale (card, modal, glow)
- [ ] Define border-radius scale
- [ ] Add custom animation keyframes
- [ ] Write `app.css` with `@layer base`, `@layer components`, font imports
- [ ] Verify build succeeds with new config

**Phase 1 Deliverable**: A buildable, empty theme package registered with Bagisto.

---

## Phase 2 — Core Layouts

**Goal**: Build the master layout, header, footer, and base components.

### Step 2.1: Master Layout (`frontend-engineer`)
- [ ] Create `layouts/master.blade.php` with HTML structure
- [ ] Include `@vite()` directive for CSS/JS
- [ ] Set up `<html>` with dynamic `dir` and `lang` attributes
- [ ] Create `@stack('styles')` and `@stack('scripts')` sections
- [ ] Add meta tags, favicon, Open Graph tags

### Step 2.2: Header Component (`frontend-engineer`)
- [ ] Logo with link to home
- [ ] Main navigation with mega-menu (Alpine.js)
- [ ] Search bar with autocomplete
- [ ] Language switcher (AR/EN)
- [ ] Dark mode toggle
- [ ] Cart icon with item count badge
- [ ] User account dropdown
- [ ] Mobile hamburger menu

### Step 2.3: Footer Component (`frontend-engineer`)
- [ ] Newsletter subscription form
- [ ] Link columns (About, Support, Legal, etc.)
- [ ] Social media icons
- [ ] Payment method badges
- [ ] Copyright and language info

### Step 2.4: Base Components (`frontend-engineer`)
- [ ] `<x-phonix::button>` — Primary, secondary, outline, ghost variants
- [ ] `<x-phonix::input>` — Text, email, password, search
- [ ] `<x-phonix::badge>` — Sale, new, hot, out-of-stock
- [ ] `<x-phonix::product-card>` — Image, title, price, rating, add-to-cart
- [ ] `<x-phonix::breadcrumb>` — Dynamic breadcrumb trail
- [ ] `<x-phonix::modal>` — Reusable modal with Alpine.js

### Step 2.5: Style Layouts (`ui-designer`)
- [ ] Style master layout (background, text colors, transitions)
- [ ] Style header (sticky, glassmorphism, hover states)
- [ ] Style footer (dark section, hover effects)
- [ ] Style all base components
- [ ] Verify dark mode for all elements
- [ ] Verify responsive at all breakpoints

### Step 2.6: Language Files (`i18n-specialist`) — *parallel with 2.5*
- [ ] Create `en/app.php` with all header/footer/component strings
- [ ] Create `ar/app.php` with Arabic translations
- [ ] Replace any hardcoded strings in views with `@lang()`
- [ ] Verify RTL rendering of header and footer

**Phase 2 Deliverable**: A styled, navigable layout shell with header, footer, and base components in both languages.

---

## Phase 3 — Pages

**Goal**: Build all shop pages with full styling, animations, and translations.

### Step 3.1: Home Page
- [ ] Hero slider with Swiper.js (`frontend-engineer`)
- [ ] Flash deals section with countdown timer (`frontend-engineer`)
- [ ] Featured categories grid (`frontend-engineer`)
- [ ] New arrivals product carousel (`frontend-engineer`)
- [ ] Brand logos carousel (`frontend-engineer`)
- [ ] Testimonials section (`frontend-engineer`)
- [ ] Style all home sections (`ui-designer`)
- [ ] Add scroll animations and hero transitions (`animation-specialist`)
- [ ] Translate all home page strings (`i18n-specialist`)

### Step 3.2: Category/Listing Page
- [ ] Filters sidebar with collapsible sections (`frontend-engineer`)
- [ ] Grid/List view toggle (`frontend-engineer`)
- [ ] Sort dropdown (`frontend-engineer`)
- [ ] Product grid with `<x-phonix::product-card>` (`frontend-engineer`)
- [ ] Pagination (`frontend-engineer`)
- [ ] Style listing page (`ui-designer`)
- [ ] Add filter and product animations (`animation-specialist`)
- [ ] Translate listing strings (`i18n-specialist`)

### Step 3.3: Product Detail Page
- [ ] Image gallery with zoom and thumbnails (`frontend-engineer`)
- [ ] Product info (title, price, rating, stock) (`frontend-engineer`)
- [ ] Variant selector (color, storage, etc.) (`frontend-engineer`)
- [ ] Tabs: Description, Specifications, Reviews (`frontend-engineer`)
- [ ] Related products carousel (`frontend-engineer`)
- [ ] Add to cart with fly-to-cart animation (`animation-specialist`)
- [ ] Style product page (`ui-designer`)
- [ ] Translate product strings (`i18n-specialist`)

### Step 3.4: Cart Page
- [ ] Cart items list with quantity controls (`frontend-engineer`)
- [ ] Coupon code input (`frontend-engineer`)
- [ ] Order summary sidebar (`frontend-engineer`)
- [ ] Style cart page (`ui-designer`)
- [ ] Translate cart strings (`i18n-specialist`)

### Step 3.5: Checkout Page
- [ ] Multi-step form: Address → Shipping → Payment → Review (`frontend-engineer`)
- [ ] Step indicator/progress bar (`frontend-engineer`)
- [ ] Form validation with Alpine.js (`frontend-engineer`)
- [ ] Style checkout page (`ui-designer`)
- [ ] Translate checkout strings (`i18n-specialist`)

### Step 3.6: Customer Account Pages
- [ ] Account dashboard (`frontend-engineer`)
- [ ] Order history and detail (`frontend-engineer`)
- [ ] Address book (`frontend-engineer`)
- [ ] Profile settings (`frontend-engineer`)
- [ ] Wishlist (`frontend-engineer`)
- [ ] Style account pages (`ui-designer`)
- [ ] Translate account strings (`i18n-specialist`)

**Phase 3 Deliverable**: All shop pages built, styled, animated, and translated.

---

## Phase 4 — Polish

**Goal**: Final animations, build optimization, comprehensive testing, and documentation.

### Step 4.1: Final Animations (`animation-specialist`)
- [ ] Page load sequence (logo rise → header → hero)
- [ ] Page transitions between routes
- [ ] Skeleton loaders for dynamic content
- [ ] Micro-interactions polish (buttons, inputs, toasts)
- [ ] Verify all animations respect `prefers-reduced-motion`
- [ ] Performance test: 60fps on mid-range devices

### Step 4.2: Build Optimization (`build-engineer`)
- [ ] Audit bundle sizes (CSS < 200KB, JS < 300KB)
- [ ] Configure code splitting per route
- [ ] Optimize font loading (subsetting, preload)
- [ ] Verify Tailwind purging removes unused CSS
- [ ] Run Lighthouse audit (target 90+ performance)
- [ ] Test production build end-to-end

### Step 4.3: Quality Assurance (`qa-engineer`)
- [ ] Test every page: LTR + RTL × Light + Dark × Mobile + Tablet + Desktop
- [ ] Accessibility audit (keyboard nav, ARIA, contrast, focus)
- [ ] Zero console errors verification
- [ ] Zero hardcoded strings verification
- [ ] Build success verification
- [ ] Bug report compilation and handoff

### Step 4.4: Documentation (`docs-writer`)
- [ ] Package README.md (English)
- [ ] Package README.ar.md (Arabic)
- [ ] Installation guide
- [ ] Configuration guide
- [ ] Component usage documentation
- [ ] CHANGELOG.md
- [ ] Troubleshooting FAQ

**Phase 4 Deliverable**: Production-ready theme with full documentation.

---

## Success Criteria

| Metric | Target |
|--------|--------|
| Lighthouse Performance | 90+ |
| Lighthouse Accessibility | 95+ |
| CSS Bundle Size (gzipped) | < 40KB |
| JS Bundle Size (gzipped) | < 80KB |
| LCP | < 2.5s |
| CLS | < 0.1 |
| Languages | Arabic (RTL) + English (LTR) |
| Dark Mode | Full coverage |
| WCAG Compliance | 2.1 AA |
| Hardcoded Strings | Zero |
| Console Errors | Zero |
