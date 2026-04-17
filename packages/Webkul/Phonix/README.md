# Phonix -- Premium Electronics Store Theme for Bagisto

A modern, bilingual (Arabic RTL + English LTR) e-commerce storefront theme built for Bagisto 2.3+. Phonix features a teal-accented design system, dark mode support, GSAP scroll animations, and a fully responsive mobile-first layout optimized for electronics retail.

## Features

- **Bilingual support** -- Arabic (RTL) and English (LTR) with 400+ translation keys
- **Dark mode** -- Class-based toggle with dedicated dark surface palette
- **Responsive** -- Mobile-first design with fluid typography (`clamp()`)
- **Design system** -- Custom teal color palette, 8px spacing grid, shadow hierarchy
- **Animations** -- GSAP-powered scroll reveals and CSS keyframe micro-interactions
- **Carousels** -- Swiper.js integration for product and brand sliders
- **Alpine.js interactivity** -- Focus trap, intersection observer, persisted state
- **Accessibility** -- ARIA attributes, keyboard navigation, `prefers-reduced-motion`
- **Glassmorphism cards** -- Frosted-glass card variant with backdrop blur
- **Full storefront** -- Home, product listing, product detail, cart, checkout, account, auth pages

## Screenshots

Screenshots coming soon.

## Requirements

| Dependency | Version |
|------------|---------|
| PHP        | 8.2+    |
| Bagisto    | 2.3+    |
| Node.js    | 18+     |
| Composer   | 2+      |

## Installation

### Step 1: Copy or require the package

Place the package at `packages/Webkul/Phonix/` inside your Bagisto root. Then add the PSR-4 namespace to the root `composer.json`:

```json
"autoload": {
    "psr-4": {
        "Webkul\\Phonix\\": "packages/Webkul/Phonix/src/"
    }
}
```

### Step 2: Register the Service Provider

Add the provider to `bootstrap/providers.php`:

```php
return [
    // ... existing providers
    Webkul\Phonix\Providers\PhonixServiceProvider::class,
];
```

### Step 3: Register the Theme

Add the `phonix` entry to `config/themes.php` under the `shop` key:

```php
'phonix' => [
    'name'        => 'Phonix',
    'assets_path' => 'public/themes/shop/phonix',
    'views_path'  => 'packages/Webkul/Phonix/src/Resources/views',

    'vite' => [
        'hot_file'                 => 'shop-phonix-vite.hot',
        'build_directory'          => 'themes/shop/phonix/build',
        'package_assets_directory' => 'src/Resources/assets',
    ],
],
```

### Step 4: Update Autoload

```bash
composer dump-autoload
```

### Step 5: Install Frontend Dependencies

```bash
cd packages/Webkul/Phonix
npm install
```

### Step 6: Build Assets

```bash
# Development (with HMR)
npm run dev

# Production
npm run build
```

Built assets are output to `public/themes/shop/phonix/build/`.

### Step 7: Activate the Theme

Set the active shop theme in `config/themes.php`:

```php
'shop-default' => 'phonix',
```

Or change it from the Bagisto admin panel under **Settings > Channels > Design > Default Theme**.

### Step 8: Clear Cache

```bash
php artisan optimize:clear
```

## Configuration

### Theme Settings

The theme configuration lives in `packages/Webkul/Phonix/src/Config/themes.php` and is merged into `config('themes.shop.phonix')` at runtime. Publish it to customize:

```bash
php artisan vendor:publish --tag=phonix-config
```

### Customizing Colors

Edit `packages/Webkul/Phonix/tailwind.config.js`. The primary palette is `phoenix`:

| Token          | Hex       | Usage                     |
|----------------|-----------|---------------------------|
| `phoenix-50`   | `#E6F7F9` | Lightest tint, hover bg   |
| `phoenix-100`  | `#C2ECF0` | Light backgrounds         |
| `phoenix-200`  | `#94DCE4` | Selection background      |
| `phoenix-300`  | `#7DD8E0` | Dark mode primary hover   |
| `phoenix-400`  | `#4FC3D0` | Dark mode primary         |
| `phoenix-500`  | `#1A8A96` | Primary brand color       |
| `phoenix-600`  | `#127883` | Primary hover             |
| `phoenix-700`  | `#0F5F6B` | Active / pressed          |
| `phoenix-800`  | `#0A4852` | Dark accents              |
| `phoenix-900`  | `#063138` | Darkest accents           |
| `phoenix-950`  | `#031D20` | Near-black                |

