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

    Route::get('/products', [\Webkul\Phonix\Http\Controllers\Shop\ProductListingController::class, 'index'])
        ->name('phonix.products.index');

    Route::get('/products/{slug}', function ($slug) {
        $product = app(\Webkul\Product\Repositories\ProductRepository::class)
            ->findBySlug($slug);

        if (! $product) {
            abort(404);
        }

        return view('phonix::products.view', compact('product'));
    })->name('phonix.products.view');

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

    // Add to Cart — accepts product_id + quantity, handles simple & configurable
    Route::post('/cart/add', function () {
        $productId = (int) request('product_id');
        $quantity  = max(1, (int) request('quantity', 1));

        if (! $productId) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Invalid product.'], 422);
            }
            return redirect()->back();
        }

        $product = app(\Webkul\Product\Repositories\ProductRepository::class)
            ->with('parent')
            ->find($productId);

        if (! $product) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Product not found.'], 404);
            }
            return redirect()->back();
        }

        // Configurable products must be configured on the product page
        if ($product->type === 'configurable') {
            $productUrl = route('phonix.products.view', ['slug' => $product->url_key]);
            if (request()->expectsJson()) {
                return response()->json(['redirect' => $productUrl], 200);
            }
            return redirect($productUrl);
        }

        try {
            \Webkul\Checkout\Facades\Cart::addProduct($product, [
                'product_id' => $productId,
                'quantity'   => $quantity,
            ]);

            if (request()->expectsJson()) {
                $cart = \Webkul\Checkout\Facades\Cart::getCart();

                return response()->json([
                    'success'    => true,
                    'message'    => trans('shop::app.checkout.cart.item-add-to-cart'),
                    'redirect'   => route('phonix.cart.index'),
                    'items_qty'  => (int) ($cart?->items_qty ?? 0),
                ]);
            }

            return redirect()->route('phonix.cart.index');

        } catch (\Webkul\Product\Exceptions\InsufficientProductInventoryException $e) {
            $productUrl = route('phonix.products.view', ['slug' => $product->url_key]);
            if (request()->expectsJson()) {
                return response()->json(['error' => $e->getMessage(), 'redirect' => $productUrl], 422);
            }
            return redirect($productUrl)->withErrors(['cart' => $e->getMessage()]);

        } catch (\Exception $e) {
            $productUrl = route('phonix.products.view', ['slug' => $product->url_key]);
            if (request()->expectsJson()) {
                return response()->json(['error' => $e->getMessage(), 'redirect' => $productUrl], 422);
            }
            return redirect($productUrl);
        }
    })->name('phonix.cart.add');

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

    // Wishlist toggle — add or remove, works for guests (redirects to login)
    Route::post('/wishlist/toggle', function () {
        if (! auth('customer')->check()) {
            if (request()->expectsJson()) {
                return response()->json(['redirect' => route('phonix.auth.login')], 401);
            }
            return redirect()->route('phonix.auth.login');
        }

        $productId = (int) request('product_id');
        $customer  = auth('customer')->user();

        $wishlistRepo = app(\Webkul\Customer\Repositories\WishlistRepository::class);
        $existing     = $wishlistRepo->findOneWhere([
            'customer_id' => $customer->id,
            'product_id'  => $productId,
        ]);

        if ($existing) {
            $wishlistRepo->delete($existing->id);
            $inWishlist = false;
        } else {
            $wishlistRepo->create([
                'customer_id' => $customer->id,
                'product_id'  => $productId,
                'channel_id'  => core()->getCurrentChannel()->id,
            ]);
            $inWishlist = true;
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'in_wishlist' => $inWishlist]);
        }

        return redirect()->back();
    })->name('phonix.wishlist.toggle');

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
        Route::get('/account/reviews', fn () => view('phonix::account.reviews'))->name('phonix.account.reviews');
        Route::get('/account/profile', fn () => view('phonix::account.profile'))->name('phonix.account.profile');

        // Address CRUD — wraps Shop logic, redirects back to Phonix
        Route::post('/account/addresses', function () {
            $customer = auth('customer')->user();
            $addrRepo = app(\Webkul\Customer\Repositories\CustomerAddressRepository::class);

            request()->validate([
                'first_name' => 'required|string|max:255',
                'last_name'  => 'required|string|max:255',
                'phone'      => 'required|string|max:20',
                'address'    => 'required',
                'city'       => 'required|string|max:255',
                'country'    => 'nullable|string|max:10',
                'state'      => 'nullable|string|max:255',
                'postcode'   => 'nullable|string|max:20',
            ]);

            $address = is_array(request('address')) ? implode(PHP_EOL, array_filter(request('address'))) : request('address', '');

            \Illuminate\Support\Facades\Event::dispatch('customer.addresses.create.before');

            $customerAddress = $addrRepo->create([
                'customer_id'   => $customer->id,
                'company_name'  => request('company_name', ''),
                'first_name'    => request('first_name'),
                'last_name'     => request('last_name'),
                'email'         => request('email', $customer->email),
                'phone'         => request('phone'),
                'address'       => $address,
                'country'       => request('country', ''),
                'state'         => request('state', ''),
                'city'          => request('city'),
                'postcode'      => request('postcode', ''),
                'default_address' => 0,
            ]);

            \Illuminate\Support\Facades\Event::dispatch('customer.addresses.create.after', $customerAddress);

            return redirect()->route('phonix.account.addresses')->with('success', true);
        })->name('phonix.account.addresses.store');

        Route::post('/account/addresses/{id}', function ($id) {
            $customer = auth('customer')->user();
            $addrRepo = app(\Webkul\Customer\Repositories\CustomerAddressRepository::class);
            $existing = $addrRepo->findOneWhere(['id' => $id, 'customer_id' => $customer->id]);

            if (! $existing) {
                return redirect()->route('phonix.account.addresses');
            }

            $method = request('_method', 'PUT');

            // PATCH = set as default
            if (strtoupper($method) === 'PATCH') {
                $addrRepo->where('customer_id', $customer->id)->update(['default_address' => 0]);
                $addrRepo->update(['default_address' => 1], $id);
                return redirect()->route('phonix.account.addresses');
            }

            // PUT = update address fields
            request()->validate([
                'first_name' => 'required|string|max:255',
                'last_name'  => 'required|string|max:255',
                'phone'      => 'required|string|max:20',
                'address'    => 'required',
                'city'       => 'required|string|max:255',
                'country'    => 'nullable|string|max:10',
                'state'      => 'nullable|string|max:255',
                'postcode'   => 'nullable|string|max:20',
            ]);

            $address = is_array(request('address')) ? implode(PHP_EOL, array_filter(request('address'))) : request('address', '');

            \Illuminate\Support\Facades\Event::dispatch('customer.addresses.update.before', $id);

            $addrRepo->update([
                'company_name' => request('company_name', ''),
                'first_name'   => request('first_name'),
                'last_name'    => request('last_name'),
                'email'        => request('email', $customer->email),
                'phone'        => request('phone'),
                'address'      => $address,
                'country'      => request('country', ''),
                'state'        => request('state', ''),
                'city'         => request('city'),
                'postcode'     => request('postcode', ''),
            ], $id);

            \Illuminate\Support\Facades\Event::dispatch('customer.addresses.update.after', $existing);

            return redirect()->route('phonix.account.addresses')->with('success', true);
        })->name('phonix.account.addresses.update');

        Route::post('/account/addresses/{id}/delete', function ($id) {
            $customer = auth('customer')->user();
            $addrRepo = app(\Webkul\Customer\Repositories\CustomerAddressRepository::class);
            $existing = $addrRepo->findOneWhere(['id' => $id, 'customer_id' => $customer->id]);

            if ($existing) {
                \Illuminate\Support\Facades\Event::dispatch('customer.addresses.delete.before', $id);
                $addrRepo->delete($id);
                \Illuminate\Support\Facades\Event::dispatch('customer.addresses.delete.after', $id);
            }

            return redirect()->route('phonix.account.addresses');
        })->name('phonix.account.addresses.delete');

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
