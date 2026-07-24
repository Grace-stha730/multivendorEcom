<?php

namespace App\Services\Catalog;

use App\Models\Product;
use App\Models\productRating;
use App\Models\Vendor;
use Illuminate\Support\Collection;

class WeightedRatingService
{
    public const MINIMUM_REVIEWS = 10;

    public function calculate(int $reviewCount, ?float $averageRating, float $platformAverage, int $minimumReviews = self::MINIMUM_REVIEWS): float
    {
        if ($reviewCount === 0) {
            return round($platformAverage, 2);
        }

        $averageRating ??= 0.0;

        return round((($reviewCount / ($reviewCount + $minimumReviews)) * $averageRating)
            + (($minimumReviews / ($reviewCount + $minimumReviews)) * $platformAverage), 2);
    }

    public function rankedProducts(?Collection $products = null): Collection
    {
        $products ??= Product::with(['vendor', 'firstImage'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get();

        $platformAverage = (float) (productRating::avg('rating') ?? 0);

        return $products->map(function (Product $product) use ($platformAverage) {
            $product->weighted_rating = $this->calculate(
                (int) ($product->reviews_count ?? 0),
                $product->reviews_avg_rating === null ? null : (float) $product->reviews_avg_rating,
                $platformAverage,
            );

            return $product;
        })->sortByDesc('weighted_rating')->values();
    }

    public function vendorRating(Vendor $vendor): float
    {
        $reviews = productRating::query()
            ->whereIn('product_id', $vendor->products()->select('id'));

        return $this->calculate(
            (int) $reviews->count(),
            $reviews->avg('rating'),
            (float) (productRating::avg('rating') ?? 0),
        );
    }
}