Accent colors:

| Token   | Hex       | Usage            |
|---------|-----------|------------------|
| `coral` | `#FF6B6B` | Sale badges, errors |
| `gold`  | `#FFD93D` | Star ratings     |

### Customizing Fonts

Three font families are loaded from Google Fonts:

| Token      | Font Family | Usage                        |
|------------|-------------|------------------------------|
| `cairo`    | Cairo       | Arabic body and headings     |
| `inter`    | Inter       | English body text            |
| `poppins`  | Poppins     | English headings             |

To change fonts, update both `tailwind.config.js` (the `fontFamily` key) and the `@import url(...)` in `src/Resources/assets/css/app.css`.

### Dark Mode

Dark mode uses Tailwind's `class` strategy. Add the `dark` class to the `<html>` element to activate dark mode. CSS custom properties switch automatically:

| Property            | Light       | Dark        |
|---------------------|-------------|-------------|
| `--color-bg`        | `#FFFFFF`   | `#0A1A1D`   |
| `--color-surface`   | `#F5F8F8`   | `#0F2528`   |
| `--color-card`      | `#FFFFFF`   | `#132E32`   |
| `--color-border`    | `#D4DCDD`   | `#1A3D42`   |
| `--color-text`      | `#1E2829`   | `#E8EDED`   |

### Language / RTL

The theme detects `lang="ar"` or `dir="rtl"` on the `<html>` element and automatically:

- Switches the body font to Cairo
- Mirrors layout using logical CSS properties (`start`/`end` instead of `left`/`right`)
- Rotates directional icons (chevrons, arrows) via `rtl:rotate-180`

Translation files are at:
- `src/Resources/lang/en/app.php` (English)
- `src/Resources/lang/ar/app.php` (Arabic)

## Pages & Routes

All routes are prefixed with `/phonix`.

| Route                          | Name                         | View                          | Description              |
|--------------------------------|------------------------------|-------------------------------|--------------------------|
| `GET /phonix`                  | `phonix.home`                | `phonix::home`                | Homepage                 |
| `GET /phonix/products`         | `phonix.products.index`      | `phonix::products.index`      | Product listing          |
| `GET /phonix/products/{slug}`  | `phonix.products.view`       | `phonix::products.view`       | Product detail           |
| `GET /phonix/cart`             | `phonix.cart.index`          | `phonix::cart.index`          | Shopping cart             |
| `GET /phonix/checkout`         | `phonix.checkout.index`      | `phonix::checkout.index`      | Multi-step checkout      |
| `GET /phonix/checkout/success` | `phonix.checkout.success`    | `phonix::checkout.success`    | Order confirmation       |
| `GET /phonix/account`          | `phonix.account.dashboard`   | `phonix::account.dashboard`   | Account dashboard        |
| `GET /phonix/account/orders`   | `phonix.account.orders`      | `phonix::account.orders.index`| Order history            |
| `GET /phonix/account/orders/{id}` | `phonix.account.orders.view` | `phonix::account.orders.view` | Order detail          |
| `GET /phonix/account/addresses`| `phonix.account.addresses`   | `phonix::account.addresses.index` | Address book        |
| `GET /phonix/account/wishlist` | `phonix.account.wishlist`    | `phonix::account.wishlist`    | Wishlist                 |
| `GET /phonix/account/profile`  | `phonix.account.profile`     | `phonix::account.profile`     | Edit profile             |
| `GET /phonix/login`            | `phonix.auth.login`          | `phonix::auth.login`          | Login                    |
| `GET /phonix/register`         | `phonix.auth.register`       | `phonix::auth.register`       | Registration             |
| `GET /phonix/forgot-password`  | `phonix.auth.forgot`         | `phonix::auth.forgot-password`| Password reset           |

## Components

All components use the `x-phonix::` prefix.

### UI Primitives

