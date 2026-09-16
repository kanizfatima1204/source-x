<?php

namespace App\Services\Analytics;

use App\Models\AdminOverride;
use App\Models\BuyerRequest;
use App\Models\MatchResult;
use App\Models\Source;
use App\Models\SourceAvailability;
use App\Models\SourceVerification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OperationalIntelligenceService
{
    public function dashboard(
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        [$from, $to] = $this->resolveDateRange(
            $dateFrom,
            $dateTo
        );

        return [
            'period' => [
                'date_from' => $from->toDateString(),
                'date_to' => $to->toDateString(),
            ],

            'overview' => $this->overview(
                $from,
                $to
            ),

            'source_health' => $this->sourceHealth(),

            'source_utilization' => $this->sourceUtilization(
                $from,
                $to
            ),

            'matching_health' => $this->matchingHealth(
                $from,
                $to
            ),

            'demand_matrix' => $this->demandMatrix(
                $from,
                $to
            ),

            'alerts' => $this->alerts(),

            'admin_activity' => $this->adminActivity(
                $from,
                $to
            ),
        ];
    }

    private function resolveDateRange(
        ?string $dateFrom,
        ?string $dateTo
    ): array {
        try {
            $from = $dateFrom
                ? Carbon::parse($dateFrom)->startOfDay()
                : now()->subDays(29)->startOfDay();
        } catch (\Throwable) {
            $from = now()->subDays(29)->startOfDay();
        }

        try {
            $to = $dateTo
                ? Carbon::parse($dateTo)->endOfDay()
                : now()->endOfDay();
        } catch (\Throwable) {
            $to = now()->endOfDay();
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    private function overview(
        Carbon $from,
        Carbon $to
    ): array {
        $requestQuery = BuyerRequest::query()
            ->whereBetween('created_at', [$from, $to]);

        $matchQuery = MatchResult::query()
            ->whereBetween('created_at', [$from, $to]);

        $totalRequests = (clone $requestQuery)->count();

        $submittedRequests = (clone $requestQuery)
            ->where('status', 'submitted')
            ->count();

        $matchedRequests = (clone $requestQuery)
            ->where('status', 'matched')
            ->count();

        $cancelledRequests = (clone $requestQuery)
            ->where('status', 'cancelled')
            ->count();

        $totalMatches = (clone $matchQuery)->count();

        $averageScore = (float) (
            (clone $matchQuery)->avg('total_score') ?? 0
        );

        $highConfidenceMatches = (clone $matchQuery)
            ->where('confidence', 'high')
            ->count();

        $approvedMatches = (clone $matchQuery)
            ->where('status', 'approved')
            ->count();

        $rejectedMatches = (clone $matchQuery)
            ->where('status', 'rejected')
            ->count();

        $decidedMatches = $approvedMatches + $rejectedMatches;

        return [
            'total_requests' => $totalRequests,
            'submitted_requests' => $submittedRequests,
            'matched_requests' => $matchedRequests,
            'cancelled_requests' => $cancelledRequests,

            'total_matches' => $totalMatches,

            'average_match_score' => round(
                $averageScore,
                2
            ),

            'high_confidence_percentage' => $totalMatches > 0
                ? round(
                    ($highConfidenceMatches / $totalMatches) * 100,
                    1
                )
                : 0,

            'approval_rate' => $decidedMatches > 0
                ? round(
                    ($approvedMatches / $decidedMatches) * 100,
                    1
                )
                : 0,

            'rejection_rate' => $decidedMatches > 0
                ? round(
                    ($rejectedMatches / $decidedMatches) * 100,
                    1
                )
                : 0,

            'match_success_rate' => $totalRequests > 0
                ? round(
                    ($matchedRequests / $totalRequests) * 100,
                    1
                )
                : 0,
        ];
    }

    private function sourceHealth(): array
    {
        $sources = Source::query()
            ->with([
                'performance',
                'latestVerification',
                'products',
                'availabilities',
            ])
            ->get();

        $health = $sources
            ->map(function (Source $source) {
                $verificationScore = $this->verificationScore($source);

                $availabilityScore = $this->availabilityScore($source);

                $qualityScore = (int) $source->quality_score;

                $performanceScore = $source->performance
                    ? (int) $source->performance->performance_score
                    : (int) $source->performance_score;

                $healthScore = (int) round(
                    ($qualityScore * 0.30)
                    + ($performanceScore * 0.30)
                    + ($verificationScore * 0.20)
                    + ($availabilityScore * 0.20)
                );

                return [
                    'id' => $source->id,
                    'reference_code' => $source->reference_code,
                    'name' => $source->name ?: 'Internal Source',
                    'type' => $source->type,
                    'status' => $source->status,

                    'health_score' => $healthScore,

                    'quality_score' => $qualityScore,
                    'performance_score' => $performanceScore,
                    'verification_score' => $verificationScore,
                    'availability_score' => $availabilityScore,

                    'product_count' => $source->products->count(),
                    'available_product_count' => $source
                        ->products
                        ->where('is_available', true)
                        ->count(),

                    'health_label' => $this->healthLabel(
                        $healthScore
                    ),
                ];
            })
            ->sortByDesc('health_score')
            ->values();

        $averageHealth = $health->count() > 0
            ? round(
                $health->avg('health_score'),
                1
            )
            : 0;

        return [
            'average_score' => $averageHealth,
            'total_sources' => $health->count(),
            'healthy_sources' => $health
                ->where('health_score', '>=', 80)
                ->count(),
            'attention_sources' => $health
                ->whereBetween('health_score', [60, 79])
                ->count(),
            'critical_sources' => $health
                ->where('health_score', '<', 60)
                ->count(),

            'sources' => $health
                ->take(10)
                ->values()
                ->all(),
        ];
    }

    private function sourceUtilization(
        Carbon $from,
        Carbon $to
    ): array {
        $sources = Source::query()
            ->withCount([
                'products',
                'matchResults' => function (Builder $query) use (
                    $from,
                    $to
                ) {
                    $query->whereBetween(
                        'created_at',
                        [$from, $to]
                    );
                },
            ])
            ->with([
                'performance',
            ])
            ->get();

        $rows = $sources
            ->map(function (Source $source) {
                $matchCount = (int) $source->match_results_count;
                $productCount = (int) $source->products_count;

                $completed = (int) (
                    $source->performance?->completed_orders ?? 0
                );

                $totalOrders = (int) (
                    $source->performance?->total_orders ?? 0
                );

                $completionRate = $totalOrders > 0
                    ? round(
                        ($completed / $totalOrders) * 100,
                        1
                    )
                    : 0;

                return [
                    'reference_code' => $source->reference_code,
                    'name' => $source->name ?: 'Internal Source',
                    'status' => $source->status,

                    'product_count' => $productCount,
                    'match_count' => $matchCount,

                    'utilization_score' => min(
                        100,
                        ($matchCount * 10)
                        + ($productCount * 3)
                    ),

                    'completion_rate' => $completionRate,
                ];
            })
            ->sortByDesc('match_count')
            ->values();

        return [
            'sources' => $rows->take(10)->values()->all(),
        ];
    }

    private function matchingHealth(
        Carbon $from,
        Carbon $to
    ): array {
        $matches = MatchResult::query()
            ->whereBetween('created_at', [$from, $to]);

        $total = (clone $matches)->count();

        $high = (clone $matches)
            ->where('confidence', 'high')
            ->count();

        $medium = (clone $matches)
            ->where('confidence', 'medium')
            ->count();

        $low = (clone $matches)
            ->where(function ($query) {
                $query
                    ->where('confidence', 'low')
                    ->orWhereNull('confidence');
            })
            ->count();

        $approved = (clone $matches)
            ->where('status', 'approved')
            ->count();

        $rejected = (clone $matches)
            ->where('status', 'rejected')
            ->count();

        $requests = BuyerRequest::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $requestsWithMatches = BuyerRequest::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereHas('matches')
            ->count();

        $noMatchRequests = max(
            0,
            $requests - $requestsWithMatches
        );

        return [
            'total_matches' => $total,

            'average_score' => round(
                (float) (
                    (clone $matches)->avg('total_score') ?? 0
                ),
                2
            ),

            'confidence' => [
                'high' => $high,
                'medium' => $medium,
                'low' => $low,
            ],

            'statuses' => [
                'approved' => $approved,
                'rejected' => $rejected,
                'other' => max(
                    0,
                    $total - $approved - $rejected
                ),
            ],

            'requests' => [
                'total' => $requests,
                'with_matches' => $requestsWithMatches,
                'without_matches' => $noMatchRequests,
            ],
        ];
    }

    private function demandMatrix(
        Carbon $from,
        Carbon $to
    ): array {
        $items = \App\Models\BuyerRequestItem::query()
            ->with([
                'product',
                'category',
            ])
            ->whereHas('buyerRequest', function (Builder $query) use (
                $from,
                $to
            ) {
                $query->whereBetween(
                    'created_at',
                    [$from, $to]
                );
            })
            ->get();

        $products = $items
            ->groupBy(function ($item) {
                return $item->product_id ?: 'category-'.$item->category_id;
            })
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'product' => $first->product?->name
                        ?: 'Category Request',

                    'category' => $first->category?->name
                        ?: 'Uncategorized',

                    'request_count' => $group->count(),

                    'quantity' => round(
                        (float) $group->sum('quantity'),
                        2
                    ),
                ];
            })
            ->sortByDesc('request_count')
            ->values()
            ->take(10);

        $categories = $items
            ->groupBy('category_id')
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'category' => $first->category?->name
                        ?: 'Uncategorized',

                    'request_count' => $group->count(),

                    'quantity' => round(
                        (float) $group->sum('quantity'),
                        2
                    ),
                ];
            })
            ->sortByDesc('request_count')
            ->values()
            ->take(8);

        return [
            'products' => $products->all(),
            'categories' => $categories->all(),
        ];
    }

    private function alerts(): array
    {
        $alerts = collect();

        $sources = Source::query()
            ->with([
                'latestVerification',
                'performance',
                'products',
                'availabilities',
            ])
            ->get();

        foreach ($sources as $source) {
            if (
                ! $source->latestVerification
                || $source->latestVerification->status !== 'verified'
            ) {
                $alerts->push([
                    'type' => 'verification',
                    'severity' => 'high',
                    'title' => 'Verification Required',
                    'message' => "{$source->reference_code} requires source verification.",
                    'reference_code' => $source->reference_code,
                ]);
            }

            $availableProducts = $source
                ->products
                ->where('is_available', true)
                ->count();

            $totalProducts = $source
                ->products
                ->count();

            if (
                $totalProducts > 0
                && $availableProducts === 0
            ) {
                $alerts->push([
                    'type' => 'availability',
                    'severity' => 'high',
                    'title' => 'No Available Products',
                    'message' => "{$source->reference_code} currently has no available products.",
                    'reference_code' => $source->reference_code,
                ]);
            } elseif (
                $totalProducts > 0
                && $availableProducts / $totalProducts < 0.40
            ) {
                $alerts->push([
                    'type' => 'availability',
                    'severity' => 'medium',
                    'title' => 'Low Availability',
                    'message' => "{$source->reference_code} has low product availability.",
                    'reference_code' => $source->reference_code,
                ]);
            }

            $performanceScore = (int) (
                $source->performance?->performance_score
                ?? $source->performance_score
            );

            if ($performanceScore < 60) {
                $alerts->push([
                    'type' => 'performance',
                    'severity' => 'high',
                    'title' => 'Low Source Performance',
                    'message' => "{$source->reference_code} has a performance score below 60.",
                    'reference_code' => $source->reference_code,
                ]);
            }
        }

        return [
            'total' => $alerts->count(),

            'high' => $alerts
                ->where('severity', 'high')
                ->count(),

            'medium' => $alerts
                ->where('severity', 'medium')
                ->count(),

            'items' => $alerts
                ->sortByDesc(function ($alert) {
                    return $alert['severity'] === 'high'
                        ? 2
                        : 1;
                })
                ->values()
                ->take(15)
                ->all(),
        ];
    }

    private function adminActivity(
        Carbon $from,
        Carbon $to
    ): array {
        $query = AdminOverride::query()
            ->whereBetween('created_at', [$from, $to]);

        $total = (clone $query)->count();

        $actions = [
            'select' => (clone $query)
                ->where('action', 'select')
                ->count(),

            'approve' => (clone $query)
                ->where('action', 'approve')
                ->count(),

            'reject' => (clone $query)
                ->where('action', 'reject')
                ->count(),

            'change_rank' => (clone $query)
                ->where('action', 'change_rank')
                ->count(),
        ];

        return [
            'total_actions' => $total,
            'actions' => $actions,
        ];
    }

    private function verificationScore(Source $source): int
    {
        return $source->latestVerification?->status === 'verified'
            ? 100
            : 0;
    }

    private function availabilityScore(Source $source): int
    {
        $total = $source->products->count();

        if ($total === 0) {
            return 0;
        }

        $available = $source
            ->products
            ->where('is_available', true)
            ->count();

        return (int) round(
            ($available / $total) * 100
        );
    }

    private function healthLabel(int $score): string
    {
        return match (true) {
            $score >= 80 => 'Healthy',
            $score >= 60 => 'Attention',
            default => 'Critical',
        };
    }
}