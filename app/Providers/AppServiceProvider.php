<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function (): void {
            $stylesheet = public_path('css/skymarket-responsive.css');
            if (! is_file($stylesheet)) {
                return;
            }

            $url = asset('css/skymarket-responsive.css') . '?v=' . filemtime($stylesheet);
            // Admin and vendor screens use Assets; storefront pages use Theme assets.
            \Botble\Base\Facades\Assets::addStylesDirectly([$url]);
            add_filter('ecommerce_checkout_header', fn (?string $html) => $html . '<link rel="stylesheet" href="' . e($url) . '">', 1000);
        });
    }
}