| Component | Usage | Props |
|-----------|-------|-------|
| `<x-phonix::button>` | `<x-phonix::button variant="primary" size="md">Text</x-phonix::button>` | `variant` (primary, outline, ghost), `size` (sm, md, lg), `type`, `href`, `disabled`, `loading` |
| `<x-phonix::card>` | `<x-phonix::card variant="glass">Content</x-phonix::card>` | `variant` (default, glass) |
| `<x-phonix::badge>` | `<x-phonix::badge type="sale">-20%</x-phonix::badge>` | `type` (new, sale, hot) |
| `<x-phonix::input>` | `<x-phonix::input type="email" label="Email" name="email" />` | `type`, `label`, `error`, `name` |
| `<x-phonix::modal>` | `<x-phonix::modal name="quick-view" maxWidth="xl">Body</x-phonix::modal>` | `name` (required), `maxWidth` (sm, md, lg, xl, 2xl) |
| `<x-phonix::breadcrumb>` | `<x-phonix::breadcrumb :items="$items" />` | `items` (array of `['label' => '', 'url' => '']`) |
| `<x-phonix::pagination>` | `<x-phonix::pagination :currentPage="1" :totalPages="7" />` | `currentPage`, `totalPages`, `from`, `to`, `total` |
| `<x-phonix::section-heading>` | `<x-phonix::section-heading title="Featured" subtitle="Top picks" />` | `title`, `subtitle`, `viewAllUrl` |

### Content Components

| Component | Description |
|-----------|-------------|
| `<x-phonix::hero>` | Full-width hero section with gradient background and decorative orbs |
| `<x-phonix::product-card>` | Product card with image, badges, rating, price, quick actions (wishlist, compare, quick view) |
| `<x-phonix::product-gallery>` | Product image gallery with thumbnails |
| `<x-phonix::product-tabs>` | Tabbed content for product details (description, specs, reviews) |
| `<x-phonix::categories-grid>` | Category grid with icons |
| `<x-phonix::featured-products>` | Featured products carousel/grid |
| `<x-phonix::flash-deals>` | Flash deal cards with countdown styling |
| `<x-phonix::deal-of-day>` | Single highlighted deal section |
| `<x-phonix::brands-carousel>` | Brand logo carousel (Swiper) |
| `<x-phonix::testimonials>` | Customer testimonials section |
| `<x-phonix::stats-counter>` | Animated statistics counters |
| `<x-phonix::features-bar>` | Trust features bar (shipping, returns, etc.) |
| `<x-phonix::newsletter>` | Newsletter subscription form |
| `<x-phonix::filters-sidebar>` | Product listing filter panel |
| `<x-phonix::cart-drawer>` | Slide-in cart drawer |

### Layout Components

| Component | Description |
|-----------|-------------|
| `<x-phonix::layouts.index>` | Master layout (wraps header + footer + Vite assets) |
| `<x-phonix::layouts.header.index>` | Site header with navigation and search |
| `<x-phonix::layouts.header.mobile-nav>` | Mobile hamburger navigation drawer |
| `<x-phonix::layouts.footer.index>` | Site footer with links and copyright |
| `<x-phonix::account.layout>` | Account pages sidebar layout |

## Design System

### Shadows

| Token         | Description                              |
|---------------|------------------------------------------|
| `shadow-sm`   | Subtle elevation                         |
| `shadow`      | Default card resting state               |
| `shadow-md`   | Medium elevation                         |
| `shadow-lg`   | Dropdowns, popovers                      |
| `shadow-xl`   | Floating elements                        |
| `shadow-glow` | Teal glow effect (CTAs, focus)           |
| `shadow-card` | Product/content card resting state       |
| `shadow-card-hover` | Card hover lift                    |
| `shadow-modal`| Modal overlay shadow                     |

### Animations (CSS)

| Class                 | Effect                                     |
|-----------------------|--------------------------------------------|
| `animate-fade-in`     | Opacity 0 to 1 (0.5s)                     |
| `animate-fade-up`     | Fade in + slide up 16px (0.6s)            |
| `animate-slide-in-left` | Fade in + slide from left 24px (0.5s)  |
| `animate-slide-in-right` | Fade in + slide from right 24px (0.5s)|
| `animate-scale-in`    | Fade in + scale from 95% (0.4s)           |
| `animate-shimmer`     | Loading skeleton shimmer (infinite)        |
| `animate-pulse-glow`  | Pulsing teal glow (infinite)               |
| `animate-float`       | Gentle vertical float (infinite)           |

### GSAP Animations

Elements with `data-gsap="fade-up"` are animated on scroll via GSAP ScrollTrigger. The hero section uses `data-gsap="hero"` for a dedicated entrance timeline. GSAP and Swiper imports are prepared in `src/Resources/assets/js/app.js` and can be activated by uncommenting the import blocks.

## Project Structure

