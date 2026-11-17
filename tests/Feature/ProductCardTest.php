<?php

namespace Tests\Feature;

use Botble\Ecommerce\Facades\Currency as CurrencyFacade;
use Botble\Ecommerce\Models\Currency;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Supports\CurrencySupport;
use Botble\Ecommerce\ValueObjects\ProductPrice;
use Botble\Setting\Supports\SettingStore;
use Mockery;
use Tests\TestCase;
use Theme\Farmart\Supports\ProductCard;

class ProductCardTest extends TestCase
{
    private function currency(string $code = 'KES', string $symbol = 'KES', float $rate = 1): void
    {
        config(['plugins.ecommerce.general.display_big_money_in_million_billion' => false]);
        $settings = Mockery::mock(SettingStore::class);
        $settings->shouldReceive('get')->andReturnUsing(fn ($key, $default = null) => $default);
        $this->app->instance(SettingStore::class, $settings);
        $currency = new Currency(['title' => $code, 'symbol' => $symbol, 'is_prefix_symbol' => true,
            'is_default' => $rate === 1.0, 'exchange_rate' => $rate, 'decimals' => 2]);
        $support = Mockery::mock(CurrencySupport::class);
        $support->shouldReceive('getApplicationCurrency', 'getDefaultCurrency')->andReturn($currency);
        CurrencyFacade::swap($support);
    }

    private function product(float $minimum, float $maximum): Product
    {
        $price = Mockery::mock(ProductPrice::class);
        $price->shouldReceive('getPriceMinimum')->andReturn($minimum);
        $price->shouldReceive('getPriceMaximum')->andReturn($maximum);
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('price')->andReturn($price);

        return $product;
    }

    public function test_equal_variant_prices_display_as_one_price(): void
    {
        $this->currency();
        $this->assertSame('Ksh 254', ProductCard::price($this->product(254, 254)));
    }

    public function test_real_variant_range_has_one_currency_label(): void
    {
        $this->currency();
        $this->assertSame('Ksh 8,820–18,709', ProductCard::price($this->product(8820, 18709)));
    }

    public function test_fractional_prices_are_preserved(): void
    {
        $this->currency();
        $this->assertSame('Ksh 254.50', ProductCard::price($this->product(254.5, 254.5)));
    }

    public function test_other_currencies_keep_their_configured_format(): void
    {
        $this->currency('USD', '$');
        $this->assertSame('$1,234.50', ProductCard::price($this->product(1234.5, 1234.5)));
    }

    public function test_currency_conversion_is_applied_once(): void
    {
        $this->currency('KES', 'KES', 2);
        $this->assertSame('Ksh 200–400', ProductCard::price($this->product(100, 200)));
    }
}
