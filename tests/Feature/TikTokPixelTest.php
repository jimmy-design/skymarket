<?php

namespace Tests\Feature;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\Settings\TrackingSettingRequest;
use Botble\Ecommerce\Models\Currency;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Supports\CurrencySupport;
use Botble\Ecommerce\Supports\TikTokPixel;
use Botble\Payment\Models\Payment;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

class TikTokPixelTest extends TestCase
{
    private function configurePixel(bool $enabled = true, string $id = 'TESTPIXEL123'): void
    {
        $prefix = EcommerceHelper::getSettingPrefix();
        $settings = Mockery::mock(SettingStore::class);
        $settings->shouldReceive('get')->andReturnUsing(fn ($key, $default = null) => match ($key) {
            $prefix . 'tiktok_pixel_enabled' => $enabled,
            $prefix . 'tiktok_pixel_id' => $id,
            default => $default,
        });
        $this->app->instance(SettingStore::class, $settings);
        $currency = Mockery::mock(CurrencySupport::class);
        $currency->shouldReceive('getDefaultCurrency')->andReturn(new Currency(['title' => 'KES']));
        $this->app->instance(CurrencySupport::class, $currency);
    }

    public function test_pixel_stays_off_without_an_id_or_when_disabled(): void
    {
        $this->configurePixel(true, '');
        $this->assertSame('', app(TikTokPixel::class)->render());
        $this->configurePixel(false);
        $this->assertFalse(app(TikTokPixel::class)->isEnabled());
    }

    public function test_enabling_requires_a_safe_pixel_id(): void
    {
        $rules = (new TrackingSettingRequest())->rules();
        foreach (['', '"></script><script>alert(1)</script>'] as $id) {
            $this->assertTrue(Validator::make(['tiktok_pixel_enabled' => 1, 'tiktok_pixel_id' => $id], $rules)->fails());
        }
        $this->assertFalse(Validator::make(['tiktok_pixel_enabled' => 1, 'tiktok_pixel_id' => 'TESTPIXEL123'], $rules)->fails());
    }

    public function test_cart_payload_uses_parent_id_and_requested_quantity(): void
    {
        $this->configurePixel();
        $parent = (new Product())->forceFill(['id' => 42, 'name' => 'Phone', 'is_variation' => false]);
        $variation = (new Product())->forceFill(['id' => 99, 'is_variation' => true]);
        $info = (new ProductVariation())->forceFill(['id' => 1]);
        $info->setRelation('configurableProduct', $parent);
        $variation->setRelation('variationInfo', $info);
        $event = app(TikTokPixel::class)->addedToCart($variation, 2, 500.0);
        $this->assertSame('AddToCart', $event['event']);
        $this->assertSame('42', $event['properties']['contents'][0]['content_id']);
        $this->assertSame(2, $event['properties']['contents'][0]['quantity']);
        $this->assertSame(250.0, $event['properties']['contents'][0]['price']);
        $this->assertSame('KES', $event['properties']['currency']);
        $this->assertSame(500.0, $event['properties']['value']);
    }

    public function test_pending_payment_does_not_emit_a_purchase(): void
    {
        $this->configurePixel();
        $order = (new Order())->forceFill(['id' => 7, 'amount' => 500]);
        $order->setRelation('payment', new Payment(['status' => 'pending']));
        $pixel = app(TikTokPixel::class);
        $pixel->purchase($order);
        $this->assertStringNotContainsString('"event":"Purchase"', $pixel->render());
    }

    public function test_paid_purchase_uses_order_values_and_deduplicates(): void
    {
        $this->configurePixel();
        $product = (new Product())->forceFill(['id' => 42, 'name' => 'Phone', 'is_variation' => false]);
        $product->exists = true;
        $line = (new OrderProduct())->forceFill(['qty' => 2, 'price' => 250]);
        $line->setRelation('product', $product);
        $order = (new Order())->forceFill(['id' => 7, 'amount' => 550]);
        $order->setRelation('payment', new Payment(['status' => 'completed']));
        $order->setRelation('products', collect([$line]));
        $pixel = app(TikTokPixel::class);
        $pixel->purchase($order);
        $pixel->purchase($order);
        $html = $pixel->render();
        $this->assertSame(1, substr_count($html, '"event":"Purchase"'));
        $this->assertStringContainsString('skymarket-purchase-7', $html);
        $this->assertStringContainsString('"value":550', $html);
        $this->assertStringNotContainsString('email', $html);
    }
}
