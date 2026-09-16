<?php

namespace App\Services\SourceMatching;

use App\Models\MatchResult;
use Illuminate\Support\Collection;

class MatchExplanationService
{
    /**
     * Return a structured explanation for one match.
     */
    public function explain(MatchResult $match): array
    {
        $match->loadMissing([
            'reasons',
            'source',
        ]);

        $reasons = $match->reasons;

        return [
            'overall' => [
                'score' => (float) $match->total_score,
                'rank' => $match->rank,
                'confidence' => $match->confidence,
                'status' => $match->status,
                'summary' => $match->summary,
            ],

            'strengths' => $this->strengths($reasons),

            'weaknesses' => $this->weaknesses($reasons),

            'factors' => $this->factors($reasons),

            'selection' => [
                'algorithm_selected' => $match->is_algorithm_selected,
                'admin_selected' => $match->is_admin_selected,
            ],
        ];
    }

    /**
     * Return factors in a predictable order.
     */
    private function factors(Collection $reasons): array
    {
        $order = [
            'product',
            'category',
            'location',
            'price',
            'quality',
            'availability',
            'verification',
            'performance',
        ];

        return $reasons
            ->sortBy(function ($reason) use ($order) {
                $position = array_search(
                    $reason->factor,
                    $order,
                    true
                );

                return $position === false
                    ? 999
                    : $position;
            })
            ->map(function ($reason) {
                return [
                    'id' => $reason->id,
                    'factor' => $reason->factor,
                    'score' => (float) $reason->score,
                    'weight' => (float) $reason->weight,
                    'weighted_score' => (float) $reason->weighted_score,
                    'status' => $reason->status,
                    'message' => $reason->message,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Strong factors.
     */
    private function strengths(Collection $reasons): array
    {
        return $reasons
            ->filter(
                fn ($reason) => (float) $reason->score >= 85
            )
            ->map(function ($reason) {
                return [
                    'factor' => $reason->factor,
                    'score' => (float) $reason->score,
                    'message' => $reason->message,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Weak factors.
     */
    private function weaknesses(Collection $reasons): array
    {
        return $reasons
            ->filter(
                fn ($reason) => (float) $reason->score < 70
            )
            ->sortBy('score')
            ->map(function ($reason) {
                return [
                    'factor' => $reason->factor,
                    'score' => (float) $reason->score,
                    'message' => $reason->message,
                ];
            })
            ->values()
            ->all();
    }
}
