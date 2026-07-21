<?php

namespace Tests\Unit;

use App\Services\Search\ProductEmbeddingService;
use PHPUnit\Framework\TestCase;

class ProductEmbeddingServiceTest extends TestCase
{
    public function test_it_returns_stable_normalized_embeddings(): void
    {
        $service = new ProductEmbeddingService();

        $first = $service->embedText('Wireless blue headphones with noise cancelling');
        $second = $service->embedText('Wireless blue headphones with noise cancelling');

        $this->assertSame($first, $second);
        $this->assertCount(ProductEmbeddingService::DIMENSIONS, $first);
        $this->assertEqualsWithDelta(1.0, sqrt(array_sum(array_map(fn ($value) => $value * $value, $first))), 0.0001);
    }
}
