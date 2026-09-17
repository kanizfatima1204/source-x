<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuyerFeatureSnapshot;
use App\Models\ProductFeatureSnapshot;
use App\Models\RecommendationTrainingSample;
use App\Services\RecommendationFeatureService;
use App\Services\RecommendationTrainingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationDataPipelineController extends Controller
{
    public function index(): Response
    {
        $latestBuyerSnapshot =
            BuyerFeatureSnapshot::query()
                ->latest('snapshot_at')
                ->first();

        $latestProductSnapshot =
            ProductFeatureSnapshot::query()
                ->latest('snapshot_at')
                ->first();

        $trainingSamples =
            RecommendationTrainingSample::query()
                ->count();

        $positiveSamples =
            RecommendationTrainingSample::query()
                ->where(
                    'engagement_label',
                    '>=',
                    0.5
                )
                ->count();

        $convertedSamples =
            RecommendationTrainingSample::query()
                ->where('converted', true)
                ->count();

        $algorithmBreakdown =
            RecommendationTrainingSample::query()
                ->selectRaw(
                    'algorithm, COUNT(*) as samples'
                )
                ->groupBy('algorithm')
                ->orderByDesc('samples')
                ->get();

        $recentSamples =
            RecommendationTrainingSample::query()
                ->with([
                    'user:id,name',
                    'product:id,name',
                ])
                ->latest()
                ->limit(20)
                ->get();

        return Inertia::render(
            'Admin/RecommendationDataPipeline/Index',
            [
                'stats' => [
                    'buyer_snapshots' =>
                        BuyerFeatureSnapshot::count(),

                    'product_snapshots' =>
                        ProductFeatureSnapshot::count(),

                    'training_samples' =>
                        $trainingSamples,

                    'positive_samples' =>
                        $positiveSamples,

                    'converted_samples' =>
                        $convertedSamples,

                    'latest_buyer_snapshot' =>
                        $latestBuyerSnapshot?->snapshot_at
                            ?->toDateTimeString(),

                    'latest_product_snapshot' =>
                        $latestProductSnapshot?->snapshot_at
                            ?->toDateTimeString(),
                ],

                'algorithmBreakdown' =>
                    $algorithmBreakdown,

                'recentSamples' =>
                    $recentSamples,
            ]
        );
    }

    public function buildFeatures(
        Request $request,
        RecommendationFeatureService $featureService
    ): RedirectResponse {
        $days = $request->integer(
            'days',
            30
        );

        $result = $featureService->buildAll($days);

        return back()->with(
            'success',
            "Feature pipeline completed. "
            . "{$result['buyers']} buyer snapshots and "
            . "{$result['products']} product snapshots generated."
        );
    }

    public function buildTrainingDataset(
        Request $request,
        RecommendationTrainingService $trainingService
    ): RedirectResponse {
        $days = $request->integer(
            'days',
            30
        );

        $count = $trainingService->build($days);

        return back()->with(
            'success',
            "{$count} recommendation training samples generated."
        );
    }
}
