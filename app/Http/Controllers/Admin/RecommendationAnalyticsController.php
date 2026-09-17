<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RecommendationLog;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationAnalyticsController extends Controller
{
    public function index(): Response
    {
        $totalRecommendations = RecommendationLog::count();

        $averageScore = round(
            (float) RecommendationLog::avg('score'),
            2
        );

        $totalClicks = RecommendationLog::where('clicked', true)->count();

        $clickRate = $totalRecommendations > 0
            ? round(($totalClicks / $totalRecommendations) * 100, 2)
            : 0;

        $topProducts = RecommendationLog::query()
            ->select(
                'product_id',
                DB::raw('COUNT(*) as recommendation_count'),
                DB::raw('AVG(score) as average_score'),
                DB::raw('SUM(CASE WHEN clicked = 1 THEN 1 ELSE 0 END) as click_count')
            )
            ->with('product:id,name,category,location')
            ->groupBy('product_id')
            ->orderByDesc('recommendation_count')
            ->limit(10)
            ->get();

        $factorPerformance = $this->factorPerformance();

        $sourcePerformance = RecommendationLog::query()
            ->select(
                'source',
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(score) as average_score'),
                DB::raw('SUM(CASE WHEN clicked = 1 THEN 1 ELSE 0 END) as clicks')
            )
            ->groupBy('source')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                $item->click_rate = $item->total > 0
                    ? round(($item->clicks / $item->total) * 100, 2)
                    : 0;

                $item->average_score = round(
                    (float) $item->average_score,
                    2
                );

                return $item;
            });

        return Inertia::render('Admin/RecommendationAnalytics/Index', [
            'stats' => [
                'total_recommendations' => $totalRecommendations,
                'average_score' => $averageScore,
                'total_clicks' => $totalClicks,
                'click_rate' => $clickRate,
                'total_products' => Product::count(),
            ],
            'topProducts' => $topProducts,
            'factorPerformance' => $factorPerformance,
            'sourcePerformance' => $sourcePerformance,
        ]);
    }

    private function factorPerformance()
    {
        $logs = RecommendationLog::query()
            ->whereNotNull('score_breakdown')
            ->get(['score_breakdown']);

        $factors = [];

        foreach ($logs as $log) {
            foreach (($log->score_breakdown ?? []) as $factor => $value) {
                if (!isset($factors[$factor])) {
                    $factors[$factor] = [
                        'factor' => $factor,
                        'total' => 0,
                        'count' => 0,
                    ];
                }

                $factors[$factor]['total'] += (float) $value;
                $factors[$factor]['count']++;
            }
        }

        return collect($factors)
            ->map(function ($factor) {
                return [
                    'factor' => $factor['factor'],
                    'average_contribution' => round(
                        $factor['count'] > 0
                            ? $factor['total'] / $factor['count']
                            : 0,
                        2
                    ),
                ];
            })
            ->sortByDesc('average_contribution')
            ->values();
    }
}
