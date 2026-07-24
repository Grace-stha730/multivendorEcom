<?php

namespace Tests\Unit;

use App\Services\Catalog\WeightedRatingService;
use App\Services\Recommendation\PurchaseRecommendationService;
use App\Services\Search\TfidfProductSearch;
use PHPUnit\Framework\TestCase;

class CommerceAlgorithmsTest extends TestCase
{
    public function test_weighted_rating_accounts_for_review_count(): void
    {
        $service = new WeightedRatingService();

        $this->assertSame(4.0, $service->calculate(2, 5.0, 3.8));
        $this->assertSame(4.47, $service->calculate(200, 4.5, 3.8));
    }

    public function test_cosine_similarity_measures_purchase_vector_overlap(): void
    {
        $service = new PurchaseRecommendationService(new WeightedRatingService());

        $this->assertEqualsWithDelta(0.5, $service->cosineSimilarity(
            [1 => 1, 2 => 1],
            [1 => 1, 4 => 1],
        ), 0.00001);
        $this->assertSame(0.0, $service->cosineSimilarity([], [1 => 1]));
    }

    public function test_tfidf_ranks_documents_by_term_frequency_and_rarity(): void
    {
        $service = new TfidfProductSearch();
        $scores = $service->scoreDocuments([
            1 => 'wireless wireless headphones',
            2 => 'wireless mouse',
            3 => 'wired keyboard',
            4 => 'gaming monitor',
        ], 'wireless');

        $this->assertGreaterThan($scores[2], $scores[1]);
        $this->assertSame(0.0, $scores[3]);
    }
}
