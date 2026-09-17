<?php

namespace App\Console\Commands;

use App\Services\RecommendationFeatureService;
use Illuminate\Console\Command;

class BuildRecommendationFeatures extends Command
{
    protected $signature = 'recommendation:build-features
                            {--days=30 : Number of days to analyse}';

    protected $description =
        'Build buyer and product recommendation feature snapshots.';

    public function handle(
        RecommendationFeatureService $featureService
    ): int {
        $days = (int) $this->option('days');

        $this->info(
            "Building recommendation features for {$days} days..."
        );

        $result = $featureService->buildAll($days);

        $this->info(
            "Buyer snapshots: {$result['buyers']}"
        );

        $this->info(
            "Product snapshots: {$result['products']}"
        );

        $this->info(
            'Recommendation features built successfully.'
        );

        return self::SUCCESS;
    }
}
