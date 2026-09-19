<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductRecommendationBlurb;
use App\Services\AiContentService;
use App\Services\Recommendation\RecommendationService;
use Illuminate\Console\Command;

class GenerateRecommendationBlurbs extends Command
{
    protected $signature = 'ai:generate-recommendation-blurbs {--limit=6}';
    protected $description = 'Cache AI explanations for TF-IDF similar-product pairs';
    public function handle(RecommendationService $recommendations, AiContentService $ai): int
    {
        Product::with('category')->eachById(function (Product $product) use ($recommendations, $ai) {
            foreach ($recommendations->getSimilarProducts($product, (int) $this->option('limit')) as $related) {
                ProductRecommendationBlurb::updateOrCreate(
                    ['product_id' => $product->id, 'related_product_id' => $related->id],
                    ['blurb_text' => $ai->generateRecommendationBlurb($product, $related), 'generated_at' => now()],
                );
            }
        });
        $this->info('Recommendation blurbs generated.'); return self::SUCCESS;
    }
}
