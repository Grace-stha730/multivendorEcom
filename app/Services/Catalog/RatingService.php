<?php

namespace App\Services\Catalog;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RatingService
{
    public const MINIMUM_RATINGS = 5;

    public function weightedRating(Model $model, ?int $minimumRatings = null): float
    {
        $minimumRatings ??= (int) config('recommendations.ratings.minimum_ratings', self::MINIMUM_RATINGS);
        $ratings = $model->ratings();
        $count = (int) $ratings->count();
        $average = (float) ($ratings->avg('rating') ?? 0);
        $platformMean = $this->platformMean($model instanceof Shop ? Shop::class : Product::class);

        // Bayesian weighting prevents one 5-star review from outranking 500 reviews averaging 4.7:
        // low-count ratings are pulled toward the platform mean until enough reviews make them reliable.
        return round((($count / ($count + $minimumRatings)) * $average)
            + (($minimumRatings / ($count + $minimumRatings)) * $platformMean), 2);
    }

    public function platformMean(string $modelClass): float
    {
        $key = $modelClass === Shop::class ? 'ratings.platform_mean.shops' : 'ratings.platform_mean.products';

        return (float) Cache::remember($key, now()->addSeconds((int) config('recommendations.ratings.platform_mean_ttl', 3600)), function () use ($modelClass) {
            // C is the mean of each rated model's mean, so one heavily-reviewed item
            // does not disproportionately define the platform baseline.
            $averages = $modelClass::query()
                ->withAvg('ratings', 'rating')
                ->get()
                ->pluck('ratings_avg_rating')
                ->filter(fn ($average) => $average !== null);

            return (float) ($averages->avg() ?? 0);
        });
    }

    public function clearPlatformMeans(): void
    {
        Cache::forget('ratings.platform_mean.products');
        Cache::forget('ratings.platform_mean.shops');
    }
}
