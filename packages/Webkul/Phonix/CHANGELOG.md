# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [1.0.0] - 2024-03-XX

### Added

#### Package Structure
- Laravel package with `PhonixServiceProvider` (view loading, translations, routes, config merge)
- Composer autoload via PSR-4 (`Webkul\Phonix`)
- Vite 5 build pipeline with `laravel-vite-plugin`, outputting to `public/themes/shop/phonix/build/`
- Tailwind CSS 3 configuration with custom design tokens
- PostCSS with Autoprefixer

#### Design System
- Custom `phoenix` teal color palette (50--950) with CSS custom properties
- Accent colors: `coral` (#FF6B6B) and `gold` (#FFD93D)
- Teal-tinted `slate` gray palette for UI surfaces
- Dark mode surface colors (`dark-bg`, `dark-surface`, `dark-card`, `dark-border`)
- Three font families: Cairo (Arabic), Inter (body), Poppins (headings)
- Fluid typography scale using `clamp()` (fluid-xs through fluid-5xl)
- 8px spacing grid
- Shadow hierarchy: sm, default, md, lg, xl, glow, card, card-hover, modal, dark variants
- Backdrop blur scale (xs through 3xl)
- CSS keyframe animations: fade-in, fade-up, slide-in-left, slide-in-right, scale-in, shimmer, pulse-glow, float
- Custom easing function `ease-phoenix` (cubic-bezier 0.22, 1, 0.36, 1)
- CSS component classes: `btn-phoenix`, `btn-phoenix-outline`, `btn-phoenix-ghost`, `card-phoenix`, `card-glass`, `badge-sale`, `badge-new`, `badge-hot`, `input-phoenix`

#### Master Layout
- `<x-phonix::layouts.index>` -- main page wrapper with Vite asset loading
- `<x-phonix::layouts.header.index>` -- site header with navigation and search
- `<x-phonix::layouts.header.mobile-nav>` -- mobile hamburger navigation drawer
- `<x-phonix::layouts.footer.index>` -- site footer with links and copyright
- Class-based dark mode toggle
- RTL support via logical CSS properties and `dir="rtl"` detection

#### UI Components
- `<x-phonix::button>` -- primary, outline, ghost variants; sm/md/lg sizes; link mode; loading spinner
- `<x-phonix::card>` -- default and glass (frosted) variants
- `<x-phonix::badge>` -- new, sale, hot badge types
- `<x-phonix::input>` -- labeled input with error state and ARIA attributes
- `<x-phonix::modal>` -- Alpine.js powered modal with focus trap, backdrop blur, escape key
- `<x-phonix::breadcrumb>` -- accessible breadcrumb navigation with RTL chevron rotation
- `<x-phonix::pagination>` -- page navigation with ellipsis, result count, previous/next
- `<x-phonix::section-heading>` -- section title with decorative underline, subtitle, view-all link

#### Homepage Sections
- `<x-phonix::hero>` -- full-width hero with gradient background and decorative orbs
- `<x-phonix::categories-grid>` -- category grid with icons
- `<x-phonix::flash-deals>` -- flash deal product cards
- `<x-phonix::featured-products>` -- featured products grid/carousel
- `<x-phonix::deal-of-day>` -- highlighted daily deal
- `<x-phonix::brands-carousel>` -- brand logo carousel (Swiper.js)
- `<x-phonix::testimonials>` -- customer testimonials
- `<x-phonix::stats-counter>` -- animated statistics counters
- `<x-phonix::features-bar>` -- trust features bar (shipping, returns, support)
- `<x-phonix::newsletter>` -- newsletter subscription form

#### Product Pages
- Product listing page with `<x-phonix::filters-sidebar>` for advanced filtering
- Product detail page with `<x-phonix::product-gallery>` and `<x-phonix::product-tabs>`
- `<x-phonix::product-card>` -- product card with hover quick actions (wishlist, compare, quick view), star ratings, sale price display

#### Cart & Checkout
- Cart page (`phonix::cart.index`)
- `<x-phonix::cart-drawer>` -- slide-in cart drawer
- Multi-step checkout page (`phonix::checkout.index`)
- Order success page (`phonix::checkout.success`)

#### Customer Account
- `<x-phonix::account.layout>` -- account sidebar layout wrapper
- Account dashboard (`phonix::account.dashboard`)
- Order history and order detail pages
- Address book management
- Wishlist page
- Profile editing page

#### Authentication
- Login page (`phonix::auth.login`)
- Registration page (`phonix::auth.register`)
- Forgot password page (`phonix::auth.forgot-password`)

#### Error Pages
- Custom 404 error page (`phonix::errors.404`)

#### Internationalization
- English translations (`src/Resources/lang/en/app.php`)
- Arabic translations (`src/Resources/lang/ar/app.php`)
- RTL-aware layout using logical CSS properties (`start`/`end`)
- Automatic font switching: Cairo for Arabic, Poppins/Inter for English

#### JavaScript
- Alpine.js with plugins: Focus, Intersect, Persist
- GSAP and ScrollTrigger integration (prepared, import-ready)
- Swiper.js carousel integration (prepared, import-ready)

#### Accessibility
- ARIA labels and roles on interactive elements
- `aria-current="page"` on active breadcrumb/pagination items
- `aria-invalid` and `aria-describedby` on form error states
- Focus-visible ring styling via CSS custom property
- Keyboard navigation support (Escape to close modals)
- `prefers-reduced-motion` consideration in animation design
