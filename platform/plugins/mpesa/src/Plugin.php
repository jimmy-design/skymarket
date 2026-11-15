<?php

namespace Njovu\Mpesa;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Models\Setting;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public function register(): void
    {
        // Register plugin components
    }
    
    public function boot(): void
    {
        // Check if the mpesa_region_settings table exists,
        // if not, create it during plugin boot
        if (!Schema::hasTable('mpesa_region_settings')) {
            Schema::create('mpesa_region_settings', function ($table) {
                $table->id();
                $table->integer('region_id')->unique();
                $table->string('business_shortcode');
                $table->string('passkey');
                $table->string('consumer_key');
                $table->string('consumer_secret');
                $table->enum('payment_mode', ['Sandbox', 'Production'])->default('Sandbox');
                $table->timestamps();
            });
        }
    }
    
    public static function remove(): void
    {
        // Remove settings from the settings table
        Setting::query()
            ->whereIn('key', [
                'payment_via_mpesa',
                'mpesa_description',
                'after_service_registration_msg',
                'enter_credentials',
                'mpesa_business_shortcode',
                'mpesa_till_number',
                'mpesa_passkey',
                'mpesa_consumer_key',
                'mpesa_consumer_secret',
                'mpesa_mode',
            ])
            ->delete();

        // Remove old region settings from settings table
        Setting::query()
            ->where('key', 'LIKE', 'mpesa_%_region_%')
            ->delete();
            
        // Drop the mpesa_region_settings table if it exists
        if (Schema::hasTable('mpesa_region_settings')) {
            Schema::dropIfExists('mpesa_region_settings');
        }
    }
}
