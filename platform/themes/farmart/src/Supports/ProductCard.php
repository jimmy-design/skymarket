<?php

namespace Theme\Farmart\Supports;

use Botble\Ecommerce\Models\Product;

class ProductCard
{
    public static function price(Product $product): string
    {
        $price = $product->price();
        $minimum = $price->getPriceMinimum();
        $maximum = $price->getPriceMaximum();
        $format = function (float $amount): string {
            $formatted = format_price($amount);
            if (get_application_currency()->title === 'KES') {
                $formatted = preg_replace('/^(?:KES|KSh|Ksh)\s*/u', 'Ksh ', $formatted);
                $formatted = preg_replace('/\.00$/', '', $formatted);
            }

            return $formatted;
        };

        if (round($minimum, 2) === round($maximum, 2)) {
            return $format($minimum);
        }

        $lower = $format($minimum);
        $upper = $format($maximum);
        if (get_application_currency()->title === 'KES') {
            $upper = preg_replace('/^Ksh\s*/', '', $upper);
        }

        return $lower . '–' . $upper;
    }
}
