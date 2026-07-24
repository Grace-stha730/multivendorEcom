<?php

namespace App\Services\Recommendation;

use App\Models\Order_item;
use App\Models\Product;
use App\Models\User;
use App\Services\Catalog\WeightedRatingService;
use Illuminate\Support\Collection;

class PurchaseRecommendationService
{
    public function __construct(private WeightedRatingService $weightedRatingService)
    {
    }

    public function forUser(User $user, int $limit = 5): Collection
    {
        $purchases = Order_item::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_status', 'Delivered')
            ->select('orders.user_id', 'order_items.product_id')
            ->distinct()
            ->get();

        $vectors = $purchases->groupBy('user_id')
            ->map(fn (Collection $items) => $items->pluck('product_id')->mapWithKeys(fn ($id) => [$id => 1])->all());
        $targetVector = $vectors->get($user->id, []);

        if ($targetVector === []) {
            return collect();
        }

        $scores = [];
        foreach ($vectors as $otherUserId => $vector) {
            if ((int) $otherUserId === $user->id) {
                continue;
            }

            $similarity = $this->cosineSimilarity($targetVector, $vector);
            if ($similarity <= 0) {
                continue;
            }

            foreach ($vector as $productId => $_) {
                if (!isset($targetVector[$productId])) {
                    $scores[$productId] = ($scores[$productId] ?? 0) + $similarity;
                }
            }
        }

        if ($scores === []) {
            return collect();
        }

        $products = Product::with(['vendor', 'firstImage'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->whereIn('id', array_keys($scores))
            ->get()
            ->filter(fn (Product $product) => $product->stock > 0);

        return $this->weightedRatingService->rankedProducts($products)
            ->map(function (Product $product) use ($scores) {
                $product->recommendation_score = round($scores[$product->id], 6);

                return $product;
            })
            ->sortByDesc('recommendation_score')
            ->take($limit)
            ->values();
    }

    public function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($vectorA as $key => $value) {
            $dotProduct += $value * ($vectorB[$key] ?? 0);
            $normA += $value ** 2;
        }

        foreach ($vectorB as $value) {
            $normB += $value ** 2;
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
