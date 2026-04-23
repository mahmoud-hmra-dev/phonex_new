# Overnight work — 2026-04-23

## What you asked for

1. Kill the per-category page (`/phonix/category/{slug}`) and make **one** products
   page, filterable via `?category_ids[]=…&price=…&sort=…&limit=…`.
2. Fix the locale switcher: the second switch produced `/phonix?locale=ar?locale=en`.
3. Make the theme **feel like a SPA** — no full-page reloads when clicking around.

All three are done, built, and smoke-tested against the running site.

---

## What I did

### 1. One products page, no category pages

- Deleted the `/phonix/category/{slug}` route and the
  `Resources/views/categories/view.blade.php` shim.
- Rewrote every category link in the theme to
  `route('phonix.products.index', ['category_ids' => [$category->id]])`.
  Touched: `categories-grid`, `hero`, `footer`, `header` (desktop + mobile nav),
  and the product-detail breadcrumb.
- `ProductListingController` now auto-resolves `$currentCategory` when exactly
  one `category_ids[]` is passed, so the page heading + breadcrumb keep showing
  the category name.
- The category filter panel is no longer hidden when a category is active —
  otherwise applying a price/brand filter would silently drop the category
  from the form.
- Every "Clear filters" link resets to `route('phonix.products.index')`.

**Verify:** visit
`http://127.0.0.1:8000/phonix/products?category_ids%5B%5D=2&price=35%2C2200&sort=created_at-desc&limit=12`
— it renders with the Samsung category, price range applied, and filters show
the correct checkboxes. The old URL
`http://127.0.0.1:8000/phonix/category/samsung-phones` 301s to Bagisto's
default catalog fallback (no 404 for users with bookmarked old links).

### 2. Locale switcher — the `?locale=ar?locale=en` bug

**Root cause.** Bagisto's shop `Locale` middleware
(`packages/Webkul/Shop/src/Http/Middleware/Locale.php`) calls
`unset($request['locale'])` after reading it. That empties the query bag, but
**the raw `QUERY_STRING`** still contains `locale=ar`. Laravel's
`request()->fullUrlWithQuery(['locale' => 'en'])` then takes its
empty-query branch (`count($this->query()) > 0` is false) and appends
`?locale=en` to `fullUrl()`, which is `/phonix?locale=ar`. Result:
`/phonix?locale=ar?locale=en`.

**Fix.** New helper file `packages/Webkul/Phonix/src/helpers.php`:

```php
phonix_switch_url(array $overrides): string   // url()->current() + merged query
phonix_locale_url(string $code): string       // sugar over the above
```

The helpers always rebuild from `url()->current()` (no stale query) and merge
`request()->query->all()` (which is now always sanitised) with the override.
Registered via `PhonixServiceProvider::register()`.

Every `request()->fullUrlWithQuery(['locale' => …])` and the currency
dropdown's `fullUrlWithQuery(['currency' => …])` were replaced.

**Verify:** Switch EN → AR → EN; both hops produce clean URLs:
- `http://127.0.0.1:8000/phonix?locale=ar` → switcher renders `…?locale=en`
- Filters are preserved when switching locale on the products page (tested —
  `?category_ids[]=2&price=35,2200&locale=ar` ↔ `…&locale=en`).

### 3. SPA feel via Hotwired Turbo Drive

The theme is still Blade/PHP — no Vue/React rewrite. Turbo Drive
intercepts link clicks and form submits, fetches the destination via `fetch()`,
and swaps `<body>` without a full reload. End result: no white flash, scroll
stays smooth, Alpine state on `<html>` (dark-mode, etc.) survives.

#### JS pipeline

- `packages/Webkul/Phonix/package.json`: added `@hotwired/turbo` dep,
  installed via `npm install`.
- `src/Resources/assets/js/app.js`: rewritten.
  - `import "@hotwired/turbo"` + `Turbo.setProgressBarDelay(150)`.
  - Alpine still starts once at boot — it auto-initialises `x-data` inside
    swapped body content via MutationObserver, so no manual re-init needed.
  - `animations.js` and `swiper-init.js` now expose `init()` / `destroy()`.
    `app.js` calls `init` on `turbo:load` and `destroy` on `turbo:before-cache`
    so ScrollTriggers + Swiper instances don't leak across visits.
  - New global helpers on `window.phonix`:
    - `updateCartBadge(count)` — injects / updates / removes the badge on the
      cart icon in place.
    - `toast(message, type)` — slide-in toast used by add-to-cart / wishlist.
