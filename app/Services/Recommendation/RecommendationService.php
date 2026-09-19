<?php

namespace App\Services\Recommendation;

use App\Models\Order_item;
use App\Models\Product;
use App\Models\ProductVector;
use Illuminate\Support\Collection;

class RecommendationService
{
    private const STOPWORDS = ['a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'in', 'is', 'it', 'of', 'on', 'or', 'that', 'the', 'this', 'to', 'with'];

    public function buildProductDocument(Product $product): string
    {
        $text = implode(' ', array_filter([$product->name, $product->category?->name, $product->summary, $product->description, $product->tags ?? null]));
        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower(strip_tags($text)), $matches);

        return implode(' ', array_filter($matches[0] ?? [], fn (string $term) => ! in_array($term, self::STOPWORDS, true)));
    }

    /** @return array<int, array<string, float>> Sparse TF-IDF vectors keyed by product ID. */
    public function computeTfIdfVectors(): array
    {
        $documents = Product::with('category')->get()->mapWithKeys(fn (Product $product) => [$product->id => preg_split('/\s+/', $this->buildProductDocument($product), -1, PREG_SPLIT_NO_EMPTY)]);
        $totalDocuments = $documents->count();
        $documentFrequency = [];
        foreach ($documents as $terms) foreach (array_unique($terms) as $term) $documentFrequency[$term] = ($documentFrequency[$term] ?? 0) + 1;

        $vectors = [];
        foreach ($documents as $productId => $terms) {
            $counts = array_count_values($terms);
            $termTotal = max(count($terms), 1);
            $vectors[$productId] = [];
            foreach ($counts as $term => $count) {
                // TF measures importance inside this product; IDF suppresses terms
                // found throughout the catalogue, leaving descriptive terms to drive similarity.
                $tf = $count / $termTotal;
                $idf = log($totalDocuments / (1 + $documentFrequency[$term]));
                // The requested IDF can be non-positive for ubiquitous words; omit them from sparse vectors.
                if ($idf > 0) $vectors[$productId][$term] = round($tf * $idf, 8);
            }
        }

        return $vectors;
    }

    public function rebuildVectors(): int
    {
        $products = Product::with('category')->get()->keyBy('id');
        $vectors = $this->computeTfIdfVectors();
        foreach ($products as $id => $product) ProductVector::updateOrCreate(['product_id' => $id], ['vector' => $vectors[$id] ?? [], 'text_hash' => hash('sha256', $this->buildProductDocument($product))]);
        ProductVector::whereNotIn('product_id', $products->keys())->delete();

        return count($vectors);
    }

    /** @return array<int, array<string, float>> */
    public function vectorsByProductId(): array
    {
        return ProductVector::query()->pluck('vector', 'product_id')->map(fn ($vector) => $vector ?: [])->all();
    }

    public function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dot = 0.0; $normA = 0.0; $normB = 0.0;
        // Cosine compares vector direction, rather than vector length, so a long
        // description is not automatically considered more similar than a short one.
        foreach ($vectorA as $term => $weight) { $dot += $weight * ($vectorB[$term] ?? 0); $normA += $weight ** 2; }
        foreach ($vectorB as $weight) $normB += $weight ** 2;
        return $normA > 0 && $normB > 0 ? round($dot / (sqrt($normA) * sqrt($normB)), 6) : 0.0;
    }

    public function getSimilarProducts(Product $product, int $limit = 6): Collection
    {
        $vectors = $this->vectorsByProductId();
        $current = $vectors[$product->id] ?? null;
        if (! $current) return collect();
        $scores = collect($vectors)->except($product->id)->map(fn (array $vector) => $this->cosineSimilarity($current, $vector))->filter(fn (float $score) => $score > 0)->sortDesc()->take($limit);
        return Product::with(['shop', 'firstImage'])->whereIn('id', $scores->keys())->get()->sortBy(fn (Product $item) => -$scores[$item->id])->values();
    }

    public function getHybridRecommendations(Product $viewedProduct, int $limit = 8): Collection
    {
        $vectors = $this->vectorsByProductId(); $current = $vectors[$viewedProduct->id] ?? null;
        if (! $current) return collect();
        $maxOrders = max(1, (int) Order_item::selectRaw('count(*) as count')->groupBy('product_id')->orderByDesc('count')->value('count'));
        $orderCounts = Order_item::selectRaw('product_id, count(*) as count')->groupBy('product_id')->pluck('count', 'product_id');
        $weights = config('recommendations.hybrid');
        $alpha = (float) ($weights['similarity'] ?? 0.5);
        $beta = (float) ($weights['weighted_rating'] ?? 0.3);
        $gamma = (float) ($weights['popularity'] ?? 0.2);

        if (abs(($alpha + $beta + $gamma) - 1.0) > 0.00001) {
            throw new \LogicException('Hybrid recommendation weights must sum to 1.');
        }

        return Product::with(['shop', 'firstImage'])->where('id', '!=', $viewedProduct->id)->where('stock', '>', 0)->get()->map(function (Product $candidate) use ($vectors, $current, $orderCounts, $maxOrders, $alpha, $beta, $gamma) {
            $similarity = $this->cosineSimilarity($current, $vectors[$candidate->id] ?? []);
            $rating = min(1, max(0, (float) ($candidate->weighted_rating ?? 0) / 5));
            $popularity = ((int) ($orderCounts[$candidate->id] ?? 0)) / $maxOrders;
            // FinalScore = alpha·similarity + beta·trusted quality + gamma·demand.
            // All inputs are normalized to [0, 1], making the configured weights meaningful.
            $candidate->recommendation_score = round(($alpha * $similarity) + ($beta * $rating) + ($gamma * $popularity), 6);
            return $candidate;
        })->sortByDesc('recommendation_score')->take($limit)->values();
    }
}
