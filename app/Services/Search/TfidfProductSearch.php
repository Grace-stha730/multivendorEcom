<?php

namespace App\Services\Search;

use App\Models\Product;
use Illuminate\Support\Collection;

class TfidfProductSearch
{
    public function search(string $query, ?int $categoryId = null): Collection
    {
        $terms = $this->terms($query);

        if ($terms === []) {
            return collect();
        }

        $catalog = Product::with(['vendor', 'firstImage'])->get();
        $scores = $this->scoreDocuments(
            $catalog->mapWithKeys(fn (Product $product) => [$product->id => $this->document($product)])->all(),
            $query,
        );

        return $catalog
            ->when($categoryId, fn (Collection $products) => $products->where('category_id', $categoryId))
            ->filter(fn (Product $product) => ($scores[$product->id] ?? 0) > 0)
            ->map(function (Product $product) use ($scores) {
                $product->search_score = round($scores[$product->id], 6);

                return $product;
            })
            ->sortByDesc('search_score')
            ->values();
    }

    /**
     * @param array<int|string, string> $documents
     * @return array<int|string, float>
     */
    public function scoreDocuments(array $documents, string $query): array
    {
        $terms = array_unique($this->terms($query));
        $documentTokens = array_map(fn (string $document) => $this->terms($document), $documents);
        $documentCount = count($documentTokens);
        $scores = array_fill_keys(array_keys($documents), 0.0);

        if ($documentCount === 0 || $terms === []) {
            return $scores;
        }

        foreach ($terms as $term) {
            $documentsContainingTerm = count(array_filter(
                $documentTokens,
                fn (array $tokens) => in_array($term, $tokens, true),
            ));
            $idf = log($documentCount / (1 + $documentsContainingTerm));

            foreach ($documentTokens as $id => $tokens) {
                $termFrequency = count(array_filter($tokens, fn (string $token) => $token === $term)) / max(count($tokens), 1);
                $scores[$id] += $termFrequency * $idf;
            }
        }

        return $scores;
    }

    private function document(Product $product): string
    {
        return implode(' ', [
            $product->name,
            $product->summary,
            $product->description,
        ]);
    }

    private function terms(string $text): array
    {
        $text = mb_strtolower(strip_tags($text));
        preg_match_all('/[\p{L}\p{N}]+/u', $text, $matches);

        return $matches[0] ?? [];
    }
}
