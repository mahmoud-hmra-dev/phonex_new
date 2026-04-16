<?php

declare(strict_types=1);

namespace Webkul\Phonix\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PhonixServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'phonix');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'phonix');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'phonix');

        $this->loadRoutes();
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/themes.php',
            'themes.shop.phonix'
        );

        $this->publishes([
            dirname(__DIR__).'/Config/themes.php' => config_path('phonix-themes.php'),
        ], 'phonix-config');
    }

    /**
     * Load routes.
     */
    protected function loadRoutes(): void
    {
        Route::middleware('web')->group(__DIR__.'/../Routes/shop-routes.php');
    }
}
