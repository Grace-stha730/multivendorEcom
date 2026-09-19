<?php

namespace App\Services\Cart;

use App\Models\Product;

class CartPricingService
{
    public function discountedUnitPrice(Product $product): float
    {
        $productDiscount = $product->discount_amount
            ?? ((float) $product->price * ((float) ($product->discount ?? 0) / 100));

        return max(0, (float) $product->price - (float) $productDiscount);
    }

    /**
     * Calculates a line before coupon discount. Structured variant selections
     * carry their own quantities, so their sum is the line quantity.
     */
    public function lineSubtotal(Product $product, array $selectedVariants, int $quantity): float
    {
        $basePrice = $this->discountedUnitPrice($product);
        $variants = $selectedVariants['variants'] ?? [];

        if ($variants) {
            return (float) collect($variants)->sum(
                fn (array $variant) => ($basePrice + (float) ($variant['price_extra'] ?? 0)) * (int) $variant['quantity']
            );
        }

        return $basePrice * max(1, $quantity);
    }

    public function finalLineTotal(float $lineSubtotal, float $couponDiscount = 0): float
    {
        return max(0, $lineSubtotal - $couponDiscount);
    }
}
