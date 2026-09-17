<?php

namespace App\Console\Commands;

use App\Services\RecommendationTrainingService;
use Illuminate\Console\Command;

class BuildRecommendationTrainingDataset extends Command
{
    protected $signature = 'recommendation:build-training-dataset
                            {--days=30 : Number of days to analyse}';

    protected $description =
        'Build the recommendation training dataset from recommendation events.';

    public function handle(
        RecommendationTrainingService $trainingService
    ): int {
        $days = (int) $this->option('days');

        $this->info(
            "Building training dataset for {$days} days..."
        );

        $count = $trainingService->build($days);

        $this->info(
            "Training samples generated: {$count}"
        );

        return self::SUCCESS;
    }
}
