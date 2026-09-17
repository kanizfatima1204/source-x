<?php

namespace App\Services;

use App\Models\BuyerProductView;
use App\Models\BuyerRequest;
use App\Models\BuyerSearchHistory;
use App\Models\BuyerFeatureSnapshot;
use App\Models\Product;
use App\Models\ProductFeatureSnapshot;
use App\Models\RecommendationEvent;
use Illuminate\Support\Collection;

class RecommendationFeatureService
{
    public function buildBuyerSnapshot(
        int $userId,
        ?int $days = 30
    ): BuyerFeatureSnapshot {
        $since = now()->subDays($days);

        $searches = BuyerSearchHistory::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->get();

        $views = BuyerProductView::query()
            ->where('user_id', $userId)
            ->where('last_viewed_at', '>=', $since)
            ->get();

        $requests = BuyerRequest::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->get();

        $events = RecommendationEvent::query()
            ->where('user_id', $userId)
            ->where('occurred_at', '>=', $since)
            ->get();

        $impressions = $events
            ->where('event_type', 'impression')
            ->count();

        $clicks = $events
            ->where('event_type', 'click')
            ->count();

        $inquiries = $events
            ->where('event_type', 'inquiry')
            ->count();

        $conversions = $events
            ->where('event_type', 'conversion')
            ->count();

        $preferredCategory =
            $this->mostFrequent(
                collect()
                    ->merge($searches->pluck('category'))
                    ->merge($requests->pluck('category'))
            );

        $preferredLocation =
            $this->mostFrequent(
                collect()
                    ->merge($searches->pluck('location'))
                    ->merge($requests->pluck('location'))
            );

        $preferredBudget =
            $this->mostFrequent(
                collect()
                    ->merge($searches->pluck('budget_level'))
                    ->merge($requests->pluck('budget_level'))
            );

        $categoryAffinity = $this->affinity(
            collect()
                ->merge($searches->pluck('category'))
                ->merge($requests->pluck('category'))
        );

        $locationAffinity = $this->affinity(
            collect()
                ->merge($searches->pluck('location'))
                ->merge($requests->pluck('location'))
        );

        $budgetAffinity = $this->affinity(
            collect()
                ->merge($searches->pluck('budget_level'))
                ->merge($requests->pluck('budget_level'))
        );

        $clickRate = $impressions > 0
            ? ($clicks / $impressions) * 100
            : 0;

        $conversionRate = $clicks > 0
            ? ($conversions / $clicks) * 100
            : 0;

        $engagementScore = $this->engagementScore(
            $searches->count(),
            $views->sum('view_count'),
            $requests->count(),
            $clicks,
            $inquiries,
            $conversions
        );

        $lastActivity = collect([
            $searches->max('created_at'),
            $views->max('last_viewed_at'),
            $requests->max('created_at'),
            $events->max('occurred_at'),
        ])
            ->filter()
            ->sortDesc()
            ->first();

        return BuyerFeatureSnapshot::create([
            'user_id' => $userId,

            'search_count' => $searches->count(),
            'view_count' => $views->sum('view_count'),
            'request_count' => $requests->count(),

            'impression_count' => $impressions,
            'click_count' => $clicks,
            'inquiry_count' => $inquiries,
            'conversion_count' => $conversions,

            'click_rate' => round($clickRate, 4),
            'conversion_rate' => round($conversionRate, 4),

            'preferred_category' => $preferredCategory,
            'preferred_location' => $preferredLocation,
            'preferred_budget' => $preferredBudget,

            'category_affinity' => $categoryAffinity,
            'location_affinity' => $locationAffinity,
            'budget_affinity' => $budgetAffinity,

            'engagement_score' => $engagementScore,

            'last_activity_at' => $lastActivity,
            'snapshot_at' => now(),
        ]);
    }

