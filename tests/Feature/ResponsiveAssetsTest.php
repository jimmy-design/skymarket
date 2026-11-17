<?php

namespace Tests\Feature;

use Botble\Base\Facades\Assets;
use Tests\TestCase;

class ResponsiveAssetsTest extends TestCase
{
    public function test_admin_and_vendor_assets_include_shared_responsive_styles(): void
    {
        $this->assertStringContainsString('css/skymarket-responsive.css', Assets::renderHeader());
    }

    public function test_checkout_includes_shared_responsive_styles(): void
    {
        $header = apply_filters('ecommerce_checkout_header', '');
        $this->assertStringContainsString('css/skymarket-responsive.css', $header);
    }
}
