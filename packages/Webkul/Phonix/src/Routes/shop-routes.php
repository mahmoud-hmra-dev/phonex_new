<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Phonix Shop Routes
|--------------------------------------------------------------------------
|
| Routes for the Phonix premium electronics store theme.
|
*/

Route::group(['prefix' => 'phonix'], function () {
    Route::get('/', function () {
        return view('phonix::home');
    })->name('phonix.home');

    Route::get('/products', function () {
        return view('phonix::products.index');
    })->name('phonix.products.index');

    Route::get('/products/{slug}', function ($slug) {
        $product = app(\Webkul\Product\Repositories\ProductRepository::class)
            ->findBySlug($slug);

        if (! $product) {
            abort(404);
        }

        return view('phonix::products.view', compact('product'));
    })->name('phonix.products.view');

    Route::get('/category/{slug}', function ($slug) {
        $category = app(\Webkul\Category\Repositories\CategoryRepository::class)
            ->findBySlug($slug);

        if (! $category) {
            abort(404);
        }

        return view('phonix::categories.view', compact('category'));
    })->name('phonix.categories.view');

    Route::get('/cart', function () {
        return view('phonix::cart.index');
    })->name('phonix.cart.index');

    Route::get('/checkout', function () {
        $cart = \Webkul\Checkout\Facades\Cart::getCart();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('phonix.cart.index');
        }

        return view('phonix::checkout.index');
    })->name('phonix.checkout.index');

    Route::get('/checkout/success', function () {
        return view('phonix::checkout.success');
    })->name('phonix.checkout.success');

    // Account pages — require authenticated customer
    Route::group(['middleware' => ['customer']], function () {
        Route::get('/account', fn () => view('phonix::account.dashboard'))->name('phonix.account.dashboard');
        Route::get('/account/orders', fn () => view('phonix::account.orders.index'))->name('phonix.account.orders');
        Route::get('/account/orders/{id}', fn ($id) => view('phonix::account.orders.view', ['id' => $id]))->name('phonix.account.orders.view');
        Route::get('/account/addresses', fn () => view('phonix::account.addresses.index'))->name('phonix.account.addresses');
        Route::get('/account/wishlist', fn () => view('phonix::account.wishlist'))->name('phonix.account.wishlist');
        Route::get('/account/profile', fn () => view('phonix::account.profile'))->name('phonix.account.profile');
    });

    // Auth pages — only for guests
    Route::group(['middleware' => ['guest:customer']], function () {
        Route::get('/login', fn () => view('phonix::auth.login'))->name('phonix.auth.login');
        Route::get('/register', fn () => view('phonix::auth.register'))->name('phonix.auth.register');
        Route::get('/forgot-password', fn () => view('phonix::auth.forgot-password'))->name('phonix.auth.forgot');
    });
});
