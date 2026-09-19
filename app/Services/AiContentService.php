<?php

namespace App\Services;

use App\Models\AiUsageLog;
use App\Models\Product;

class AiContentService
{
    public function __construct(private AiClientService $client) {}

    public function generateProductDescription(array $inputs, ?int $shopUserId = null): string
    {
        $result = $this->client->generate(
            'Write an accurate, appealing e-commerce product description. Do not invent specifications. Return plain text under 160 words.',
            'Product details: '.json_encode($inputs, JSON_UNESCAPED_UNICODE), 300,
        );
        $this->log('product_description', null, $shopUserId, $result['tokens']);
        return $result['text'];
    }

    public function generateRecommendationBlurb(Product $viewedProduct, Product $recommendedProduct): string
    {
        $result = $this->client->generate(
            'Write one neutral recommendation explanation of 15 to 20 words maximum. Never claim unprovided specifications or discounts.',
            "Viewed: {$viewedProduct->name}; category: {$viewedProduct->category?->name}; summary: {$viewedProduct->summary}. Recommended: {$recommendedProduct->name}; category: {$recommendedProduct->category?->name}; summary: {$recommendedProduct->summary}.",
            60,
        );
        $text = trim(preg_replace('/\s+/', ' ', $result['text']));
        $this->log('recommendation_text', null, null, $result['tokens']);
        return str($text)->words(20, '')->toString();
    }

    private function log(string $feature, ?int $userId, ?int $shopUserId, ?int $tokens): void
    {
        AiUsageLog::create([
            'feature' => $feature, 'user_id' => $userId, 'shop_user_id' => $shopUserId,
            'tokens_used' => $tokens, 'created_at' => now(),
        ]);
    }
}
