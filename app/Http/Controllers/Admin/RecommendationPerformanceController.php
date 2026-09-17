<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecommendationEvent;
use App\Models\RecommendationLog;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationPerformanceController extends Controller
{
    public function index(): Response
    {
        $impressions = RecommendationEvent::where(
            'event_type',
            'impression'
        )->count();

        $clicks = RecommendationEvent::where(
            'event_type',
            'click'
        )->count();

        $inquiries = RecommendationEvent::where(
            'event_type',
            'inquiry'
        )->count();

        $conversions = RecommendationEvent::where(
            'event_type',
            'conversion'
        )->count();

        $ctr = $impressions > 0
            ? round(
                ($clicks / $impressions) * 100,
                2
            )
            : 0;

        $inquiryRate = $clicks > 0
            ? round(
                ($inquiries / $clicks) * 100,
                2
            )
            : 0;

        $conversionRate = $clicks > 0
            ? round(
                ($conversions / $clicks) * 100,
                2
            )
            : 0;

        $recommendationConversionRate = $impressions > 0
            ? round(
                ($conversions / $impressions) * 100,
                2
            )
            : 0;

        $algorithmPerformance = RecommendationEvent::query()
            ->select(
                'algorithm',
                DB::raw(
                    "SUM(CASE WHEN event_type = 'impression' THEN 1 ELSE 0 END) as impressions"
                ),
                DB::raw(
                    "SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks"
                ),
                DB::raw(
                    "SUM(CASE WHEN event_type = 'inquiry' THEN 1 ELSE 0 END) as inquiries"
                ),
                DB::raw(
                    "SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) as conversions"
                )
            )
            ->whereNotNull('algorithm')
            ->groupBy('algorithm')
            ->get()
            ->map(function ($row) {
                $row->ctr = $row->impressions > 0
                    ? round(
                        ($row->clicks / $row->impressions) * 100,
                        2
                    )
                    : 0;

                $row->conversion_rate = $row->clicks > 0
                    ? round(
                        ($row->conversions / $row->clicks) * 100,
                        2
                    )
                    : 0;

                return $row;
            });

        $productPerformance = RecommendationEvent::query()
            ->select(
                'product_id',
                DB::raw(
                    "SUM(CASE WHEN event_type = 'impression' THEN 1 ELSE 0 END) as impressions"
                ),
                DB::raw(
                    "SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks"
                ),
                DB::raw(
                    "SUM(CASE WHEN event_type = 'inquiry' THEN 1 ELSE 0 END) as inquiries"
                ),
                DB::raw(
                    "SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) as conversions"
                )
            )
            ->with('product:id,name,category,location')
            ->groupBy('product_id')
            ->orderByDesc('conversions')
            ->orderByDesc('clicks')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                $row->ctr = $row->impressions > 0
                    ? round(
                        ($row->clicks / $row->impressions) * 100,
                        2
                    )
                    : 0;

                $row->conversion_rate = $row->clicks > 0
                    ? round(
                        ($row->conversions / $row->clicks) * 100,
                        2
                    )
                    : 0;

                return $row;
            });

        $recentEvents = RecommendationEvent::query()
            ->with([
                'product:id,name',
                'user:id,name',
            ])
            ->latest('occurred_at')
            ->limit(30)
            ->get();

        $dailyPerformance = RecommendationEvent::query()
            ->select(
                DB::raw('DATE(occurred_at) as date'),
                DB::raw(
                    "SUM(CASE WHEN event_type = 'impression' THEN 1 ELSE 0 END) as impressions"
                ),
                DB::raw(
                    "SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks"
                ),
                DB::raw(
                    "SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) as conversions"
                )
            )
            ->where(
                'occurred_at',
                '>=',
                now()->subDays(30)
            )
            ->groupBy(
                DB::raw('DATE(occurred_at)')
            )
            ->orderBy('date')
            ->get();

        return Inertia::render(
            'Admin/RecommendationPerformance/Index',
            [
                'stats' => [
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'inquiries' => $inquiries,
                    'conversions' => $conversions,
                    'ctr' => $ctr,
                    'inquiry_rate' => $inquiryRate,
                    'conversion_rate' => $conversionRate,
                    'recommendation_conversion_rate' =>
                        $recommendationConversionRate,
                ],

                'algorithmPerformance' =>
                    $algorithmPerformance,

                'productPerformance' =>
                    $productPerformance,

                'recentEvents' =>
                    $recentEvents,

                'dailyPerformance' =>
                    $dailyPerformance,
            ]
        );
    }
}