```
packages/Webkul/Phonix/
├── composer.json
├── package.json
├── tailwind.config.js
├── vite.config.js
└── src/
    ├── Config/
    │   └── themes.php
    ├── Http/
    │   └── Controllers/
    ├── Providers/
    │   └── PhonixServiceProvider.php
    ├── Resources/
    │   ├── assets/
    │   │   ├── css/
    │   │   │   └── app.css
    │   │   └── js/
    │   │       └── app.js
    │   ├── lang/
    │   │   ├── ar/
    │   │   │   └── app.php
    │   │   └── en/
    │   │       └── app.php
    │   └── views/
    │       ├── layouts/
    │       │   └── index.blade.php
    │       ├── components/
    │       │   ├── layouts/
    │       │   │   ├── index.blade.php
    │       │   │   ├── header/
    │       │   │   │   ├── index.blade.php
    │       │   │   │   └── mobile-nav.blade.php
    │       │   │   └── footer/
    │       │   │       └── index.blade.php
    │       │   ├── account/
    │       │   │   └── layout.blade.php
    │       │   ├── button.blade.php
    │       │   ├── card.blade.php
    │       │   ├── badge.blade.php
    │       │   ├── input.blade.php
    │       │   ├── modal.blade.php
    │       │   ├── breadcrumb.blade.php
    │       │   ├── pagination.blade.php
    │       │   ├── section-heading.blade.php
    │       │   ├── product-card.blade.php
    │       │   ├── product-gallery.blade.php
    │       │   ├── product-tabs.blade.php
    │       │   ├── hero.blade.php
    │       │   ├── categories-grid.blade.php
    │       │   ├── featured-products.blade.php
    │       │   ├── flash-deals.blade.php
    │       │   ├── deal-of-day.blade.php
    │       │   ├── brands-carousel.blade.php
    │       │   ├── testimonials.blade.php
    │       │   ├── stats-counter.blade.php
    │       │   ├── features-bar.blade.php
    │       │   ├── newsletter.blade.php
    │       │   ├── filters-sidebar.blade.php
    │       │   └── cart-drawer.blade.php
    │       ├── home.blade.php
    │       ├── products/
    │       │   ├── index.blade.php
    │       │   └── view.blade.php
    │       ├── cart/
    │       │   └── index.blade.php
    │       ├── checkout/
    │       │   ├── index.blade.php
    │       │   └── success.blade.php
    │       ├── account/
    │       │   ├── dashboard.blade.php
    │       │   ├── profile.blade.php
    │       │   ├── wishlist.blade.php
    │       │   ├── orders/
    │       │   │   ├── index.blade.php
    │       │   │   └── view.blade.php
    │       │   └── addresses/
    │       │       └── index.blade.php
    │       ├── auth/
    │       │   ├── login.blade.php
    │       │   ├── register.blade.php
    │       │   └── forgot-password.blade.php
    │       └── errors/
    │           └── 404.blade.php
    └── Routes/
        └── shop-routes.php
```

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Build fails | Verify Node.js 18+. Delete `node_modules` and run `npm install` again. |
| Theme not showing | Confirm `PhonixServiceProvider` is in `bootstrap/providers.php` and `phonix` entry exists in `config/themes.php`. |
| Styles missing | Run `npm run build` from `packages/Webkul/Phonix/`. Check that `public/themes/shop/phonix/build/` contains output files. |
| RTL not working | Ensure the `<html>` element has `lang="ar"` or `dir="rtl"`. |
| Dark mode not working | Verify Tailwind `darkMode: "class"` in `tailwind.config.js` and that the `dark` class is toggled on `<html>`. |
| Vite HMR not connecting | Check `VITE_HOST` and `VITE_PORT` env vars. Default dev server port is `5174`. |
| Cache issues | Run `php artisan optimize:clear` after any config or provider change. |

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Follow existing code style -- use Laravel Pint for PHP, Prettier for JS/CSS
4. Add translations for both `en` and `ar` locales
5. Test in both LTR and RTL modes, and in light and dark themes
6. Submit a pull request

## License

MIT

## Credits

- [Bagisto](https://bagisto.com) by Webkul
- [Tailwind CSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)
- [GSAP](https://greensock.com/gsap/) by GreenSock
- [Swiper.js](https://swiperjs.com)
- [Google Fonts](https://fonts.google.com) -- Cairo, Inter, Poppins
