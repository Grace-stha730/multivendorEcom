<?php

namespace App\Services\Coupon;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Stateless coupon eligibility and discount calculations.
 * UI components should use the returned result arrays; only order placement
 * should increment used_count or create CouponRedemption records.
 */
class CouponService
{
    /**
     * Vendor coupons that can be used against one exact product.
     *
     * A product coupon must match the product ID. A category coupon must have no
     * product ID and must match both the product's shop and category.
     *
     * @return Collection<int, Coupon>
     */
    public function eligibleVendorCouponsForProduct(Product $product): Collection
    {
        return $this->availableCoupons()
            ->whereNotNull('shop_id')
            ->where(function ($query) use ($product) {
                $query->where(function ($productCoupon) use ($product) {
                    $productCoupon->where('shop_id', $product->shop_id)
                        ->where('product_id', $product->id);
                })->orWhere(function ($categoryCoupon) use ($product) {
                    $categoryCoupon->where('shop_id', $product->shop_id)
                        ->whereNull('product_id')
                        ->where('category_id', $product->category_id);
                });
            })
            ->get();
    }

    /** @return Collection<int, Coupon> */
    public function eligibleAdminCouponsForCategories(Collection $categoryIds): Collection
    {
        return $this->availableCoupons()
            ->whereNull('shop_id')
            ->whereNull('product_id')
            ->whereIn('category_id', $categoryIds->filter()->unique()->values())
            ->get();
    }

    private function availableCoupons()
    {
        return Coupon::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            // A zero usage_limit retains this project's existing "unlimited" convention.
            ->where(fn ($query) => $query->where('usage_limit', 0)->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    /**
     * Calculates one coupon against one eligible subtotal without persisting it.
     * $oneTimePerUser can be false for coupons that permit repeat use by a user.
     *
     * @return array{applied: bool, discount: float, reason: ?string}
     */
    public function calculateDiscount(Coupon $coupon, float $subtotal, ?User $user = null, bool $oneTimePerUser = true): array
    {
        if (! $coupon->isAvailable()) {
            return $this->failed('This coupon is no longer available.');
        }

        if ($oneTimePerUser && $user && CouponRedemption::where('coupon_id', $coupon->id)->where('user_id', $user->id)->exists()) {
            return $this->failed('You have already used this coupon.');
        }

        if ($subtotal < (float) $coupon->min_order_amount) {
            return $this->failed('Minimum order of Rs. ' . number_format($coupon->min_order_amount, 2) . ' is required for this coupon.');
        }

        if ($subtotal < (float) $coupon->min_item_price) {
            return $this->failed('Minimum item subtotal of Rs. ' . number_format($coupon->min_item_price, 2) . ' is required for this coupon.');
        }

        $discount = $coupon->type === 'percent'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        if ($coupon->type === 'percent' && $coupon->max_discount_amount !== null) {
            $discount = min($discount, (float) $coupon->max_discount_amount);
        }

        return ['applied' => true, 'discount' => round(min($discount, $subtotal), 2), 'reason' => null];
    }

    /**
     * Splits an admin category coupon across matching line amounts proportionally.
     * $lineSubtotals must be [lineKey => subtotal]. Zero-value lines get no share.
     *
     * @return array{applied: bool, discount: float, allocations: array<int|string, float>, reason: ?string}
     */
    public function calculateProportionalDiscount(Coupon $coupon, array $lineSubtotals, ?User $user = null, bool $oneTimePerUser = true): array
    {
        $eligibleSubtotal = array_sum($lineSubtotals);
        $result = $this->calculateDiscount($coupon, (float) $eligibleSubtotal, $user, $oneTimePerUser);
        if (! $result['applied']) return $result + ['allocations' => []];

        $allocations = [];
        $remaining = $result['discount'];
        $keys = array_keys(array_filter($lineSubtotals, fn ($subtotal) => $subtotal > 0));
        foreach ($keys as $index => $key) {
            $share = $index === array_key_last($keys)
                ? $remaining
                : round($result['discount'] * ($lineSubtotals[$key] / $eligibleSubtotal), 2);
            $allocations[$key] = $share;
            $remaining -= $share;
        }

        return $result + ['allocations' => $allocations];
    }

    private function failed(string $reason): array
    {
        return ['applied' => false, 'discount' => 0.0, 'reason' => $reason];
    }
}
