<?php

namespace Njovu\Mpesa\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MpesaServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        // Load the plugin's routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // Check if the 'payment' plugin is active
        if (is_plugin_active('payment')) {
            // Load and publish plugin-specific resources
            $this->setNamespace('plugins/mpesa')
                ->loadAndPublishConfigurations(['general']) // Load configuration files
                ->loadHelpers() // Load helper functions
                ->loadRoutes() // Load general routes
                ->loadAndPublishViews() // Load and publish view files
                ->loadAndPublishTranslations() // Load and publish translation files
                ->loadMigrations() // Load migrations
                ->publishAssets(); // Publish assets like JavaScript and CSS files
                
            $this->publishes([
                __DIR__ . '/../../resources/js' => public_path('vendor/core/plugins/mpesa/js'),
            ], 'mpesa-assets');
            
            // Register the HookServiceProvider for additional functionality
            $this->app->register(HookServiceProvider::class);
        }
    }
}