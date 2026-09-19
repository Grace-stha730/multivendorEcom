<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Shop;
use App\Services\Catalog\RatingService;
use Illuminate\Console\Command;

class RecalculateRatings extends Command
{
    protected $signature = 'ratings:recalculate';
    protected $description = 'Recalculate and persist Bayesian weighted ratings for products and shops';

    public function handle(RatingService $ratings): int
    {
        $ratings->clearPlatformMeans();
        Product::query()->eachById(fn (Product $product) => $product->update(['weighted_rating' => $ratings->weightedRating($product)]));
        Shop::query()->eachById(fn (Shop $shop) => $shop->update(['weighted_rating' => $ratings->weightedRating($shop)]));
        $this->info('Weighted ratings recalculated.');
        return self::SUCCESS;
    }
}
