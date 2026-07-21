<?php

namespace App\Services\Search;

use App\Models\Product;

class ProductEmbeddingService
{
    public const DIMENSIONS = 48;

    public function productText(Product $product): string
    {
        return trim(implode(' ', array_filter([
            $product->name,
            $product->summary,
            $product->description,
            $product->category?->name,
            $product->vendor?->shop_name,
        ])));
    }

    public function productTextHash(Product $product): string
    {
        return hash('sha256', $this->productText($product));
    }

    public function embedProduct(Product $product): array
    {
        return $this->embedText($this->productText($product));
    }

    public function embedText(string $text): array
    {
        $vector = array_fill(0, self::DIMENSIONS, 0.0);
        $tokens = $this->tokens($text);

        foreach ($tokens as $token) {
            $hash = crc32($token);
            $index = abs($hash) % self::DIMENSIONS;
            $sign = ($hash % 2) === 0 ? 1 : -1;
            $vector[$index] += $sign * (1 + min(strlen($token), 12) / 12);
        }

        return $this->normalize($vector);
    }

    private function tokens(string $text): array
    {
        $text = strtolower(strip_tags($text));
        preg_match_all('/[a-z0-9]+/', $text, $matches);

        return array_values(array_filter($matches[0] ?? [], fn ($token) => strlen($token) > 2));
    }

    private function normalize(array $vector): array
    {
        $magnitude = sqrt(array_sum(array_map(fn ($value) => $value * $value, $vector)));

        if ($magnitude == 0.0) {
            return $vector;
        }

        return array_map(fn ($value) => round($value / $magnitude, 6), $vector);
    }
}
