<?php

namespace App\Console\Commands;

use App\Services\Recommendation\RecommendationService;
use Illuminate\Console\Command;

class RebuildProductVectors extends Command
{
    protected $signature = 'recommendations:rebuild-vectors';
    protected $description = 'Build and persist TF-IDF vectors for all products';

    public function handle(RecommendationService $recommendations): int
    {
        $this->info("Rebuilt {$recommendations->rebuildVectors()} product TF-IDF vectors.");
        return self::SUCCESS;
    }
}