- `animations.js` and `swiper-init.js`: refactored to expose lifecycle and
  track ScrollTriggers / Swiper instances for clean teardown. Sticky-nav and
  product-card hover listeners are guarded with a `dataset.gsap*Init` flag
  so they don't double-bind if the same element re-appears.

#### Layout + chrome

`components/layouts/index.blade.php`:
- Header re-renders on every visit (keeps `$cartItemCount` fresh) — this is
  fine, Turbo swaps body in place with no flash.
- Footer, compare-bar, and a new `#phonix-toasts` host are wrapped in
  `data-turbo-permanent` so they survive navigation (no re-mount, no flicker).

`header/index.blade.php`: cart icon now carries
- `data-cart-badge-wrap` on the `<a>`
- `data-cart-icon` on the `<svg>`
- `data-cart-badge` on the count `<span>`

The JS `updateCartBadge` helper finds these and inserts / updates / removes
the badge without any navigation.

#### Cart + wishlist — stay in page

- **`Routes/shop-routes.php`**: the `/phonix/cart/add` JSON response now
  includes `items_qty` (the current cart badge count).
- **`components/product-card.blade.php`**: add-to-cart was doing
  `document.createElement('form'); form.submit();` → full page reload. Now it
  `fetch`es the JSON endpoint, bumps the badge, and shows a toast.
- **`products/index.blade.php`**: same rewrite for the listing page's grid +
  list cards. Same JSON flow, same badge + toast.
- **`products/view.blade.php`**: add-to-cart now goes to our Phonix route
  (was hitting `/api/checkout/cart`), with JSON + badge + toast. Wishlist
  now uses `phonix.wishlist.toggle` for consistency with the rest of the
  theme (previously hit `/api/customer/wishlist`).

