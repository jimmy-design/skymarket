<?php

namespace Botble\Ecommerce\Supports;

use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Payment\Enums\PaymentStatusEnum;

class TikTokPixel
{
    protected array $events = [];
    protected array $purchases = [];

    public function isEnabled(): bool
    {
        return (bool) get_ecommerce_setting('tiktok_pixel_enabled', false)
            && preg_match('/^[A-Za-z0-9_-]{5,64}$/D', (string) get_ecommerce_setting('tiktok_pixel_id')) === 1;
    }

    public function productContent(Product $product, int $quantity = 1, ?float $price = null): array
    {
        return [
            'content_id' => (string) $product->original_product->getKey(),
            'content_name' => $product->original_product->name,
            'quantity' => $quantity,
            'price' => $price ?? $product->price()->getPrice(),
        ];
    }

    public function viewProduct(Product $product): void
    {
        if ($this->isEnabled()) {
            $content = $this->productContent($product);
            $this->record('ViewContent', [$content], $content['price']);
        }
    }

    public function addedToCart(Product $product, int $quantity, float $value): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        return $this->record('AddToCart', [$this->productContent($product, $quantity, $quantity > 0 ? $value / $quantity : 0)], $value);
    }

    public function beginCheckout(float $value): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $contents = [];
        foreach (Cart::instance('cart')->content() as $item) {
            if ($product = Product::query()->find($item->id)) {
                $contents[] = $this->productContent($product, (int) $item->qty, (float) $item->price);
            }
        }

        $this->record('InitiateCheckout', $contents, $value);
    }

    public function purchase(Order $order): void
    {
        if (! $this->isEnabled() || ! is_plugin_active('payment')
            || $order->payment->status != PaymentStatusEnum::COMPLETED
            || isset($this->purchases[$order->getKey()])) {
            return;
        }

        $this->purchases[$order->getKey()] = true;

        $contents = [];
        foreach ($order->products as $item) {
            if ($item->product->exists) {
                $contents[] = $this->productContent($item->product, (int) $item->qty, (float) $item->price);
            }
        }

        $this->record('Purchase', $contents, (float) $order->amount, 'skymarket-purchase-' . $order->getKey());
    }

    public function search(string $query): void
    {
        if ($this->isEnabled() && trim($query) !== '') {
            $this->events[] = ['event' => 'Search', 'properties' => ['query' => mb_substr($query, 0, 200)]];
        }
    }

    protected function record(string $event, array $contents, float $value, ?string $eventId = null): array
    {
        return $this->events[] = [
            'event' => $event,
            'properties' => [
                'content_type' => 'product',
                'contents' => $contents,
                'currency' => cms_currency()->getDefaultCurrency()->title,
                'value' => round($value, 2),
            ],
            'event_id' => $eventId,
        ];
    }

    public function render(): string
    {
        if (! $this->isEnabled()) {
            return '';
        }

        return view('plugins/ecommerce::tracking.tiktok', [
            'pixelId' => get_ecommerce_setting('tiktok_pixel_id'),
            'events' => $this->events,
        ])->render();
    }
}
