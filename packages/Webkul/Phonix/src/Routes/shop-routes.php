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
        return view('phonix::products.view', ['slug' => $slug]);
    })->name('phonix.products.view');

    Route::get('/cart', function () {
        return view('phonix::cart.index');
    })->name('phonix.cart.index');

    Route::get('/checkout', function () {
        return view('phonix::checkout.index');
    })->name('phonix.checkout.index');

    Route::get('/checkout/success', function () {
        return view('phonix::checkout.success');
    })->name('phonix.checkout.success');

    // Account pages
    Route::get('/account', fn () => view('phonix::account.dashboard'))->name('phonix.account.dashboard');
    Route::get('/account/orders', fn () => view('phonix::account.orders.index'))->name('phonix.account.orders');
    Route::get('/account/orders/{id}', fn ($id) => view('phonix::account.orders.view', ['id' => $id]))->name('phonix.account.orders.view');
    Route::get('/account/addresses', fn () => view('phonix::account.addresses.index'))->name('phonix.account.addresses');
    Route::get('/account/wishlist', fn () => view('phonix::account.wishlist'))->name('phonix.account.wishlist');
    Route::get('/account/profile', fn () => view('phonix::account.profile'))->name('phonix.account.profile');

    // Auth pages
    Route::get('/login', fn () => view('phonix::auth.login'))->name('phonix.auth.login');
    Route::get('/register', fn () => view('phonix::auth.register'))->name('phonix.auth.register');
    Route::get('/forgot-password', fn () => view('phonix::auth.forgot-password'))->name('phonix.auth.forgot');
});
