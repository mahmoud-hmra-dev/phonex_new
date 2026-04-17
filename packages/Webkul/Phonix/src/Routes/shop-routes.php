<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Phonix Shop Routes
|--------------------------------------------------------------------------
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

    Route::get('/compare', function () {
        $ids = array_filter(
            array_map('intval', explode(',', request('ids', '')))
        );

        $products = collect();

        if (! empty($ids)) {
            $productRepository = app(\Webkul\Product\Repositories\ProductRepository::class);
            $products = collect($ids)
                ->take(4)
                ->map(fn ($id) => $productRepository->find($id))
                ->filter()
                ->values();
        }

        return view('phonix::compare.index', compact('products'));
    })->name('phonix.compare.index');

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

    // --------------------------------------------------------------------------
    // Auth POST routes — handle login & register, redirect to Phonix pages
    // --------------------------------------------------------------------------

    Route::post('/login', function () {
        $credentials = [
            'email'      => request('email'),
            'password'   => request('password'),
            'channel_id' => core()->getCurrentChannel()->id,
        ];

        if (! auth('customer')->attempt($credentials, (bool) request('remember'))) {
            return redirect()->route('phonix.auth.login')
                ->withErrors(['email' => trans('shop::app.customers.login-form.invalid-credentials')])
                ->withInput(request()->only('email'));
        }

        $customer = auth('customer')->user();

        if (! $customer->status) {
            auth('customer')->logout();
            return redirect()->route('phonix.auth.login')
                ->withErrors(['email' => trans('shop::app.customers.login-form.not-activated')])
                ->withInput(request()->only('email'));
        }

        if (! $customer->is_verified) {
            auth('customer')->logout();
            return redirect()->route('phonix.auth.login')
                ->withErrors(['email' => trans('shop::app.customers.login-form.verify-first')])
                ->withInput(request()->only('email'));
        }

        \Illuminate\Support\Facades\Event::dispatch('customer.after.login', $customer);

        return redirect()->route('phonix.home');
    })->name('phonix.auth.login.store');

    Route::post('/register', function () {
        $data = request()->validate([
            'first_name'            => 'required|string|max:255',
            'last_name'             => 'required|string|max:255',
            'email'                 => 'required|email|unique:customers,email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $customerGroupRepository = app(\Webkul\Customer\Repositories\CustomerGroupRepository::class);
        $customerRepository      = app(\Webkul\Customer\Repositories\CustomerRepository::class);

        $groupCode     = core()->getConfigData('customer.settings.create_new_account_options.default_group') ?? 'general';
        $customerGroup = $customerGroupRepository->findOneWhere(['code' => $groupCode]);

        \Illuminate\Support\Facades\Event::dispatch('customer.registration.before');

        $customer = $customerRepository->create([
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'email'             => $data['email'],
            'password'          => bcrypt($data['password']),
            'api_token'         => Str::random(80),
            'is_verified'       => ! core()->getConfigData('customer.settings.email.verification'),
            'customer_group_id' => $customerGroup->id,
            'channel_id'        => core()->getCurrentChannel()->id,
            'token'             => md5(uniqid((string) rand(), true)),
        ]);

        \Illuminate\Support\Facades\Event::dispatch('customer.registration.after', $customer);

        // Auto-login after registration
        auth('customer')->login($customer);

        return redirect()->route('phonix.home');
    })->name('phonix.auth.register.store');

    // --------------------------------------------------------------------------
    // Account pages — require authenticated customer
    // --------------------------------------------------------------------------

    Route::group(['middleware' => ['customer']], function () {
        Route::get('/account', fn () => view('phonix::account.dashboard'))->name('phonix.account.dashboard');
        Route::get('/account/orders', fn () => view('phonix::account.orders.index'))->name('phonix.account.orders');
        Route::get('/account/orders/{id}', fn ($id) => view('phonix::account.orders.view', ['id' => $id]))->name('phonix.account.orders.view');
        Route::get('/account/addresses', fn () => view('phonix::account.addresses.index'))->name('phonix.account.addresses');
        Route::get('/account/wishlist', fn () => view('phonix::account.wishlist'))->name('phonix.account.wishlist');
        Route::get('/account/profile', fn () => view('phonix::account.profile'))->name('phonix.account.profile');

        // Profile update
        Route::post('/account/profile/update', function () {
            $customer = auth('customer')->user();

            $data = request()->validate([
                'first_name'    => 'required|string|max:255',
                'last_name'     => 'required|string|max:255',
                'email'         => 'required|email|unique:customers,email,' . $customer->id,
                'phone'         => 'nullable|string|max:20',
                'gender'        => 'nullable|in:male,female,other',
                'date_of_birth' => 'nullable|date|before:today',
            ]);

            app(\Webkul\Customer\Repositories\CustomerRepository::class)->update($data, $customer->id);

            return redirect()->route('phonix.account.profile')->with('success', true);
        })->name('phonix.account.profile.update');

        // Password update
        Route::post('/account/password/update', function () {
            $customer = auth('customer')->user();

            $data = request()->validate([
                'current_password'          => 'required',
                'new_password'              => 'required|min:8|confirmed',
                'new_password_confirmation' => 'required',
            ]);

            if (! \Illuminate\Support\Facades\Hash::check($data['current_password'], $customer->password)) {
                return redirect()->route('phonix.account.profile')
                    ->withErrors(['current_password' => __('shop::app.customers.account.profile.index.unmatched')]);
            }

            app(\Webkul\Customer\Repositories\CustomerRepository::class)->update([
                'password' => bcrypt($data['new_password']),
            ], $customer->id);

            return redirect()->route('phonix.account.profile')->with('password_success', true);
        })->name('phonix.account.password.update');
    });

    // --------------------------------------------------------------------------
    // Auth pages — only for guests
    // --------------------------------------------------------------------------

    Route::group(['middleware' => ['guest:customer']], function () {
        Route::get('/login', fn () => view('phonix::auth.login'))->name('phonix.auth.login');
        Route::get('/register', fn () => view('phonix::auth.register'))->name('phonix.auth.register');
        Route::get('/forgot-password', fn () => view('phonix::auth.forgot-password'))->name('phonix.auth.forgot');
    });
});
