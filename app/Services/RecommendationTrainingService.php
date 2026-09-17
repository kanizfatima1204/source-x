<?php

namespace App\Services;

use App\Models\BuyerFeatureSnapshot;
use App\Models\ProductFeatureSnapshot;
use App\Models\RecommendationEvent;
use App\Models\RecommendationLog;
use App\Models\RecommendationTrainingSample;

class RecommendationTrainingService
{
    public function build(
        ?int $days = 30
    ): int {
        $since = now()->subDays($days);

        $logs = RecommendationLog::query()
            ->where('created_at', '>=', $since)
            ->with('product')
            ->get();

        $created = 0;

        foreach ($logs as $log) {
            $events = RecommendationEvent::query()
                ->where('recommendation_log_id', $log->id)
                ->get();

            if ($events->isEmpty()) {
                continue;
            }

            $buyerFeatures =
                BuyerFeatureSnapshot::query()
                    ->where('user_id', $log->user_id)
                    ->latest('snapshot_at')
                    ->first();

            $productFeatures =
                ProductFeatureSnapshot::query()
                    ->where('product_id', $log->product_id)
                    ->latest('snapshot_at')
                    ->first();

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

            $label = $this->engagementLabel(
                $impressions,
                $clicks,
                $inquiries,
                $conversions
            );

            $breakdown =
                is_array($log->score_breakdown)
                    ? $log->score_breakdown
                    : [];

            RecommendationTrainingSample::updateOrCreate(
                [
                    'recommendation_log_id' => $log->id,
                ],
                [
                    'user_id' => $log->user_id,
                    'product_id' => $log->product_id,

                    'algorithm' => $log->algorithm ?? null,

                    'variant' =>
                        $breakdown['experiment_variant']
                        ?? null,

                    'recommendation_score' =>
                        $log->score,

                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'inquiries' => $inquiries,
                    'conversions' => $conversions,

                    'engagement_label' => $label,

                    'clicked' => $clicks > 0,
                    'converted' => $conversions > 0,

                    'buyer_features' =>
                        $buyerFeatures?->toArray(),

                    'product_features' =>
                        $productFeatures?->toArray(),

                    'recommendation_features' =>
                        $breakdown,

                    'first_seen_at' =>
                        $events->min('occurred_at'),

                    'last_event_at' =>
                        $events->max('occurred_at'),
                ]
            );

            $created++;
        }

        return $created;
    }

    private function engagementLabel(
        int $impressions,
        int $clicks,
        int $inquiries,
        int $conversions
    ): float {
        if ($conversions > 0) {
            return 1;
        }

        if ($inquiries > 0) {
            return 0.75;
        }

        if ($clicks > 0) {
            return 0.50;
        }

        if ($impressions > 0) {
            return 0.05;
        }

        return 0;
    }
}