    public function buildProductSnapshot(
        Product $product,
        ?int $days = 30
    ): ProductFeatureSnapshot {
        $since = now()->subDays($days);

        $events = RecommendationEvent::query()
            ->where('product_id', $product->id)
            ->where('occurred_at', '>=', $since)
            ->get();

        $impressions = $events
            ->where('event_type', 'impression')
            ->count();

        $clicks = $events
            ->where('event_type', 'click')
            ->count();

        $inquiries = $events
            ->where('event_type', 'inquiry')
            ->count();

        $conversions = $events
            ->where('event_type', 'conversion')
            ->count();

        $ctr = $impressions > 0
            ? ($clicks / $impressions) * 100
            : 0;

        $conversionRate = $clicks > 0
            ? ($conversions / $clicks) * 100
            : 0;

        $qualityScore = $this->productQualityScore(
            $product,
            $ctr,
            $conversionRate
        );

        return ProductFeatureSnapshot::create([
            'product_id' => $product->id,

            'category' => $product->category,
            'location' => $product->location,
            'budget_level' => $product->budget_level,

            'price_score' => $this->priceScore(
                (float) $product->price
            ),

            'popularity_score' =>
                min(
                    1,
                    max(
                        0,
                        ((float) $product->popularity_score) / 100
                    )
                ),

            'availability_score' =>
                $product->is_available ? 1 : 0,

            'verification_score' =>
                $product->is_verified ? 1 : 0,

            'view_count' => $product->view_count,
            'request_count' => $product->request_count,

            'recommendation_impressions' => $impressions,
            'recommendation_clicks' => $clicks,
            'recommendation_inquiries' => $inquiries,
            'recommendation_conversions' => $conversions,

            'ctr' => round($ctr, 4),
            'conversion_rate' => round(
                $conversionRate,
                4
            ),

            'quality_score' => $qualityScore,

            'snapshot_at' => now(),
        ]);
    }

    public function buildAll(
        ?int $days = 30
    ): array {
        $users = collect()
            ->merge(
                BuyerSearchHistory::query()
                    ->where('created_at', '>=', now()->subDays($days))
                    ->pluck('user_id')
            )
            ->merge(
                BuyerProductView::query()
                    ->where(
                        'last_viewed_at',
                        '>=',
                        now()->subDays($days)
                    )
                    ->pluck('user_id')
            )
            ->merge(
                BuyerRequest::query()
                    ->where(
                        'created_at',
                        '>=',
                        now()->subDays($days)
                    )
                    ->pluck('user_id')
            )
            ->merge(
                RecommendationEvent::query()
                    ->where(
                        'occurred_at',
                        '>=',
                        now()->subDays($days)
                    )
                    ->pluck('user_id')
            )
            ->unique()
            ->values();

        $buyerSnapshots = 0;

        foreach ($users as $userId) {
            $this->buildBuyerSnapshot(
                (int) $userId,
                $days
            );

            $buyerSnapshots++;
        }

        $productSnapshots = 0;

        Product::query()
            ->chunkById(
                100,
                function (Collection $products) use (
                    &$productSnapshots,
                    $days
                ) {
                    foreach ($products as $product) {
                        $this->buildProductSnapshot(
                            $product,
                            $days
                        );

                        $productSnapshots++;
                    }
                }
            );

        return [
            'buyers' => $buyerSnapshots,
            'products' => $productSnapshots,
        ];
    }

    private function mostFrequent(
        Collection $values
    ): ?string {
        return $values
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();
    }

    private function affinity(
        Collection $values
    ): float {
        if ($values->isEmpty()) {
            return 0;
        }

        return round(
            1 / max(1, $values->unique()->count()),
            4
        );
    }

    private function engagementScore(
        int $searches,
        int $views,
        int $requests,
        int $clicks,
        int $inquiries,
        int $conversions
    ): float {
        $raw =
            ($searches * 2)
            + ($views * 1)
            + ($requests * 5)
            + ($clicks * 4)
            + ($inquiries * 8)
            + ($conversions * 15);

        return round(
            min(
                1,
                $raw / 100
            ),
            4
        );
    }

    private function priceScore(
        float $price
    ): float {
        if ($price <= 0) {
            return 0;
        }

        return round(
            1 / (1 + log10($price)),
            4
        );
    }

    private function productQualityScore(
        Product $product,
        float $ctr,
        float $conversionRate
    ): float {
        $score = 0;

        $score +=
            min(1, $ctr / 20) * 0.30;

        $score +=
            min(1, $conversionRate / 10) * 0.30;

        $score +=
            ($product->is_verified ? 1 : 0) * 0.15;

        $score +=
            ($product->is_available ? 1 : 0) * 0.15;

        $score +=
            min(
                1,
                ((float) $product->popularity_score) / 100
            ) * 0.10;

        return round($score, 4);
    }
}
