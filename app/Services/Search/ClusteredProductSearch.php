<?php

namespace App\Services\Search;

use App\Models\Product;
use App\Models\ProductEmbedding;
use Illuminate\Support\Collection;

class ClusteredProductSearch
{
    public function __construct(private ProductEmbeddingService $embeddingService)
    {
    }

    public function search(string $query = '', string $algorithm = 'kmeans'): array
    {
        $products = Product::with(['vendor', 'category', 'firstImage', 'productRating'])->get();

        if ($products->isEmpty()) {
            return [];
        }

        $points = $products->map(function (Product $product) {
            $embedding = $this->embeddingFor($product);

            return [
                'product' => $product,
                'embedding' => $embedding,
            ];
        })->values();

        $queryEmbedding = $this->embeddingService->embedText($query);
        $ranked = $points->map(function (array $point) use ($query, $queryEmbedding) {
            $point['score'] = $this->rankScore($point['product'], $point['embedding'], $queryEmbedding, $query);

            return $point;
        })->filter(fn (array $point) => $query === '' || $point['score'] > 0.08)->values();

        if ($ranked->isEmpty()) {
            return [];
        }

        $labels = $algorithm === 'dbscan'
            ? $this->dbscan($ranked)
            : $this->kmeans($ranked, min(4, max(1, (int) floor(sqrt($ranked->count())))));

        return $this->groupResults($ranked, $labels, $algorithm);
    }

    private function embeddingFor(Product $product): array
    {
        $hash = $this->embeddingService->productTextHash($product);
        $stored = ProductEmbedding::where('product_id', $product->id)->first();

        if ($stored && $stored->text_hash === $hash) {
            return $stored->embedding;
        }

        $embedding = $this->embeddingService->embedProduct($product);

        ProductEmbedding::updateOrCreate(
            ['product_id' => $product->id],
            ['embedding' => $embedding, 'text_hash' => $hash]
        );

        return $embedding;
    }

    private function rankScore(Product $product, array $embedding, array $queryEmbedding, string $query): float
    {
        $similarity = trim($query) === '' ? 0.35 : $this->cosine($embedding, $queryEmbedding);
        $stockBoost = $product->stock > 0 ? 0.08 : -0.15;
        $discountBoost = $product->discount ? 0.03 : 0.0;
        $ratingBoost = ((float) ($product->productRating?->rating ?? 0)) / 100;

        return round($similarity + $stockBoost + $discountBoost + $ratingBoost, 6);
    }

    private function groupResults(Collection $ranked, array $labels, string $algorithm): array
    {
        $groups = [];

        foreach ($ranked as $index => $point) {
            $label = $labels[$index] ?? -1;
            $groups[$label][] = $point;

            ProductEmbedding::where('product_id', $point['product']->id)->update([
                'cluster_algorithm' => $algorithm,
                'cluster_label' => $label,
            ]);
        }

        ksort($groups);

        return collect($groups)->map(function (array $items, int $label) {
            usort($items, fn ($a, $b) => $b['score'] <=> $a['score']);

            return [
                'label' => $label,
                'name' => $label < 0 ? 'Other matches' : 'Cluster ' . ($label + 1),
                'score' => round(collect($items)->avg('score'), 4),
                'products' => collect($items)->pluck('product')->values(),
            ];
        })->sortByDesc('score')->values()->all();
    }

    private function kmeans(Collection $points, int $k): array
    {
        $centroids = $points->take($k)->pluck('embedding')->all();
        $labels = array_fill(0, $points->count(), 0);

        for ($iteration = 0; $iteration < 12; $iteration++) {
            foreach ($points as $index => $point) {
                $labels[$index] = $this->nearestCentroid($point['embedding'], $centroids);
            }

            foreach (range(0, $k - 1) as $cluster) {
                $members = $points->filter(fn ($point, $index) => $labels[$index] === $cluster)->pluck('embedding')->all();

                if ($members !== []) {
                    $centroids[$cluster] = $this->meanVector($members);
                }
            }
        }

        return $labels;
    }

    private function dbscan(Collection $points, float $epsilon = 0.55, int $minPoints = 2): array
    {
        $labels = array_fill(0, $points->count(), null);
        $clusterId = 0;

        foreach ($points as $index => $point) {
            if ($labels[$index] !== null) {
                continue;
            }

            $neighbors = $this->neighbors($points, $index, $epsilon);

            if (count($neighbors) < $minPoints) {
                $labels[$index] = -1;
                continue;
            }

            $labels[$index] = $clusterId;
            $queue = $neighbors;

            while ($queue !== []) {
                $neighborIndex = array_shift($queue);

                if ($labels[$neighborIndex] === -1) {
                    $labels[$neighborIndex] = $clusterId;
                }

                if ($labels[$neighborIndex] !== null) {
                    continue;
                }

                $labels[$neighborIndex] = $clusterId;
                $neighborNeighbors = $this->neighbors($points, $neighborIndex, $epsilon);

                if (count($neighborNeighbors) >= $minPoints) {
                    $queue = array_values(array_unique(array_merge($queue, $neighborNeighbors)));
                }
            }

            $clusterId++;
        }

        return $labels;
    }

    private function neighbors(Collection $points, int $index, float $epsilon): array
    {
        $neighbors = [];
        $embedding = $points[$index]['embedding'];

        foreach ($points as $candidateIndex => $point) {
            if ((1 - $this->cosine($embedding, $point['embedding'])) <= $epsilon) {
                $neighbors[] = $candidateIndex;
            }
        }

        return $neighbors;
    }

    private function nearestCentroid(array $embedding, array $centroids): int
    {
        $bestIndex = 0;
        $bestScore = -INF;

        foreach ($centroids as $index => $centroid) {
            $score = $this->cosine($embedding, $centroid);

            if ($score > $bestScore) {
                $bestIndex = $index;
                $bestScore = $score;
            }
        }

        return $bestIndex;
    }

    private function meanVector(array $vectors): array
    {
        $mean = array_fill(0, ProductEmbeddingService::DIMENSIONS, 0.0);

        foreach ($vectors as $vector) {
            foreach ($vector as $index => $value) {
                $mean[$index] += $value;
            }
        }

        return array_map(fn ($value) => $value / count($vectors), $mean);
    }

    private function cosine(array $a, array $b): float
    {
        $dot = 0.0;
        $aMagnitude = 0.0;
        $bMagnitude = 0.0;

        foreach ($a as $index => $value) {
            $dot += $value * ($b[$index] ?? 0);
            $aMagnitude += $value * $value;
            $bMagnitude += ($b[$index] ?? 0) * ($b[$index] ?? 0);
        }

        if ($aMagnitude == 0.0 || $bMagnitude == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($aMagnitude) * sqrt($bMagnitude));
    }
}
