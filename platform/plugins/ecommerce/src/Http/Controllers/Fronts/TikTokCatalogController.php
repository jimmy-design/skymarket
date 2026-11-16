<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Supports\TikTokPixel;
use Botble\Media\Facades\RvMedia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TikTokCatalogController extends BaseController
{
    public function __invoke(TikTokPixel $pixel): StreamedResponse
    {
        abort_unless($pixel->isEnabled(), 404);
        $currency = cms_currency()->getDefaultCurrency()->title;

        return response()->stream(function () use ($currency) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['sku_id', 'title', 'description', 'availability', 'condition', 'price', 'link', 'image_link', 'brand'], ',', '"', '');

            Product::query()
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->where('is_variation', false)
                ->with(['slugable', 'brand', 'defaultVariation.product'])
                ->chunkById(200, function ($products) use ($output, $currency) {
                    foreach ($products as $product) {
                        if (! $product->image) {
                            continue;
                        }

                        $pricedProduct = $product->defaultVariation->product->exists
                            ? $product->defaultVariation->product : $product;
                        $description = trim(html_entity_decode(strip_tags((string) ($product->description ?: $product->name)), ENT_QUOTES, 'UTF-8'));
                        $row = [
                            (string) $product->getKey(),
                            $product->name,
                            mb_substr($description, 0, 5000),
                            $pricedProduct->isOutOfStock() ? 'out of stock' : 'in stock',
                            'new',
                            number_format($pricedProduct->price()->getPrice(), 2, '.', '') . ' ' . $currency,
                            $product->url,
                            RvMedia::getImageUrl($product->image),
                            $product->brand->name ?: 'Unbranded',
                        ];

                        // Prevent formulas when the public CSV is opened in a spreadsheet.
                        $row = array_map(fn ($value) => preg_match('/^[=+@\-\t\r]/u', (string) $value) ? "'" . $value : $value, $row);
                        fputcsv($output, $row, ',', '"', '');
                    }
                });
            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="tiktok-products.csv"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store',
        ]);
    }
}