Configurable products still redirect to the options picker page via
`Turbo.visit(data.redirect)` (no reload — it's a Turbo-soft navigation).

#### Filters feel instant

- `products/index.blade.php` filter form got `data-turbo-action="replace"` so
  filter tweaks don't bloat the history stack.
- **All** `$el.form.submit()` calls in the filter UI were changed to
  `$el.form.requestSubmit()`. Native `form.submit()` does NOT dispatch the
  `submit` event — Turbo can't see it and would fall back to a full reload.
  Same fix for the `removeChip()` and price-clear handlers.

#### POST forms that redirect — opt out of Turbo

Turbo expects **303** on non-GET redirects. Laravel defaults to **302**.
Rather than patching every controller, I marked the forms that server-redirect
with `data-turbo="false"`:

- `auth/login.blade.php`, `auth/register.blade.php`
- `account/profile.blade.php` (profile + password update)
- `account/addresses/index.blade.php` (PATCH set-default, DELETE,
  and the two dynamic JS-built forms via `f.setAttribute('data-turbo','false')`)
- `components/account/layout.blade.php` (logout)
- `components/layouts/header/index.blade.php` (logout)
- `components/layouts/header/mobile-nav.blade.php` (logout)
- `components/layouts/footer/index.blade.php` (newsletter)

These fall back to traditional submits + full reload (same as before —
nothing regressed), while every link click is still Turbo-driven.

---

## Files touched

### New
- `packages/Webkul/Phonix/src/helpers.php`
- `OVERNIGHT_REPORT.md` ← this file

### Modified
- `packages/Webkul/Phonix/package.json`
- `packages/Webkul/Phonix/src/Providers/PhonixServiceProvider.php`
- `packages/Webkul/Phonix/src/Http/Controllers/Shop/ProductListingController.php`
- `packages/Webkul/Phonix/src/Routes/shop-routes.php`
- `packages/Webkul/Phonix/src/Resources/assets/js/app.js`
- `packages/Webkul/Phonix/src/Resources/assets/js/animations.js`
- `packages/Webkul/Phonix/src/Resources/assets/js/swiper-init.js`
- `packages/Webkul/Phonix/src/Resources/views/components/layouts/index.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/components/layouts/header/index.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/components/layouts/header/mobile-nav.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/components/layouts/footer/index.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/components/categories-grid.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/components/hero.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/components/product-card.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/components/account/layout.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/products/index.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/products/view.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/auth/login.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/auth/register.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/account/profile.blade.php`
- `packages/Webkul/Phonix/src/Resources/views/account/addresses/index.blade.php`

### Deleted
- `packages/Webkul/Phonix/src/Resources/views/categories/view.blade.php`

### Rebuilt
- `public/themes/shop/phonix/build/` — `npm run build` produced a fresh
  manifest and `app-*.js` bundle containing Turbo (`turbo:load`, `hotwire`
  strings grep-verified in the compiled JS).

---

## How to verify in the browser

1. **Dev server**
   ```bash
   php artisan serve   # :8000
   ```
   (If the assets look stale: `php artisan optimize:clear`, then
   `cd packages/Webkul/Phonix && npm run build`.)

2. **SPA feel**
   - Open DevTools → Network.
   - Click around: home → products → product detail → back to home.
   - You should see XHR-style `fetch` documents, not full navigations. The
     header **does not re-mount** (no flash). The footer / compare bar /
     toast host physically survive the swap (they're `data-turbo-permanent`).
   - A progress bar appears at the top of the page if a navigation takes
     >150ms.

3. **Category → products**
   - Click any category in the header / footer / hero / categories-grid.
   - URL becomes `/phonix/products?category_ids[]=<id>`.
   - Breadcrumb + page title still show the category name.
   - Untick the category in the sidebar → filter reapplies, URL drops it.

4. **Locale switcher**
   - Top-bar → EN → AR. URL: `/phonix?locale=ar`. Switch to EN again.
     URL becomes `/phonix?locale=en`, **never** `?locale=ar?locale=en`.
   - Do the same on `/phonix/products?category_ids[]=2&price=35,2200` —
     filters survive the locale swap.

5. **Cart — stay in page**
   - On the home page or the products listing, click any card's "Add to Cart"
     button.
   - You should see:
     - Button spinner, then a **green slide-in toast** "Product added to cart".
     - The **cart badge** (top-right) appears (or increments) with a tiny
       scale pulse.
     - URL does **not** change. Page does not reload.
   - Open cart page — item is there, quantities match the badge.

6. **Wishlist — stay in page**
   - On a product card, click the heart icon. Toggles instantly with a toast
     (once you're logged in). Guests are Turbo-visit'd to the login page.

7. **Forms that should NOT be SPA** (by design — they redirect with 302)
   - Login, register, profile update, password update, address save / delete,
     set-default-address, logout, newsletter — these do a traditional submit +
     full reload. Everything still works.

---

## Caveats + follow-ups (for a second pass when you have time)

- **Turbo form-redirect strictness.** If you later want login / register /
  profile-update to also feel SPA, patch those controller responses to return
  `303 See Other` instead of `302`. Then you can drop the `data-turbo="false"`
  on those forms. It's a tiny diff per controller
  (`redirect()->route(...)->setStatusCode(303)`).
- **`Turbo.setProgressBarDelay(150)`.** Tweak if the progress bar feels too
  eager or too lazy. Turbo's default is 500ms.
- **Configurable product "Add to cart"** still needs the user to pick RAM /
  storage on the product page — there's no cart-add from the card for those.
  That's the correct behavior (prevents adding an unconfigured SKU).
- **Payment gateway redirects.** If/when a payment gateway redirects to an
  external domain (Stripe / PayU / Razorpay), mark the "Place order" form
  with `data-turbo="false"` — they're in Bagisto's own views so the theme
  doesn't need changes today, but worth remembering if you wire a custom
  gateway inside Phonix later.
- **Opt-in Turbo only.** If Turbo ever causes weird behavior on a single
  page, you can opt that `<body>` out with `<meta name="turbo-visit-control"
  content="reload">` in the `@stack('meta')` of that view.
- **Debug.** `window.Turbo.session.drive = false` in the console temporarily
  disables Turbo for the current session — useful if you want to compare
  behavior vs. full-reload navigation.

---

## Commands for tomorrow

```bash
# Start dev server (if not already running)
php artisan serve

# If you change any Blade views, caches auto-invalidate on request.
# If you change controllers / routes / config:
php artisan optimize:clear

# If you change JS / CSS:
cd packages/Webkul/Phonix && npm run build

# Run the theme's dev watcher (HMR) instead of build for faster iteration:
cd packages/Webkul/Phonix && npm run dev
```

Sleep well. — Claude
